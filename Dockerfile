# Use the official PHP image
FROM php:8.2-cli

# Set working directory inside the container
WORKDIR /app

# Copy backend files into the container
COPY . .

# Install dependencies (if using Composer)
RUN composer install || true

# Start the PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
