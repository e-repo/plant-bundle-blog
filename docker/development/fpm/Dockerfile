FROM php:8.2-fpm-alpine

RUN apk add --no-cache autoconf build-base linux-headers imagemagick-dev \
    && pecl install xdebug-3.4.7 imagick \
    && docker-php-ext-enable xdebug imagick

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql intl

COPY ./conf.d/ $PHP_INI_DIR/conf.d/
COPY ./pool.d/*.conf /usr/local/etc/php-fpm.d/

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /app

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
