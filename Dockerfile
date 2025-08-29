FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip gd

# Set the working directory
WORKDIR /var/www/html

# Copy all application files first
COPY . /var/www/html/

# Install Composer
COPY --from=composer:latest /usr/local/bin/composer /usr/local/bin/composer

# Run Composer to install dependencies
RUN composer install --no-dev --optimize-autoloader