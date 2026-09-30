FROM php:8.4-fpm

# Instalar dependencias esenciales del sistema y Nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    nginx && \
    rm -rf /var/lib/apt/lists/*

# Instalar instalador rápido de extensiones de PHP
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_pgsql mbstring exif pcntl bcmath gd

# Copiar ejecutable de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código del proyecto
COPY . .

# Permisos de ejecución para el script de despliegue
RUN chmod +x ./scripts/00-laravel-deploy.sh

# Instalar dependencias PHP de Laravel
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# Ajustar permisos de directorios de almacenamiento y caché
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Ejecutar el script de despliegue y servir la aplicación
CMD ./scripts/00-laravel-deploy.sh && php artisan serve --host=0.0.0.0 --port=80