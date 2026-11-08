FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader
FROM php:8.3-cli-alpine
RUN apk add --no-cache postgresql-dev icu-dev && docker-php-ext-install pdo_pgsql intl
COPY --from=vendor /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY --from=vendor /app/vendor vendor
COPY . .
RUN php bin/console importmap:install --no-interaction \
    && php bin/console asset-map:compile
EXPOSE 8080
ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["sh","-c","php -S 0.0.0.0:${PORT:-8080} -t public"]
