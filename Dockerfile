FROM php:7.4-fpm  AS vc_php

ARG APP_ENV=dev
ENV APP_ENV=$APP_ENV

RUN apt update && apt install -y --fix-missing acl git zip

RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache

ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /var/www/html

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress --no-suggest; \
	composer clear-cache

# copy only specifically what we need
RUN touch .env.${APP_ENV}.local

COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY .env* ./

COPY docker-services/php/timezone.ini /usr/local/etc/php/conf.d/timezone.ini

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative; \
	composer run-script  post-install-cmd;

RUN chmod +x bin/console; sync

# "nginx" stage
# depends on the "php" stage above
FROM nginx:1.17-alpine AS vc_nginx

COPY docker-services/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html/public

COPY --from=vc_php /var/www/html/public ./
