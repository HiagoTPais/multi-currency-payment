FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev \
    && docker-php-ext-install pdo_mysql zip

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT