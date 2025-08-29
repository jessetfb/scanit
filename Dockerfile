FROM php:8.3-apache

# Install the necessary library for PostgreSQL
# libpq-dev is required to build the pdo_pgsql extension.
RUN apt-get update && apt-get install -y libpq-dev \
    # Clean up APT caches to reduce image size
    && rm -rf /var/lib/apt/lists/*

# Install the pdo_pgsql PHP extension
RUN docker-php-ext-install pdo pdo_pgsql

# Copy your application files into the container
COPY . /var/www/html/

# Expose port 80 for the web server
EXPOSE 80