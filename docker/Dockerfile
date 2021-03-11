FROM php:7.4-fpm
RUN docker-php-ext-install bcmath
RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev libicu-dev g++ \
    && apt-get install -y git \
    && apt-get install -y unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD php-fpm;
EXPOSE 9000
