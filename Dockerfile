# Stage 1: Build frontend assets
FROM node:22-bookworm-slim AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
ENV VERCEL=1
RUN npm run build

# Stage 2: Production PHP runtime with FrankenPHP
FROM dunglas/frankenphp:latest-php8.4

# Install required PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    bcmath \
    gd \
    intl \
    opcache \
    zip \
    exif

WORKDIR /app

# Copy Composer binary from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /app

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build /app/public/build

# Install production PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Prepare storage & bootstrap directories with correct permissions
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/logs \
             bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Copy custom Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

ENV PORT=80
EXPOSE 80
