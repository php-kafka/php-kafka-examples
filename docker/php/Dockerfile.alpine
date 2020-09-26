ARG PHP_IMAGE_TAG

FROM php:${PHP_IMAGE_TAG}

ARG LIBRDKAFKA_VERSION
ARG PHP_RDKAFKA_VERSION

# Install packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash openssh sudo git gcc g++ make autoconf \
    icu libssl1.1 openssl-dev pcre-dev zlib-dev icu-dev wget gettext valgrind

# Install librdkafka and ext-rdkafka
RUN git clone --depth 1 --branch ${LIBRDKAFKA_VERSION} https://github.com/edenhill/librdkafka.git \
    && cd librdkafka \
    && ./configure \
    && make \
    && make install \
    && git clone --depth 1 --branch ${PHP_RDKAFKA_VERSION} https://github.com/arnaud-lb/php-rdkafka.git \
    && cd php-rdkafka \
    && phpize \
    && ./configure \
    && make all -j 5 \
    && make install \
    && cd ../..;rm -rf librdkafka

# Install php extensions
RUN docker-php-ext-install pcntl && \
    docker-php-ext-enable rdkafka pcntl > /dev/null 2>&1

# Install composer and prestissimo
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && composer global require hirak/prestissimo
