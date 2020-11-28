FROM webdevops/php:7.2

RUN pecl install xdebug-2.8.1 && docker-php-ext-enable xdebug

WORKDIR /code

# Create test bucket directory
RUN mkdir -p /code/tmp/bucket
