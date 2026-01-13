FROM dunglas/frankenphp

# 1. Instalar las extensiones de PHP necesarias para Laravel
RUN install-php-extensions \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    pdo_mysql

# 2. COPIAR COMPOSER (Aquí está la magia que te falta)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configurar directorio de trabajo
WORKDIR /app

# 4. Copiar archivos del proyecto
COPY . .

# 5. Instalar dependencias (Ahora SÍ funcionará porque copiamos Composer arriba)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer update --no-dev --optimize-autoloader

# 6. Comando de arranque (Migraciones + Servidor)
# Usamos el formato Shell para que reconozca la variable $PORT de Railway
CMD php artisan migrate --force && \
    php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=${PORT:-8000}
