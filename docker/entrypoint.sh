#!/bin/sh
LOCKFILE="/var/www/storage/.composer.lock"

if [ ! -f "/var/www/vendor/autoload.php" ]; then
    if mkdir "$LOCKFILE" 2>/dev/null; then
        echo "Installing dependencies..."
        composer install --no-interaction --optimize-autoloader
        rm -rf "$LOCKFILE"
    else
        echo "Waiting for dependencies..."
        while [ ! -f "/var/www/vendor/autoload.php" ]; do
            sleep 2
        done
    fi
fi

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
exec "$@"
