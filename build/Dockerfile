FROM hyperf/hyperf:8.3-alpine-v3.19-swoole

ARG timezone

ENV TIMEZONE=${timezone:-"UTC"} \
    APP_ENV=dev \
    SCAN_CACHEABLE=(false)

RUN set -ex \
    && if ! apk add --no-cache php83-pecl-pcov; then \
        apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS:-autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c php83-dev php83-pear linux-headers} \
        && pecl install pcov \
        && apk del --no-cache .build-deps; \
    fi \
    && cd /etc/php* \
    && { \
        echo "extension=pcov.so"; \
        echo "pcov.enabled=1"; \
    } | tee conf.d/10-pcov.ini \
    && php -v \
    && php -m \
    && php --ri swoole \
    && cd /etc/php* \
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"


WORKDIR /opt/www
RUN git config --global --add safe.directory /opt/www

# Composer Cache
# COPY ./composer.* /opt/www/
# RUN composer install --no-dev --no-scripts

COPY . /opt/www
RUN composer install && php bin/hyperf.php

EXPOSE 9501
