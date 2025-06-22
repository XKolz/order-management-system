# # Use the official PHP 8.2 image with Apache
# FROM php:8.2-apache

# # Install system dependencies and PHP extensions
# RUN apt-get update && apt-get install -y \
#     unzip git curl libzip-dev zip \
#     && docker-php-ext-install pdo pdo_mysql zip

# # Enable Apache mod_rewrite
# RUN a2enmod rewrite

# # Set working directory
# WORKDIR /var/www/html

# # Copy composer from official image
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# # Copy all project files
# COPY . .

# # Install PHP dependencies
# RUN composer install --no-dev --optimize-autoloader

# # Set permissions
# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 775 storage bootstrap/cache database/database.sqlite

# # Expose port 80 (Apache default)
# EXPOSE 80

# # Start Apache
# CMD ["apache2-foreground"]
# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy all project files
COPY . .

# Create SQLite file in case it's missing and fix permissions
RUN touch database/database.sqlite \
    && composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 664 database/database.sqlite

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
