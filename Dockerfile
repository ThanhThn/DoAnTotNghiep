# Dùng base image Ubuntu 22.04
FROM ubuntu:22.04

# Thiết lập môi trường không tương tác
ENV DEBIAN_FRONTEND=noninteractive

# Cài đặt các gói cần thiết với retry logic
RUN apt-get update --allow-releaseinfo-change || apt-get update --allow-releaseinfo-change \
    && apt-get install -y \
        curl \
        git \
        unzip \
        supervisor \
        nginx \
        software-properties-common \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update \
    && apt-get install -y \
        php8.2 \
        php8.2-fpm \
        php8.2-mysql \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-bcmath \
        php8.2-pcntl \
    && php -v \
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

# Quyền thư mục
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copy file cấu hình
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY nginx.conf /etc/nginx/sites-available/default

# Mở port mà Render yêu cầu
EXPOSE 10000

# Chạy Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
