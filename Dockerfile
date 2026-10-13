# syntax=docker/dockerfile:1

# ---- base: FrankenPHP (PHP 8.5 + Caddy) and Composer -------------------------
# The image already has OPcache and every extension Laravel needs; zip is for
# Composer, which has no unzip or git to fall back on here.
FROM dunglas/frankenphp:1.13-php8.5 AS base
RUN install-php-extensions zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app

# ---- test: what CI builds. The build fails if any test fails. ----------------
FROM base AS test
COPY . .
RUN composer install --no-interaction --no-progress --prefer-dist \
 && cp .env.example .env \
 && php artisan key:generate \
 && php artisan test

# ---- app: the runtime image ---------------------------------------------------
FROM base AS app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --no-interaction --no-progress --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/kizon-entrypoint
ENTRYPOINT ["kizon-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
