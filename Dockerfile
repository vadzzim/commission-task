FROM php:7.4-cli
RUN docker-php-ext-install bcmath
RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD composer install;
