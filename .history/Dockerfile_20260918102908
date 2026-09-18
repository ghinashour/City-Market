FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/city-market-entrypoint
RUN chmod +x /usr/local/bin/city-market-entrypoint \
    && chown -R www-data:www-data /var/www/html

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["city-market-entrypoint"]