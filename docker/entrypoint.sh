#!/bin/sh
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    composer install --no-interaction --optimize-autoloader
fi
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
exec "$@"
