
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

EXPOSE 8000

# Needs completion in the future
CMD ["sh", "-c", "php artisan migrate --force --no-interaction && php artisan db:seed --force --no-interaction && php artisan serve --host=0.0.0.0 --port=8000"]
