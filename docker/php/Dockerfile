FROM php:8.1-fpm-bookworm AS base

# Configurar zona horaria del sistema (Colombia UTC-5)
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

# Configurar OpenSSL para soportar algoritmos legacy
# CRÍTICO para certificados DIAN .p12 que usan RC2-40-CBC
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

# Configurar e instalar extensiones PHP
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

# Instalar extensiones PECL
RUN pecl install redis-5.3.7 imagick \
    && docker-php-ext-enable redis imagick

# ============================================
# STAGE: Production
# ============================================
FROM base AS production

# Configuración PHP para producción
COPY php-production.ini /usr/local/etc/php/conf.d/99-production.ini

# Copiar entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Variables de entorno
ENV OPENSSL_CONF=/etc/ssl/openssl.cnf
ENV TZ=America/Bogota

WORKDIR /var/www/html

# Copiar composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear directorios con permisos correctos
RUN mkdir -p /var/log/php \
    && mkdir -p /var/www/html/storage \
    && mkdir -p /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# ============================================
# STAGE: Development
# ============================================
FROM base AS development

RUN pecl install xdebug-3.2.2 \
    && docker-php-ext-enable xdebug

COPY php-development.ini /usr/local/etc/php/conf.d/99-development.ini

ENV OPENSSL_CONF=/etc/ssl/openssl.cnf
ENV TZ=America/Bogota

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 9000

CMD ["php-fpm"]
