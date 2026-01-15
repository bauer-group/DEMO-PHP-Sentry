FROM php:8.5-cli

WORKDIR /app

# Install dependencies für Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD php -r "file_get_contents('http://localhost:8080') || exit(1);"

# Start PHP server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
