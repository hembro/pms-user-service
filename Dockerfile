# ==========================================
# Stage 1: Build Dependencies (Backend Only)
# ==========================================
FROM php:8.4-fpm-alpine AS builder

# Install system dependencies required for compiling PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    linux-headers \
    $PHPIZE_DEPS

# Install required PHP extensions (Postgres, Redis, BCMath for 2FA, PCNTL for Queues)
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath pcntl sockets \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# ADD THESE LINES: Inject dummy variables to prevent PHP 8.4 strict type crashes during boot
ENV APP_ENV="production"
ENV APP_KEY="base64:VZSDA9cZT0Enb7/EUDTPO1kCR24JPNd0edMbuGyca90="
ENV APP_URL="http://pms-user-service"
ENV APP_FRONTEND_URL="http://pms-user-service"

# 1. Install PHP dependencies BUT skip the auto-scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# 2. Forge temporary Passport keys using OpenSSL to bypass Laravel's boot crash
#RUN openssl genrsa -out storage/oauth-private.key 2048 \
    #&& openssl rsa -in storage/oauth-private.key -pubout -out storage/oauth-public.key \
    #&& chmod 600 storage/oauth-private.key storage/oauth-public.key

# 3. Now safely run the discovery script and rm cache for more bulletproofing
RUN rm -f bootstrap/cache/*.php && php artisan package:discover --ansi
RUN php artisan package:discover --ansi

# 4. Create the storage symlink
RUN php artisan storage:link

# ==========================================
# Stage 2: Production Environment
# ==========================================
FROM php:8.4-fpm-alpine

ENV APP_ENV=production
ENV APP_KEY="base64:VZSDA9cZT0Enb7/EUDTPO1kCR24JPNd0edMbuGyca90="
ENV APP_URL=http://pms-user-service
ENV APP_FRONTEND_URL=http://pms-user-service

# Install minimal production system dependencies (Nginx and Supervisor)
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    supervisor \
    tzdata \
    libzip

# Copy compiled PHP extensions from the builder stage
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

WORKDIR /var/www/html

# Copy the fully built Laravel application from the builder stage
COPY --from=builder /app /var/www/html

# Set correct storage and cache permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx and Supervisor configuration files
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Expose web server port
EXPOSE 80

# Start Supervisor (This will run both Nginx and PHP-FPM simultaneously)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]