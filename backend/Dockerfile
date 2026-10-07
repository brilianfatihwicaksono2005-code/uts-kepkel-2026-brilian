# STAGE 1: Build Dependencies menggunakan Composer
FROM composer:2.7 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
# Install dependency (abaikan platform reqs agar tidak bentrok)
RUN composer install --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize

# STAGE 2: Production menggunakan FrankenPHP (Menjawab Poin Bonus +10)
FROM dunglas/frankenphp:php8.3
WORKDIR /app
# Menyalin file aplikasi dan vendor dari STAGE 1
COPY --from=builder /app /app

# Mengatur permission untuk folder cache dan storage Laravel
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Menjalankan server FrankenPHP 
ENV SERVER_NAME=":8000"
EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]