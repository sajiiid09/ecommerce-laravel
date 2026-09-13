#!/usr/bin/env bash
#
# StoreZ container entrypoint.
#
# Order matters here: storage dirs must exist and be writable before
# storage:link runs, and config must be cached before the scheduler starts.

set -Eeuo pipefail

log() { printf '[entrypoint] %s\n' "$*"; }
fail() { printf '[entrypoint] ERROR: %s\n' "$*" >&2; exit 1; }

cd /var/www/html

# Allow one-off commands to run without the full boot sequence, e.g.
#   docker compose run --rm --no-deps app php artisan key:generate --show
#   docker compose exec app php artisan migrate:status
# Only a bare `docker run <image>` (no args) starts the server.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# --------------------------------------------------------------------------
# 1. Storage scaffolding
#
# Named volumes mount in empty and root-owned, shadowing whatever the image
# built. Recreate the tree and hand it to www-data every boot, or uploads
# fail with a permission error that surfaces only as a 500.
# --------------------------------------------------------------------------
log 'Preparing storage directories'
mkdir -p \
    storage/app/public \
    storage/app/private/imports \
    storage/app/private/exports \
    storage/app/private/category-imports \
    storage/app/private/livewire-tmp \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R u+rwX,g+rwX storage bootstrap/cache

# --------------------------------------------------------------------------
# 2. public/storage symlink
#
# Must happen at runtime, not build time: storage/app/public is a volume, and
# a symlink baked into the image would point at the pre-mount contents.
# Dockerfile.render never ran this at all, which 404s every uploaded image.
# --------------------------------------------------------------------------
log 'Linking public/storage -> storage/app/public'
php artisan storage:link --force --quiet
[ -e public/storage ] || fail 'storage:link did not produce public/storage'

# --------------------------------------------------------------------------
# 3. Fail fast on missing configuration
# --------------------------------------------------------------------------
if [ -z "${APP_KEY:-}" ]; then
    fail 'APP_KEY is not set. Generate one with:
        docker compose -f docker-compose.prod.yml run --rm --no-deps app php artisan key:generate --show'
fi

# ->onOneServer() on the hourly schedule needs an atomic lock, which the file
# cache driver cannot provide. Catch this here rather than as an hourly
# exception nobody reads.
case "${CACHE_STORE:-file}" in
    redis|database|memcached|dynamodb) ;;
    *) fail "CACHE_STORE='${CACHE_STORE:-file}' does not support atomic locks, which the
        scheduler's ->onOneServer() requires. Use redis or database." ;;
esac

# --------------------------------------------------------------------------
# 4. Wait for the database
#
# Postgres lives on the host (via host.docker.internal), so it is not covered
# by compose's depends_on healthchecks.
# --------------------------------------------------------------------------
log "Waiting for database at ${DB_HOST:-unset}:${DB_PORT:-5432}"
for attempt in $(seq 1 30); do
    if php -r '
        $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST"), getenv("DB_PORT") ?: "5432", getenv("DB_DATABASE"));
        try { new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"),
            [PDO::ATTR_TIMEOUT => 3]); exit(0); }
        catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; then
        log 'Database is reachable'
        break
    fi
    [ "$attempt" -eq 30 ] && fail "Database unreachable after 30 attempts.
        Check that pg_hba.conf allows the docker bridge and that
        DB_HOST=host.docker.internal with extra_hosts host-gateway."
    sleep 2
done

# --------------------------------------------------------------------------
# 5. Optimize caches
#
# Rebuilt every boot because .env is supplied at runtime, so a config cache
# baked at build time would hold the wrong values.
# --------------------------------------------------------------------------
log 'Caching config, views and events'
php artisan config:clear --quiet
# Defensive: a stale bootstrap/cache/routes-v7.php from an older image would
# be fatal at boot (see the route:cache note below).
rm -f bootstrap/cache/routes-v7.php bootstrap/cache/routes.php
php artisan config:cache --quiet
php artisan view:cache --quiet
php artisan event:cache --quiet

# NOTE: `route:cache` is deliberately NOT run.
#
# routes/web.php registers 54 Livewire routes by passing component
# *instances* - Route::livewire('/cart', new Cart). Route caching serializes
# the route collection with var_export(), which requires __set_state() on
# every exported object. Livewire components do not implement it, so
# route:cache aborts with:
#
#   Method App\Livewire\Pages\Store\Category::__set_state does not exist.
#
# and the cached file it leaves behind makes every subsequent request fatal.
# Route caching is only an optimization; config/view/event caching above
# provide the bulk of the benefit. To enable it later, all 54 routes would
# need to pass class strings instead of instances.

# --------------------------------------------------------------------------
# 6. Migrations / seeding (opt-in)
# --------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    log 'Running migrations'
    php artisan migrate --force
else
    log 'Skipping migrations (RUN_MIGRATIONS is not "true")'
fi

# First boot only. Seeding populates the demo catalog and copies ~90 images
# onto the public disk, and creates the admin user from ADMIN_EMAIL /
# ADMIN_PASSWORD. Set back to false afterwards.
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    log 'Running seeders'
    php artisan db:seed --force
    log 'Seeding complete - set RUN_SEEDERS=false before the next deploy'
fi

# Ensure anything the seeders or migrations wrote is still www-data-owned
# (artisan runs as root here).
chown -R www-data:www-data storage bootstrap/cache

log 'Starting supervisord (php-fpm, nginx, scheduler)'
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
