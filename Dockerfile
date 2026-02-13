FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y 
    git 
    curl 
    libpng-dev 
    libonig-dev 
    libxml2-dev 
    zip 
    unzip 
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Ensure entrypoint is executable
RUN chmod +x /var/www/docker-entrypoint.sh

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
