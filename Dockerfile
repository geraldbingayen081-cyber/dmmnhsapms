FROM php:8.3-cli

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Node.js and Composer
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies and build assets
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

EXPOSE 10000

CMD ["sh", "-c", "php artisan serve --host 0.0.0.0 --port ${PORT:-10000}"]
