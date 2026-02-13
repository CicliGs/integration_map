FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

COPY . .

RUN apk add --no-cache \
        nodejs npm bash git curl \
        libpng-dev libzip-dev zip unzip sqlite-dev \
    && docker-php-ext-install pdo pdo_mysql gd zip pdo_sqlite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && npm ci \
    && npm run build \
    && rm -rf node_modules /root/.npm \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

CMD ["sh", "-c", "touch database/database.sqlite && php artisan migrate --force && php artisan db:seed --class=AdminUserSeeder --force && php artisan serve --host=0.0.0.0 --port=8000"]
