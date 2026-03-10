FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git curl zip unzip libpng-dev libzip-dev oniguruma-dev \
    postgresql-dev nginx supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy built frontend assets (pre-built)
# If building frontend here, install Node:
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --legacy-peer-deps
COPY . .
RUN npm run build

# Final production image
FROM php:8.3-fpm-alpine AS production

RUN apk add --no-cache \
    libpng-dev libzip-dev oniguruma-dev postgresql-dev \
    nginx supervisor curl

RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=base /var/www/html .
COPY --from=frontend /app/public/build ./public/build

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP-FPM config
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
