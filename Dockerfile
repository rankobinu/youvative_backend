# Use the official PHP image
FROM php:8.2-cli

# Set working directory inside the container
WORKDIR /app

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy backend files into the container
COPY . .

# Install dependencies (if using Composer)
RUN composer install --no-dev --optimize-autoloader

# Start the PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]

