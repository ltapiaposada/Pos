FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY postcss.config.js tailwind.config.js ./
RUN npm ci
RUN npm run build

FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && cp .env.example .env \
    && sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env \
    && sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env \
    && sed -i 's|^SEED_DEMO_DATA=.*|SEED_DEMO_DATA=false|' .env

EXPOSE 80

CMD ["sh", "-lc", "grep -q '^APP_KEY=base64:' .env || php artisan key:generate --force && php artisan migrate --seed --force && php artisan config:clear && php artisan route:clear && apache2-foreground"]
