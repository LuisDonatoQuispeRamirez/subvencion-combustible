#!/usr/bin/env bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=VehiculosDemoSeeder --force
php artisan db:seed --class=EstacionDemoSeeder --force