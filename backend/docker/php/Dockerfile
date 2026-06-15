FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    $PHPIZE_DEPS \
    && docker-php-ext-install intl mbstring pdo_mysql zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
