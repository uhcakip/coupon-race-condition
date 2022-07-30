# syntax=docker/dockerfile:1

FROM php:8.1-fpm-alpine3.15

# install core extension
RUN docker-php-ext-install opcache \
    pdo_mysql \
    mysqli

# install redis extension
RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && apk del pcre-dev ${PHPIZE_DEPS} \
  && rm -rf /tmp/pear

# install composer
RUN echo "allow_url_fopen = On" >> /usr/local/etc/php/php.ini \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && alias composer='php /usr/local/bin/composer'

# install phpcs & phpcbf (for editor to format code)
RUN curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar \
    && curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar \
    && chmod 777 ./phpcs.phar \
    && chmod 777 ./phpcbf.phar \
    && mv ./phpcs.phar /usr/local/bin/phpcs \
    && mv ./phpcbf.phar /usr/local/bin/phpcbf \
    && /usr/local/bin/phpcs --config-set show_progress 1 \
    && /usr/local/bin/phpcs --config-set colors 1 \
    && /usr/local/bin/phpcs --config-set report_width 140 \
    && /usr/local/bin/phpcs --config-set encoding utf-8

# install tool
RUN apk add mysql-client vim busybox-extras git openssh

WORKDIR /code

EXPOSE 80
EXPOSE 443