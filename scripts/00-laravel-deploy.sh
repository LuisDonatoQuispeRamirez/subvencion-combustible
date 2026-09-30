#!/usr/bin/env bash
set -e

# Render indica el puerto en la variable PORT
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Despliegue de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=VehiculosDemoSeeder --force
php artisan db:seed --class=EstacionDemoSeeder --force

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground