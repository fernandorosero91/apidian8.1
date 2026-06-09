#!/bin/bash
set -e

echo "============================================"
echo " APIDIAN - PHP-FPM Entrypoint"
echo "============================================"

cd /var/www/html

# [1] Configurar permisos de storage y cache
echo "[1/8] Configurando permisos..."
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

# [2] Instalar dependencias si no existen
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "[2/8] Instalando dependencias de Composer..."
    rm -f composer.lock
    composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
    composer dump-autoload --optimize
else
    echo "[2/8] Dependencias ya instaladas."
fi

# [3] Ejecutar urn_on.sh (copia templates XML y patches al vendor)
echo "[3/8] Ejecutando urn_on.sh (patches de templates DIAN)..."
if [ -f "urn_on.sh" ]; then
    chmod +x urn_on.sh
    bash urn_on.sh
fi

# Permisos para mPDF y DomPDF
if [ -d "vendor/mpdf/mpdf" ]; then
    chmod -R 777 vendor/mpdf/mpdf
fi
if [ -d "vendor/dompdf" ]; then
    chmod -R 777 vendor/dompdf
fi

# [4] Generar APP_KEY si no existe
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "[4/8] Generando APP_KEY..."
    php artisan key:generate --force 2>/dev/null || true
else
    echo "[4/8] APP_KEY proporcionada via variable de entorno."
fi

# [5] Storage link
echo "[5/8] Creando storage link..."
php artisan storage:link --force 2>/dev/null || true

# [6] Ejecutar migraciones
echo "[6/8] Ejecutando migraciones..."
php artisan migrate --seed --force --no-interaction 2>&1 || echo "WARN: Migraciones fallaron o ya ejecutadas"

# [7] Optimizar para producción
echo "[7/8] Optimizando para producción..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true
php artisan package:discover --ansi 2>/dev/null || true

# [8] Precalentar cache de catálogos
echo "[8/8] Precalentando cache..."
php artisan cache:warmup 2>/dev/null || true

echo "============================================"
echo " APIDIAN listo - Iniciando PHP-FPM"
echo "============================================"

# Iniciar PHP-FPM
exec php-fpm
