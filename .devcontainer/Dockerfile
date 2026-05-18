# syntax=docker/dockerfile:1.4
# Magento Open Source 2.4.9 on-premises stack (Adobe): PHP 8.5, MariaDB 11.4, OpenSearch 3, Valkey 8
# https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/system-requirements
#
# Build requires Docker BuildKit and a Magento Marketplace auth file (see docker-compose build secrets).
# Magento CE files live in /opt/magento so they are not hidden by the /workspace bind mount.
FROM mcr.microsoft.com/devcontainers/php:8.5-apache-bookworm

RUN export DEBIAN_FRONTEND=noninteractive \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxslt1-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        ftp \
        gd \
        intl \
        mysqli \
        pdo_mysql \
        soap \
        sockets \
        xsl \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Base devcontainer PHP image ships Xdebug; remove it (no step-debug noise, faster Composer/CLI).
RUN rm -f /usr/local/etc/php/conf.d/xdebug.ini \
    && rm -f /usr/local/lib/php/extensions/*/xdebug.so \
    && rm -rf /usr/local/lib/php/doc/xdebug

RUN a2enmod rewrite headers

# Magento-friendly PHP defaults (tune as needed)
COPY php-magento.ini /usr/local/etc/php/conf.d/99-magento.ini

# Stock 000-default uses DocumentRoot /var/www/html — point it at the stub pub until ensure-var-www-symlink.sh retargets.
COPY magento-docroot.conf /etc/apache2/conf-available/magento-docroot.conf
RUN set -eux; \
    rm -rf /var/www/html; \
    ln -s /workspace/.devcontainer/stub/pub /var/www/html; \
    a2enconf magento-docroot; \
    apache2ctl configtest

# Composer 2.9.3+ (per Adobe 2.4.9)
RUN composer self-update --2 \
    && composer --version

ENV COMPOSER_PROCESS_TIMEOUT=0 \
    COMPOSER_MEMORY_LIMIT=2G \
    COMPOSER_CACHE_DIR=/var/cache/composer

RUN mkdir -p /var/cache/composer && chmod 1777 /var/cache/composer

ARG MAGENTO_VERSION=2.4.9

RUN --mount=type=secret,id=composer_auth \
    --mount=type=cache,target=/root/.composer/cache,id=magento-composer \
    set -eux; \
    mkdir -p /root/.composer; \
    install -m 600 /run/secrets/composer_auth /root/.composer/auth.json; \
    php "$(command -v composer)" create-project \
      --repository-url=https://repo.magento.com/ \
      --prefer-dist \
      --no-dev \
      --no-interaction \
      "magento/project-community-edition:${MAGENTO_VERSION}" \
      /opt/magento; \
    rm -f /root/.composer/auth.json

RUN set -eux; \
    rm -rf /opt/magento/var/cache/* /opt/magento/var/page_cache/* /opt/magento/var/view_preprocessed/*; \
    chown -R www-data:www-data /opt/magento; \
    mkdir -p /opt/magento/pub/static /opt/magento/pub/media; \
    chmod -R g+rwX /opt/magento/vendor /opt/magento/var /opt/magento/generated /opt/magento/pub/static /opt/magento/pub/media /opt/magento/app/etc; \
    find /opt/magento/vendor /opt/magento/var /opt/magento/generated /opt/magento/pub/static /opt/magento/pub/media /opt/magento/app/etc -type d -exec chmod g+s {} +
