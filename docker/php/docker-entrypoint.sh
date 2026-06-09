#!/bin/bash
set -e

echo "============================================"
echo " APIDIAN - PHP-FPM Entrypoint"
echo "============================================"

cd /var/www/html

# [1] Crear .env desde variables de entorno si no existe
if [ ! -f ".env" ]; then
    echo "[1/8] Generando .env desde variables de entorno..."
    cat > .env << EOF
APP_NAME="${APP_NAME:-APIDIAN}"
APP_VERSION="${APP_VERSION:- v2.1}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
FORCE_HTTPS=${FORCE_HTTPS:-true}

LOG_CHANNEL=${LOG_CHANNEL:-stack}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mariadb}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-apidian}
DB_USERNAME=${DB_USERNAME:-apidian}
DB_PASSWORD=${DB_PASSWORD:-apidian}

BROADCAST_DRIVER=${BROADCAST_DRIVER:-log}
CACHE_DRIVER=${CACHE_DRIVER:-redis}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
SESSION_DRIVER=${SESSION_DRIVER:-redis}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}

REDIS_HOST=${REDIS_HOST:-redis}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_CLIENT=${REDIS_CLIENT:-phpredis}

MAIL_DRIVER=${MAIL_MAILER:-smtp}
MAIL_HOST=${MAIL_HOST:-}
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME=${MAIL_USERNAME:-}
MAIL_PASSWORD=${MAIL_PASSWORD:-}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-}
MAIL_FROM_NAME="${MAIL_FROM_NAME:-APIDIAN}"

ALLOW_PUBLIC_DOWNLOAD=${ALLOW_PUBLIC_DOWNLOAD:-true}
APPLY_SEND_CUSTOMER_CREDENTIALS=${APPLY_SEND_CUSTOMER_CREDENTIALS:-true}
GRAPHIC_REPRESENTATION_TEMPLATE=${GRAPHIC_REPRESENTATION_TEMPLATE:-2}
ALLOW_PUBLIC_REGISTER=${ALLOW_PUBLIC_REGISTER:-true}
VALIDATE_BEFORE_SENDING=${VALIDATE_BEFORE_SENDING:-true}
SAVE_RESPONSE_DIAN_TO_DB=${SAVE_RESPONSE_DIAN_TO_DB:-false}
ENABLE_API_REGISTER=${ENABLE_API_REGISTER:-true}
EOF
else
    echo "[1/8] .env ya existe."
fi

# [2] Permisos
echo "[2/8] Configurando permisos..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/app/certificates
mkdir -p storage/received
mkdir -p bootstrap/cache
mkdir -p /var/log/php

chmod -R 777 storage
chmod -R 777 bootstrap/cache
touch storage/logs/laravel.log
chmod 777 storage/logs/laravel.log

# [3] Instalar dependencias si no existen
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "[3/8] Instalando dependencias de Composer..."
    rm -f composer.lock
    composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    composer dump-autoload --optimize
else
    echo "[3/8] Dependencias ya instaladas."
fi

# [4] Ejecutar urn_on.sh (patches de templates DIAN)
echo "[4/8] Ejecutando urn_on.sh (patches de templates DIAN)..."
if [ -f "urn_on.sh" ]; then
    chmod +x urn_on.sh
    bash urn_on.sh
fi

# Permisos para mPDF
if [ -d "vendor/mpdf/mpdf" ]; then
    chmod -R 777 vendor/mpdf/mpdf
fi
if [ -d "vendor/dompdf" ]; then
    chmod -R 777 vendor/dompdf
fi

# [5] Generar APP_KEY si no existe
if grep -q "^APP_KEY=$" .env 2>/dev/null; then
    echo "[5/8] Generando APP_KEY..."
    php artisan key:generate --force
else
    echo "[5/8] APP_KEY ya configurada."
fi

# [6] Storage link
echo "[6/8] Creando storage link..."
php artisan storage:link --force 2>/dev/null || true

# [7] Ejecutar migraciones
echo "[7/8] Ejecutando migraciones..."
php artisan migrate --seed --force --no-interaction 2>&1 || echo "WARN: Migraciones fallaron o ya ejecutadas"

# [8] Optimizar para producción
echo "[8/8] Optimizando para producción..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true
php artisan package:discover --ansi 2>/dev/null || true
php artisan cache:warmup 2>/dev/null || true

echo "============================================"
echo " APIDIAN listo - Iniciando PHP-FPM"
echo "============================================"

exec php-fpm
