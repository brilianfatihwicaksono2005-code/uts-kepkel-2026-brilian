FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Copy seluruh file proyek ke dalam docker
COPY . .

# Jalankan server backend secara default saat container dijalankan
CMD ["php", "-S", "0.0.0.0:8000", "-t", "backend/public"]