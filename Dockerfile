FROM php:8.3-apache

# Install PHP PostgreSQL extension
RUN docker-php-ext-install pdo pdo_pgsql
# Copy your application files into the container
COPY . /var/www/html/

# Expose port 80 for the web server
EXPOSE 80

# The web server will start automatically with this base image