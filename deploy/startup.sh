#!/bin/bash
# Startup script for Azure App Service (Linux, PHP 8.3).
# Runs on every container boot via the "Startup Command":
#   /home/site/wwwroot/deploy/startup.sh
set -e

# 1. Point nginx at Laravel's document root (public/), not the default wwwroot.
cp /home/site/wwwroot/deploy/nginx-default.conf /etc/nginx/sites-available/default
service nginx reload

# 2. Run migrations (idempotent) and cache config & routes for production.
cd /home/site/wwwroot
php artisan migrate --force
php artisan config:cache
php artisan route:cache
