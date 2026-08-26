#!/usr/bin/env bash

set -Eeuo pipefail

PORT="${PORT:-10000}"
export PORT

envsubst '${PORT}' \
    < /etc/nginx/render/nginx.conf.template \
    > /etc/nginx/conf.d/default.conf

php-fpm -D

php artisan config:cache
php artisan view:cache

if [[ "${RUN_MIGRATIONS:-false}" == "true" ]]; then
    php artisan migrate --force
fi

exec nginx -g 'daemon off;'
