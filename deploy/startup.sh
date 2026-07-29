#!/bin/bash
# Startup script for Azure App Service (Linux, PHP 8.3).
# Runs on every container boot via the "Startup Command":
#   /home/site/wwwroot/deploy/startup.sh
set -e

# 1. Point nginx at Laravel's document root (public/), not the default wwwroot.
cp /home/site/wwwroot/deploy/nginx-default.conf /etc/nginx/sites-available/default
service nginx reload

# 2. Ensure Laravel's writable directories exist. The GitHub Actions artifact
#    can drop empty dirs (their .gitignore placeholders get stripped), which
#    makes realpath(storage/framework/views) fail during config:cache.
cd /home/site/wwwroot
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache

# 3. Run migrations (idempotent) and cache config & routes for production.
php artisan migrate --force
php artisan config:cache
php artisan route:cache
