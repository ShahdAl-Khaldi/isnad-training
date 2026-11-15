#!/bin/sh
set -e
cd /var/www/html

# Render بيحدد البورت بمتغيّر PORT
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Render بيولّد مفتاح base64 بدون بادئة "base64:" اللي Laravel بيحتاجها
if [ -n "$APP_KEY" ] && [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    export APP_KEY="base64:${APP_KEY}"
fi

# قاعدة بيانات دائمة (Neon / Postgres): إذا في DB_URL، البيانات والصور بتنحفظ هناك وما بتنمسح
if [ -n "$DB_URL" ]; then
    export DB_CONNECTION=pgsql
    export FILE_STORE=database
fi

# قاعدة بيانات SQLite (لما ما في DB_URL)
DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
[ -f "$DB_FILE" ] || touch "$DB_FILE"

mkdir -p storage/app/private/receipts storage/app/private/avatars storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache "$(dirname "$DB_FILE")"

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache "$(dirname "$DB_FILE")"

exec apache2-foreground
