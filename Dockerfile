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

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan optimize

EXPOSE 8000

CMD ["sh", "-c", "\
    php artisan migrate --force --no-interaction && \
    php artisan db:seed --force --no-interaction && \
    php artisan serve --host=0.0.0.0 --port=8000 & \
    php artisan queue:work --sleep=3 --tries=3 --timeout=90 \
"]
