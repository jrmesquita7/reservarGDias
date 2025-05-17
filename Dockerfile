# Use official PHP image with Apache
FROM php:8.0-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application source code to /var/www/html
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (optional, depending on your app needs)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
