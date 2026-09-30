FROM php:8.4-fpm

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx

# Instalar extensiones de PHP requeridas por Laravel y PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Copiar ejecutable de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Dar permisos de ejecución al script de despliegue
RUN chmod +x ./scripts/00-laravel-deploy.sh

# Instalar dependencias PHP
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# Ajustar permisos de carpetas de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Ejecutar script de despliegue (migraciones + seeders) y levantar el servidor
CMD ./scripts/00-laravel-deploy.sh && php artisan serve --host=0.0.0.0 --port=80