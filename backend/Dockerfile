FROM php:8.3-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libonig-dev zip unzip git curl libpq-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /app

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers Symfony
COPY . .

# Installer les dépendances Symfony
RUN composer install

# Expose port
EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
