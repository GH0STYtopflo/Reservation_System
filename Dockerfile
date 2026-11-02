FROM php:8.4-cli-alpine

WORKDIR /app

RUN apk add --no-cache --virtual .build-deps \
        postgresql-dev \
        libzip-dev \
        $PHPIZE_DEPS \
    && apk add --no-cache \
        postgresql-libs \
        libzip \
        unzip \
        curl \
    && docker-php-ext-install pdo_pgsql zip \
    && apk del .build-deps \
    && curl -fsSL https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8000

CMD ["sh", "-c", "\
    php artisan migrate && \
    php artisan db:seed --force && \
    php artisan queue:work --sleep=3 --tries=3 --timeout=90 & \
    php artisan serve --host=0.0.0.0 --port=8000 \
"]
