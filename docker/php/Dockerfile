FROM php:8.1-fpm-bookworm AS base

# Configurar zona horaria
ENV TZ=America/Bogota
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libssl-dev \
    libicu-dev \
    libc-client-dev \
    libkrb5-dev \
    libxslt1-dev \
    libmagickwand-dev \
    imagemagick \
    mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# OpenSSL legacy para certificados DIAN .p12 (RC2-40-CBC)
RUN cp /etc/ssl/openssl.cnf /etc/ssl/openssl.cnf.backup && \
    cat > /etc/ssl/openssl.cnf << 'OPENSSLEOF'
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1

[req]
default_bits = 2048
distinguished_name = req_distinguished_name

[req_distinguished_name]
OPENSSLEOF

ENV OPENSSL_CONF=/etc/ssl/openssl.cnf

# Extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        soap \
        intl \
        imap \
        opcache \
        xsl \
        sockets

# Redis e Imagick
RUN pecl install redis-5.3.7 imagick \
    && docker-php-ext-enable redis imagick

# ============================================
# STAGE: Production
# ============================================
FROM base AS production

# PHP config
COPY docker/php/php-production.ini /usr/local/etc/php/conf.d/99-production.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar TODO el código del proyecto dentro de la imagen
COPY . .

# Entrypoint
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Crear directorios necesarios y permisos
RUN mkdir -p /var/log/php \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/public \
    && mkdir -p storage/app/certificates \
    && mkdir -p storage/received \
    && mkdir -p bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Variables de entorno
ENV OPENSSL_CONF=/etc/ssl/openssl.cnf
ENV TZ=America/Bogota

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
