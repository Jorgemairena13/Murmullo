 #!/bin/bash

echo "==> Arreglando permisos..."
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

php artisan storage:link --force 2>/dev/null || true

echo "==> DB_HOST: $DB_HOST"
echo "==> DB_DATABASE: $DB_DATABASE"
echo "==> CLOUDINARY_URL: $CLOUDINARY_URL"

echo "==> Limpiando cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo "==> Cacheando configuracion..."
php artisan config:cache || true
php artisan route:cache || true
php artisan event:cache || true

echo "==> Ejecutando migraciones..."
php artisan migrate --force || true

echo "==> Arrancando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
