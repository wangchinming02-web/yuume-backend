FROM php:8.2-apache

# 1. 安裝系統套件與 PHP 擴充
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# 2. 開啟 Apache mod_rewrite 模組 (這是解決 404 Not Found 的關鍵)
RUN a2enmod rewrite

# 3. 設定 Laravel 的 Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. 複製專案代碼
COPY . /var/www/html

# 6. 安裝 Laravel 相依套件
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# 7. 設定儲存空間權限 (Laravel 必須)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. 自動執行資料庫遷移
# 注意：確保你的 Render Environment Variables 設定正確，否則這行會失敗
RUN bash -c "php /var/www/html/artisan migrate --force"

EXPOSE 80