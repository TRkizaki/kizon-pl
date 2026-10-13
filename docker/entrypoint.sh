#!/bin/sh
set -e

# Cache config, routes and views with the real runtime environment (APP_KEY,
# the API token), then hand over to the image's own entrypoint, which starts
# FrankenPHP with the default Caddyfile.
php artisan optimize

exec docker-php-entrypoint "$@"
