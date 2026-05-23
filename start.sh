#!/bin/bash
set -e
php artisan storage:link --force 2>/dev/null || true
echo "==> DB_HOST: $DB_HOST"
echo "==> DB_DATABASE: $DB_DATABASE"
echo "==> Limpiando cache anterior..."
php artisan config:clear
php artisan cache:clear

echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Arrancando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
