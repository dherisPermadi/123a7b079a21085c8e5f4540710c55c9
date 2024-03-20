# Use the official PHP image
FROM php:latest

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
    git \
    unzip \
    libpq-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql pdo_pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy custom php.ini file
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Copy composer.json and composer.lock files
COPY composer.json composer.lock ./

# Install project dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application code
COPY . .

# Generate autoload files
RUN composer dump-autoload --optimize

# Install PostgreSQL client and Redis extension for PHP
RUN apt-get install -y postgresql-client && \
    pecl install redis && \
    docker-php-ext-enable redis

# Expose port 80 (if your PHP built-in server runs on a different port, change it accordingly)
EXPOSE 8000

# Set entrypoint
ENTRYPOINT ["./entrypoint.sh"]