FROM php:8.2.4RC1-apache

RUN apt update \
        && apt install -y \
            g++ \
            libicu-dev \
            libpq-dev \
            libzip-dev \
            zip \
            zlib1g-dev \
        && docker-php-ext-install \
            intl \
            opcache \
            pdo \
            mysqli \
            pdo_mysql 
RUN a2enmod rewrite
RUN service apache2 restart
RUN mkdir /var/www/logs
WORKDIR /var/www/workdir
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

###########################################
# xDebug
###########################################

ARG XDEBUG_MODE=develop,debug

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN mkdir /var/www/script
COPY deployment/entrypoint.sh /var/www/script/entrypoint.sh
RUN chmod +x /var/www/script/entrypoint.sh

EXPOSE 9000
EXPOSE 3000

ENTRYPOINT [ "/var/www/script/entrypoint.sh" ] 