FROM php:8.2-apache

# Install mysqli and PDO MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your project into Apache’s document root
COPY . /var/www/html/

# Fix permissions (optional, but good practice)
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80