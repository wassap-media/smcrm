# RISE CRM - PHP + Apache image
FROM php:8.3-apache

# Install system dependencies for PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libicu-dev libzip-dev zlib1g-dev \
       libpng-dev libjpeg62-turbo-dev libfreetype6-dev libxml2-dev \
       libonig-dev pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Build and enable PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mbstring mysqli pdo pdo_mysql intl zip exif \
    && docker-php-ext-enable opcache

# Set Apache DocumentRoot to CRM directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/CRM
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/>!g' /etc/apache2/apache2.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy application
COPY CRM /var/www/html/CRM

# Create writable directories and set permissions
RUN mkdir -p /var/www/html/CRM/writable/cache \
    /var/www/html/CRM/writable/logs \
    /var/www/html/CRM/writable/session \
    /var/www/html/CRM/writable/debugbar \
    /var/www/html/CRM/writable/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/CRM/writable

EXPOSE 80

# Health check (optional)
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD curl -fsS http://localhost/ || exit 1

# Start Apache (default CMD from base image)
