#!/bin/sh
set -e

# Run composer install if needed
if [ ! -d "/var/www/html/vendor" ]; then
  composer install
fi

# Run the webserver
php-fpm &
nginx -g 'daemon off;'
