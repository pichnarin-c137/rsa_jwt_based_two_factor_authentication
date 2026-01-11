FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    openssl \
    autoconf \
    build-essential

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring

# Install Xdebug via pecl and enable it
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy Xdebug ini
COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install dependencies
RUN composer install --optimize-autoloader

# Create keys directory
RUN mkdir -p storage/keys && chmod -R 775 storage

EXPOSE 8000
