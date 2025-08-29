FROM php:8.3-apache

# Install necessary system dependencies for Composer
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip \
&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application code
COPY . /var/www/html/

# Expose port 80 for the web server
EXPOSE 80