FROM php:8.2-apache

# Enable PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Change Apache port for Render (10000)
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf && \
    sed -i 's/:80>/:10000>/' /etc/apache2/sites-enabled/000-default.conf

# Copy project files
COPY . /var/www/html/

# Expose Render port
EXPOSE 10000

CMD ["apache2-foreground"]
