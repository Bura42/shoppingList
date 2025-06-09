# ---- Base Stage ----
# Using a simplified and more resilient approach to avoid platform-specific issues.
FROM php:8.3-fpm-alpine AS base

WORKDIR /var/www/html

# Update the package repository and install persistent system dependencies.
# ADDED oniguruma-dev, which is a required dependency for the mbstring extension.
RUN apk update && apk add --no-cache \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    oniguruma-dev

# Install the required PHP extensions.
# Re-combined into a single command for efficiency now that the dependency issue is resolved.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    pdo_mysql \
    mbstring \
    exif \
    zip

# Get the latest version of Composer.
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Configure Opcache (it's built-in, so we just need to enable and configure).
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=4000"; \
    echo "opcache.revalidate_freq=60"; \
    echo "opcache.fast_shutdown=1"; \
} > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Create a non-root user for security.
RUN addgroup -g 1000 appgroup && \
    adduser -u 1000 -G appgroup -s /bin/sh -D appuser
RUN chown -R appuser:appgroup /var/www/html

USER appuser

EXPOSE 9000

# ---- Development Stage ----
FROM base AS development

USER root

#
# Xdebug DO NOT WORK AND I DONT WANNA DEAL WITH IT NOW
#
# Install development-specific tools.
#RUN apk add --no-cache git \
#    && pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug for development.
#RUN { \
#    echo "xdebug.mode=develop,debug"; \
#    echo "xdebug.start_with_request=yes"; \
#    echo "xdebug.discover_client_host=1"; \
#    echo "xdebug.client_host=host.docker.internal"; \
#    echo "xdebug.client_port=9003"; \
#} > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER appuser

# ---- Production Stage ----
FROM base AS production

USER root

COPY --chown=appuser:appgroup composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader
COPY --chown=appuser:appgroup . .

USER appuser
