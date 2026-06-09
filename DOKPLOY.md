# 🚀 Deploy APIDIAN en Dokploy

## Requisitos

- VPS Ubuntu 20+ (Hetzner, Contabo, AWS, GCP, DigitalOcean)
- Dokploy instalado en el VPS
- Dominio con DNS A record apuntando al servidor
- Mínimo 2GB RAM, 2 vCPU

---

## Paso 1: Crear servicio Compose en Dokploy

1. Panel Dokploy → **Projects** → **Create Project** → nombre: `apidian`
2. Dentro del proyecto → **Create Service** → **Compose**
3. Configurar:
   - **Source Type**: Git → GitHub
   - **Repository**: `https://github.com/fernandorosero91/apidian8.1.git`
   - **Branch**: `main`
   - **Compose Path**: `./docker-compose.yml`

---

## Paso 2: Variables de Entorno

En la pestaña **Environment** pegar:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=https://tu-dominio.com
APP_PORT=80
FORCE_HTTPS=true

DB_DATABASE=apidian
DB_USERNAME=apidian
DB_PASSWORD=CambiarPorPasswordSeguro123
DB_ROOT_PASSWORD=CambiarRootPassword456

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu-usuario-smtp
MAIL_PASSWORD=tu-password-smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=facturacion@tu-dominio.com
MAIL_FROM_NAME=APIDIAN

ALLOW_PUBLIC_DOWNLOAD=true
APPLY_SEND_CUSTOMER_CREDENTIALS=true
GRAPHIC_REPRESENTATION_TEMPLATE=2
VALIDATE_BEFORE_SENDING=true
ENABLE_API_REGISTER=true
ALLOW_PUBLIC_REGISTER=true
SAVE_RESPONSE_DIAN_TO_DB=false
```

> `APP_KEY` se genera automáticamente en el primer deploy.

---

## Paso 3: Configurar Dominio

En la pestaña **Domains** del servicio compose:

| Campo | Valor |
|-------|-------|
| Host | `tu-dominio.com` |
| Path | `/` |
| Container Port | `80` |
| Service Name | `nginx` |
| HTTPS | ✅ Activar |
| Certificate | Let's Encrypt (auto) |

---

## Paso 4: Deploy

Click en **Deploy**. El proceso automático:

1. Clona el repositorio desde GitHub
2. Construye imagen PHP 8.1 con extensiones (soap, redis, imagick, gd, imap, etc.)
3. Configura OpenSSL legacy (para certificados DIAN .p12)
4. Levanta MariaDB 10.11 y Redis 7
5. Instala dependencias Composer
6. Ejecuta `urn_on.sh` (patches de templates XML DIAN)
7. Asigna permisos 777 a storage, bootstrap/cache, vendor/mpdf
8. Genera APP_KEY
9. Ejecuta migraciones + seeders
10. Optimiza cache de config/rutas/vistas
11. Precalienta cache de catálogos DIAN
12. Inicia PHP-FPM + Nginx

**Primer deploy: ~5-10 minutos** (por compilación de extensiones PHP).
**Deploys siguientes: ~1-2 minutos**.

---

## Paso 5: Verificar

```bash
# Health check básico
curl https://tu-dominio.com/api/health
# → {"status":"ok","timestamp":"..."}

# Estado completo (BD, Redis, Storage, Cache)
curl https://tu-dominio.com/api/health/status

# Estado servicios DIAN
curl https://tu-dominio.com/api/health/dian
```

---

## Qué hace el entrypoint automáticamente

El contenedor PHP al iniciar ejecuta:

| Paso | Acción | Equivalente manual |
|------|--------|--------------------|
| 1 | Crear directorios y permisos 777 | `chmod -R 777 storage bootstrap/cache` |
| 2 | Instalar composer (si no existe vendor) | `rm composer.lock && composer install` |
| 3 | Ejecutar urn_on.sh | `./urn_on.sh` (copia templates XML) |
| 4 | Permisos vendor/mpdf | `chmod -R 777 vendor/mpdf/mpdf` |
| 5 | Generar APP_KEY | `php artisan key:generate` |
| 6 | Storage link | `php artisan storage:link` |
| 7 | Migraciones + seed | `php artisan migrate --seed --force` |
| 8 | Cache optimización | `php artisan config:cache && route:cache && view:cache` |
| 9 | Precalentar catálogos | `php artisan cache:warmup` |

---

## Comandos útiles desde Terminal Dokploy

Acceder via **Dokploy → Service → Terminal** → seleccionar contenedor `apidian_php`:

```bash
# Limpiar todo el cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Re-optimizar después de cambios
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Ejecutar migraciones nuevas
php artisan migrate --force

# Ver certificados próximos a vencer
php artisan certificates:check --days=30

# Limpiar logs antiguos (más de 90 días)
php artisan logs:clean --days=90 --force

# Verificar extensiones PHP instaladas
php -m | grep -E "soap|redis|imagick|gd|imap|intl|zip"

# Verificar OpenSSL legacy (necesario para DIAN)
php -r "print_r(openssl_get_providers());"
```

Para acceder a MariaDB (contenedor `apidian_mariadb`):
```bash
mysql -u apidian -p apidian
```

---

## Actualización del API

1. Push cambios al branch `main` en GitHub
2. En Dokploy → Servicio → **Redeploy**
3. Todo se ejecuta automáticamente (migraciones, permisos, cache)

O configurar **Auto Deploy** en Dokploy para que haga deploy automático en cada push a `main`.

---

## Backups

### Base de datos (desde terminal contenedor mariadb)
```bash
mysqldump -u root -p$DB_ROOT_PASSWORD apidian > /var/lib/mysql/backup_$(date +%Y%m%d).sql
```

### Restaurar backup
```bash
mysql -u root -p$DB_ROOT_PASSWORD apidian < /var/lib/mysql/backup.sql
```

### Archivos importantes a respaldar
- `storage/app/public/` → XMLs, PDFs y logos de empresas
- `storage/app/certificates/` → Certificados .p12 de la DIAN
- `storage/received/` → Documentos recibidos

---

## Troubleshooting

| Problema | Solución |
|----------|----------|
| Error 502 Bad Gateway | PHP aún iniciando. Esperar 60s y recargar |
| Error 500 | `php artisan config:clear && php artisan cache:clear` |
| Certificado DIAN no lee | Verificar `.p12` en `storage/app/certificates/` con permisos de lectura |
| SOAP timeout | Verificar conectividad a `vpfe.dian.gov.co` desde el contenedor |
| PDF no genera | Verificar `chmod -R 777 vendor/mpdf/mpdf` |
| Redis connection refused | Verificar que el contenedor redis esté healthy |
| Migraciones fallan | Verificar que MariaDB esté lista: `php artisan migrate:status` |

---

## Arquitectura

```
[Internet]
    ↓
[Dokploy / Traefik] ← SSL automático Let's Encrypt
    ↓ :80
[Nginx 1.25] ← archivos estáticos + proxy
    ↓ :9000
[PHP-FPM 8.1] ← Laravel 10 + OPcache JIT
    ↓         ↓
[MariaDB 10.11]  [Redis 7]
  :3306           :6379
(solo red interna, no expuestos)
```
