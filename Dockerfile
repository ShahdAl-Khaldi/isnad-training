FROM php:8.3-apache

# مكتبات النظام والإضافات اللي بيحتاجها Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev libsqlite3-dev \
    && docker-php-ext-install zip intl pdo_sqlite opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Apache بيخدم من مجلد public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod +x docker/start.sh

EXPOSE 10000
CMD ["docker/start.sh"]
