FROM php:8.4-fpm

# 1. Dependencias del sistema y Nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    nginx && \
    rm -rf /var/lib/apt/lists/*

# 2. Descarga del script optimizado de extensión de PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# 3. Instalación limpia (evita compilar desde cero)
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_pgsql mbstring exif pcntl bcmath gd

# 4. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Código del proyecto
COPY . .

# 6. Permisos del script de despliegue
RUN chmod +x ./scripts/00-laravel-deploy.sh

# 7. Dependencias de Laravel
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# 8. Permisos de directorios de almacenamiento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ./scripts/00-laravel-deploy.sh && php artisan serve --host=0.0.0.0 --port=80