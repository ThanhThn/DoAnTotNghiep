# Dùng base image Ubuntu để không phụ thuộc vào PHP mặc định
FROM ubuntu:22.04

# Cài đặt các gói cần thiết
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    supervisor \
    nginx \
    software-properties-common \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update

# Cài PHP 8.2 và extensions cần thiết
RUN apt-get install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cài Node.js để build asset (Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Thiết lập thư mục làm việc
WORKDIR /app
COPY . /app

# Cài dependencies
RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

# Copy file cấu hình Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy Nginx config (tùy chọn nếu muốn custom proxy)
COPY nginx.conf /etc/nginx/sites-available/default

# Mở port mà Render yêu cầu
EXPOSE 10000

# Chạy Supervisor để quản lý Laravel và Reverb
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
