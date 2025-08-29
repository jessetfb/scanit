FROM php:8.3-apache

# Install the necessary library for PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# Install the pdo_pgsql PHP extension
RUN docker-php-ext-install pdo pdo_pgsql

# Copy your application files into the container
COPY . /var/www/html/

# Expose port 80 for the web server
EXPOSE 80