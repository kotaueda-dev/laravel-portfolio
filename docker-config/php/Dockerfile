
FROM php:8.2-fpm-alpine

# composerコマンドのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk update && apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev \
    sqlite-dev \
    mysql-client \
    curl \
    git \
    unzip \
    icu-dev \
    shadow

RUN docker-php-ext-install pdo pdo_mysql opcache zip intl

RUN useradd -m -u 1000 laravel
RUN chown laravel:laravel /var/www/html

WORKDIR /var/www/html

USER laravel
