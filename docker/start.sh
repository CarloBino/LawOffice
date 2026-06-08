#!/usr/bin/env bash
set -e

export PORT="${PORT:-8080}"

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -z "${ASSET_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export ASSET_URL="$RENDER_EXTERNAL_URL"
fi

if [ -n "${APP_KEY:-}" ] && [[ "$APP_KEY" != base64:* ]]; then
    export APP_KEY="base64:${APP_KEY}"
fi

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

if [ -n "${DEPLOY_ADMIN_EMAIL:-}" ] && [ -n "${DEPLOY_ADMIN_PASSWORD:-}" ]; then
    php artisan admin:ensure \
        --email="$DEPLOY_ADMIN_EMAIL" \
        --password="$DEPLOY_ADMIN_PASSWORD" \
        --name="${DEPLOY_ADMIN_NAME:-Administrator}"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
