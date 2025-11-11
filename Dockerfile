# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy your PHP files into the Apache document root
COPY . /var/www/html/

# Expose port 80 (Render automatically maps this)
EXPOSE 80