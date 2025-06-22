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

# Serve from Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update Apache config
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf


# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy all project files
COPY . .

# Create SQLite file in case it's missing and fix permissions
RUN touch database/database.sqlite \
    # && composer install --no-dev --optimize-autoloader \
    && composer install --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 664 database/database.sqlite

# Expose Apache port
EXPOSE 80

# Start Apache
# CMD ["apache2-foreground"]
# migrate the database
# CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]
# seeding, add data from the seed file
CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && apache2-foreground"]


