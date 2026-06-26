FROM php:8.2-apache

# 安裝必要的套件 (如 git, zip, unzip)
RUN apt-get update && apt-get install -y git zip unzip \
    && docker-php-ext-install pdo pdo_mysql

# 設定 Laravel 的 Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 複製代碼
COPY . /var/www/html

# 設定權限
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 自動執行資料庫遷移
RUN bash -c "php /var/www/html/artisan migrate --force"

EXPOSE 80