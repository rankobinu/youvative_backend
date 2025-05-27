# Use the official PHP image
FROM php:8.2-cli

# Set working directory inside the container
WORKDIR /app

# Copy backend files into the container
COPY . .

# Install dependencies (if using Composer)
RUN composer install || true

# Install PDO and PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql


# Start the PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]

