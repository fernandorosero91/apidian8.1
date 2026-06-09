# 🚀 Guía de Despliegue en Producción - APIDIAN

## Pre-requisitos

- PHP 8.1+ con extensiones: openssl, soap, xml, zip, gd, pdo_mysql, redis
- MySQL 5.7+ / MariaDB 10.3+
- Redis 6+ (recomendado para mejor rendimiento)
- Composer 2.x
- Servidor web (Nginx recomendado o Apache)
- Certificado SSL válido

## Pasos de Despliegue

### 1. Configuración del Entorno

```bash
# Copiar archivo de configuración de producción
cp .env.production.example .env

# Generar clave de aplicación
php artisan key:generate

# Editar .env con tus valores reales
nano .env
```

### 2. Configuración Crítica en .env

```env
APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true

# Deshabilitar registro público si no es necesario
ENABLE_API_REGISTER=false

# Habilitar validación de duplicados
VALIDATE_BEFORE_SENDING=true

# Redis para mejor rendimiento (recomendado)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

### 3. Optimización para Producción

```bash
# Instalar dependencias sin dev
composer install --no-dev --optimize-autoloader

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Ejecutar migraciones
php artisan migrate --force
```

### 4. Permisos de Directorios

```bash
# Permisos de storage y cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Crear directorios necesarios
mkdir -p storage/app/public
mkdir -p storage/app/certificates
mkdir -p storage/app/xml
mkdir -p storage/received
```

### 5. Configuración de Nginx (Recomendada)

```nginx
server {
    listen 443 ssl http2;
    server_name tu-dominio.com;
    root /var/www/apidian/public;

    ssl_certificate /etc/ssl/certs/tu-certificado.crt;
    ssl_certificate_key /etc/ssl/private/tu-certificado.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Aumentar límites para archivos grandes
    client_max_body_size 50M;
}
```

### 6. Instalación de Redis (Recomendado)

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server

# Iniciar y habilitar Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Verificar que Redis funciona
redis-cli ping
# Debe responder: PONG

# Instalar extensión PHP Redis
sudo apt install php8.1-redis
sudo systemctl restart php8.1-fpm
```

### 7. Cron Jobs (Tareas Programadas)

```bash
# Agregar al crontab
crontab -e

# Agregar estas líneas:
# Limpieza de logs cada domingo a las 3am
0 3 * * 0 cd /var/www/apidian && php artisan logs:clean --days=90 --force

# Verificar certificados diariamente a las 8am
0 8 * * * cd /var/www/apidian && php artisan certificates:check --days=30

# Scheduler de Laravel (si usas colas)
* * * * * cd /var/www/apidian && php artisan schedule:run >> /dev/null 2>&1
```

## Comandos Útiles

```bash
# Verificar estado de la API
curl https://tu-dominio.com/api/health

# Verificar estado completo
curl https://tu-dominio.com/api/health/status

# Verificar estado de servicios DIAN
curl https://tu-dominio.com/api/health/dian

# Precalentar cache de catálogos (mejora rendimiento)
php artisan cache:warmup

# Verificar certificados próximos a vencer
php artisan certificates:check --days=30

# Limpiar logs antiguos (simulación)
php artisan logs:clean --days=30 --dry-run

# Limpiar logs antiguos (ejecutar)
php artisan logs:clean --days=30

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Checklist de Seguridad

- [ ] `APP_DEBUG=false`
- [ ] `FORCE_HTTPS=true`
- [ ] Certificado SSL válido instalado
- [ ] `ENABLE_API_REGISTER=false` (si no se necesita)
- [ ] Contraseña de BD segura (no la de ejemplo)
- [ ] Firewall configurado (solo puertos 80, 443)
- [ ] Backups automáticos configurados
- [ ] Logs rotados automáticamente
- [ ] Rate limiting habilitado

## Monitoreo

### Health Check Endpoints

- `GET /api/health` - Ping básico
- `GET /api/health/status` - Estado completo de servicios (BD, Redis, Storage, Cache)
- `GET /api/health/dian` - Estado de servicios DIAN (producción y pruebas)

### Logs

Los logs se encuentran en:
- `storage/logs/laravel.log` - Log general
- Tabla `logs` en BD - Peticiones API

## Troubleshooting

### Error 500 después del despliegue
```bash
php artisan config:clear
php artisan cache:clear
chmod -R 775 storage
```

### Certificado no encontrado
```bash
ls -la storage/app/certificates/
# Verificar que el archivo .p12 existe y tiene permisos
```

### Conexión DIAN fallida
- Verificar que el servidor puede conectar a `vpfe.dian.gov.co`
- Verificar que el certificado no está vencido
- Verificar que el PIN del software es correcto

## Soporte

Para problemas técnicos, revisar:
1. `storage/logs/laravel.log`
2. Tabla `logs` en la base de datos
3. Respuestas de la DIAN en el campo `response_api` de documentos


---

## 🐳 Despliegue con Docker (Recomendado)

### Instalación Automática en VPS

```bash
# 1. Subir el proyecto al servidor
scp -r apidian8.1 usuario@servidor:/tmp/

# 2. Conectar al servidor
ssh usuario@servidor

# 3. Ejecutar instalación automática
cd /tmp/apidian8.1
sudo chmod +x install.sh
sudo ./install.sh
```

### Variables Personalizables

```bash
export INSTALL_DIR=/var/www/apidian    # Directorio de instalación
export APP_PORT=80                      # Puerto de la API
export DB_PORT=3306                     # Puerto de MariaDB
export DB_DATABASE=apidian              # Nombre de la base de datos
export DB_USERNAME=apidian              # Usuario de la base de datos
export DB_PASSWORD=tu_password          # Password (auto-generado si no se especifica)
sudo ./install.sh
```

### Comandos Docker Post-Instalación

```bash
cd /var/www/apidian

# Ver logs en tiempo real
docker compose logs -f

# Reiniciar servicios
docker compose restart

# Detener/Iniciar
docker compose down
docker compose up -d

# Ejecutar comandos artisan
docker compose exec php php artisan migrate
docker compose exec php php artisan cache:clear

# Acceder al contenedor PHP
docker compose exec php sh

# Acceder a MariaDB
docker compose exec mariadb mysql -u apidian -p
```

### Estructura de Contenedores

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| nginx | 80 | Servidor web optimizado |
| php | 9000 | PHP-FPM 8.1 con OPcache JIT |
| mariadb | 3306 | Base de datos |
| redis | 6379 | Cache y sesiones |

### Backup con Docker

```bash
# Backup de base de datos
docker compose exec mariadb mysqldump -u root -p apidian > backup.sql

# Backup de archivos
tar -czvf storage_backup.tar.gz /var/www/apidian/storage
```

### Actualización con Docker

```bash
cd /var/www/apidian
docker compose down
# Actualizar archivos del proyecto
docker compose build --no-cache
docker compose up -d
docker compose exec php composer install --no-dev --optimize-autoloader
docker compose exec php php artisan migrate --force
docker compose exec php php artisan config:cache
```
