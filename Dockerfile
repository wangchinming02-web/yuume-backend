FROM php:8.2-apache

# 1. 安裝必要的系統套件與 PHP 擴充 (包含 pdo_pgsql)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# 2. 開啟 Apache mod_rewrite
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
# 這裡使用 --no-dev 確保 Image 輕量化
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# 7. 設定儲存空間權限 (Laravel 必須)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. 設定啟動腳本 (EntryPoint)
# 我們不要把 migrate 寫在 RUN 裡，而是寫在啟動腳本中
# 這樣 Render 部署時如果資料庫連線還沒準備好，也不會讓建置過程直接崩潰
RUN echo '#!/bin/bash\n\
php /var/www/html/artisan migrate --force\n\
apache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]