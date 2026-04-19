FROM php:8.3-apache

# Veritabanı (PostgreSQL) bağlantısı için gerekli eklentiler
RUN apt-get update && apt-get install -y libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Apache ayarları ve Public klasörünü ana dizin yapma
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Proje dosyalarını kopyala
COPY . /var/www/html

# Composer'ı kur ve Laravel paketlerini indir
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Klasör izinlerini ayarla
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache