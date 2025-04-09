FROM php:8.2-fpm

# Install system dependencies dan ekstensi yang dibutuhkan
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Salin composer file dulu supaya layer caching efisien
COPY composer.json composer.lock ./

# Jalankan composer install
RUN composer install --no-scripts --no-autoloader

# Salin seluruh project ke container
COPY . .

# Generate autoload
RUN composer dump-autoload

CMD ["php-fpm"]
