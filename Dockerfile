FROM dunglas/frankenphp:php8.3-bookworm

# Install PHP extensions yang kurang (gd wajib untuk phpspreadsheet/excel)
RUN install-php-extensions gd zip intl pdo_mysql

# Install Node.js 22 untuk build frontend
RUN apt-get update && apt-get install -y git \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Pakai Composer dari official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Set Node memory limit untuk Railway builder (free tier RAM terbatas)
ENV NODE_OPTIONS="--max-old-space-size=512"

# Install PHP dependencies
COPY composer.json composer.lock artisan ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --optimize-autoloader --no-scripts --no-interaction

# Install & build Node dependencies
COPY package.json package-lock.json vite.config.js ./
RUN npm install --prefer-offline --no-audit --no-fund

# Copy semua file aplikasi
COPY . .

# Build frontend + setup folder storage
RUN npm run build \
    && npm prune --omit=dev --ignore-scripts \
    && mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && COMPOSER_ALLOW_SUPERUSER=1 php artisan package:discover --ansi 2>/dev/null || true

EXPOSE 8080

# Saat container start: migrate DB, cache config, lalu jalankan server
CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && frankenphp php-server --listen :${PORT:-8080} --root /app/public"]