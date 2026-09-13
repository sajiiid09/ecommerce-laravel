# syntax=docker/dockerfile:1
#
# StoreZ production image.
#
# Self-contained: nginx + php-fpm + the Laravel scheduler under supervisord,
# serving on port 8080. Intended to be bound to 127.0.0.1 on the host and
# reverse-proxied by the host's nginx. See docker-compose.prod.yml.
#
# Differs from Dockerfile.render (Render.com) in four ways that matter:
#   1. GD is compiled with WebP support - ImageHandler.php hard-fails without it
#   2. storage:link runs at container start (entrypoint), not never
#   3. the scheduler actually runs (supervisord)
#   4. .dockerignore keeps tests/env/dev files out of the image

# ---------------------------------------------------------------------------
# Stage 1: PHP dependencies
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /var/www/html

# Only the manifests, so this layer caches until dependencies actually change.
COPY composer.json composer.lock ./

# --no-scripts because artisan isn't present yet at this stage.
# The committed lockfile is authoritative: composer.json sets
# minimum-stability=dev, so never resolve fresh here.
RUN composer install \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --no-dev \
        --optimize-autoloader \
        --no-scripts

# ---------------------------------------------------------------------------
# Stage 2: Frontend assets (Vite + Tailwind)
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

# Sheaf/Livewire ship CSS and JS that Vite resolves out of vendor/.
COPY --from=vendor /var/www/html/vendor ./vendor
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build

# ---------------------------------------------------------------------------
# Stage 3: Runtime
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS app

# Runtime packages plus the headers needed to build the PHP extensions.
# The -dev packages are removed again below to keep the image small.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        curl \
        libpq5 \
        libzip4 \
        libicu72 \
        libfreetype6 \
        libjpeg62-turbo \
        libpng16-16 \
        libwebp7; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        build-essential \
        autoconf \
        pkg-config; \
    \
    # GD with WebP: required by app/Traits/ImageHandler.php, which throws
    # without extension_loaded('gd') && function_exists('imagewebp').
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    \
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark > /dev/null; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*

COPY docker/prod/php.ini /usr/local/etc/php/conf.d/99-storez.ini
COPY docker/prod/php-fpm.conf /usr/local/etc/php-fpm.d/zz-storez.conf
COPY docker/prod/nginx.conf /etc/nginx/nginx.conf
COPY docker/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/prod/entrypoint.sh /usr/local/bin/storez-entrypoint

WORKDIR /var/www/html

COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .
COPY --from=assets /var/www/html/public/build ./public/build

RUN set -eux; \
    chmod +x /usr/local/bin/storez-entrypoint; \
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        storage/app/private \
        bootstrap/cache \
        /run/php; \
    chown -R www-data:www-data storage bootstrap/cache; \
    # nginx runs as www-data and needs to write its own runtime dirs
    chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# Regenerate the classmap now that the application source is present, then
# drop the composer binary so it is not shipped in the runtime image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN set -eux; \
    composer dump-autoload --no-dev --optimize --classmap-authoritative; \
    rm -f /usr/bin/composer

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["/usr/local/bin/storez-entrypoint"]
