# Use PHP 8.3 with Apache
FROM php:8.3-apache

# Install required dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl sqlite3 libsqlite3-dev libonig-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql bcmath intl zip

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Ensure required directories exist
RUN mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/bootstrap/cache

# Fix Laravel storage & cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/storage/framework/cache/data \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/storage/framework/cache/data

# Ensure Apache serves from the "public" directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Set environment variables
ENV DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/storage/opengrc.sqlite

# Copy environment file
RUN cp .env.example .env

# ✅ Install Backend Dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev || cat /var/www/html/vendor/composer/installed.json

# ✅ Install Filament & publish assets
RUN php artisan filament:install \
    && php artisan vendor:publish --tag=filament-config \
    && php artisan vendor:publish --tag=filament-assets

# ✅ Install Frontend Dependencies
RUN npm install && npm run build

# ✅ Install Faker & update autoload
RUN composer require fakerphp/faker --dev && composer dump-autoload

# ✅ Generate application key & run migrations
RUN php artisan key:generate
RUN php artisan migrate --force
RUN php artisan db:seed --force

# ✅ Create symbolic links (fixes storage access issues)
RUN php artisan storage:link || true

# ✅ Fix final permissions
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/storage/framework/cache/data \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/storage/framework/cache/data

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# ✅ Set container to start with root user
USER root

# Start Apache
CMD ["apache2-foreground"]
