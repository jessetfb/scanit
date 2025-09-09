FROM composer:2 AS composer_stage

FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD and install PHP extensions
RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-freetype \
    --with-webp \
 && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    zip \
    gd

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . /var/www/html/

# Copy composer from first stage
COPY --from=composer_stage /usr/bin/composer /usr/local/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader
