#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")"
php -r 'exit(extension_loaded("gd") && extension_loaded("pdo_sqlite") && extension_loaded("mbstring") ? 0 : 1);' || { echo 'Enable GD, pdo_sqlite, and mbstring first.'; exit 1; }
composer install
[ -f .env ] || cp .env.example .env
[ -f database/database.sqlite ] || touch database/database.sqlite
if ! grep -q '^APP_KEY=.' .env; then php artisan key:generate; fi
php artisan migrate --force
php artisan pos:install
printf 'Ready. Run: php artisan serve
'
