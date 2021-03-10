FROM php:7.4-fpm
RUN docker-php-ext-install bcmath
RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get install -y git
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install; php-fpm;
EXPOSE 9000
