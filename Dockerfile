# ---- Base Stage ----
# Use an official PHP image with FPM on a lightweight Alpine Linux base.
# Using a specific version (e.g., 8.3) is better than `latest` for predictability.
FROM php:8.3-fpm-alpine AS base

# Set the working directory in the container
WORKDIR /var/www/html

# Install system dependencies required for common PHP extensions.
# --no-cache reduces image size.
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install core PHP extensions.
# `docker-php-ext-install` is a helper script included in the base image.
# We're including PDO for database access, mbstring for string manipulation,
# exif and gd for image processing, and intl for internationalization.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql \
    mbstring \
    exif \
    gd \
    intl \
    opcache \
    zip

# Get the latest version of Composer, the PHP dependency manager.
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set recommended opcache settings for production.
# These would be customized in a mounted php.ini file for development.
RUN { \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=4000"; \
    echo "opcache.revalidate_freq=60"; \
    echo "opcache.fast_shutdown=1"; \
} > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Create a standard user for the application to run under.
# Running as a non-root user is a security best practice.
RUN addgroup -g 1000 appgroup && \
    adduser -u 1000 -G appgroup -s /bin/sh -D appuser
RUN chown -R appuser:appgroup /var/www/html

# Switch to the non-root user.
USER appuser

# Expose port 9000 to communicate with the web server (Nginx) via FastCGI.
EXPOSE 9000

# The default command `php-fpm` is inherited from the base image and will be run.

# ---- Development Stage ----
# This stage inherits from base and is optimized for development.
FROM base AS development

# Switch back to root to install dev-specific tools
USER root

# Install tools useful for development, like git and xdebug
RUN apk add --no-cache git
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug
RUN { \
    echo "xdebug.mode=develop,debug"; \
    echo "xdebug.start_with_request=yes"; \
    echo "xdebug.discover_client_host=1"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
} > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Switch back to the application user
USER appuser

# ---- Production Stage ----
# This stage builds the final, lean production image.
FROM base AS production

# Switch back to root to handle file copying and permissions
USER root

# Copy Composer dependency definitions
COPY --chown=appuser:appgroup composer.json composer.lock ./

# Install only production dependencies.
# --no-dev: Skips development packages.
# --no-interaction: Fails if any user input is required.
# --no-progress: Reduces noise.
# --no-scripts: Disables execution of scripts defined in composer.json.
# --optimize-autoloader: Creates a more performant classmap.
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

# Copy the rest of the application source code
COPY --chown=appuser:appgroup . .

# Final switch to the application user
USER appuser
