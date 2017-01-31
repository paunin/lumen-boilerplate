#!/usr/bin/env bash

set -e
cd /var/www/application/

echo '> Install dependencies'
php -d memory_limit=-1 /bin/composer install

echo '> Set up PHP Code Sniffer...'
./vendor/bin/phpcs --config-set installed_paths ../../../vendor/squizlabs/php_codesniffer/,../../../vendor/m6web/symfony2-coding-standard/

echo '> Enabling xDebug...'
cp -f docker/ApiApplication/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

echo '> Generating IDE helpers...'
php artisan ide-helper:generate

echo '> Run migrations...'
php artisan migrate:reset
php artisan migrate --seed
