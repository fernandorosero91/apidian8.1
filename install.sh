#!/bin/bash
#===============================================
# APIDIAN - Instalación Automática con Docker
# Compatible con Ubuntu 20.04/22.04/24.04 LTS
# PHP 8.1 + Nginx + MariaDB + Redis + SSL
#===============================================

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuración por defecto
INSTALL_DIR="${INSTALL_DIR:-/var/www/apidian}"
DOMAIN="${DOMAIN:-}"
EMAIL="${EMAIL:-admin@example.com}"
DB_DATABASE="${DB_DATABASE:-apidian}"
DB_USERNAME="${DB_USERNAME:-apidian}"
DB_PASSWORD="${DB_PASSWORD:-$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 16)}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 16)}"

print_banner() {
    echo -e "${CYAN}"
    echo "╔═══════════════════════════════════════════════════════════╗"
    echo "║                                                           ║"
    echo "║     █████╗ ██████╗ ██╗██████╗ ██╗ █████╗ ███╗   ██╗      ║"
    echo "║    ██╔══██╗██╔══██╗██║██╔══██╗██║██╔══██╗████╗  ██║      ║"
    echo "║    ███████║██████╔╝██║██║  ██║██║███████║██╔██╗ ██║      ║"
    echo "║    ██╔══██║██╔═══╝ ██║██║  ██║██║██╔══██║██║╚██╗██║      ║"
    echo "║    ██║  ██║██║     ██║██████╔╝██║██║  ██║██║ ╚████║      ║"
    echo "║    ╚═╝  ╚═╝╚═╝     ╚═╝╚═════╝ ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝      ║"
    echo "║                                                           ║"
    echo "║         Facturación Electrónica DIAN Colombia             ║"
    echo "║              PHP 8.1 + Laravel 10 + Docker                ║"
    echo "║                                                           ║"
    echo "╚═══════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

check_root() {
    if [ "$EUID" -ne 0 ]; then
        log_error "Este script debe ejecutarse como root"
        echo "Ejecuta: sudo ./install.sh"
        exit 1
    fi
}

get_domain() {
    if [ -z "$DOMAIN" ]; then
        echo ""
        echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
        read -p "Ingresa el dominio para SSL (ej: apidian.tudominio.com): " DOMAIN
        read -p "Ingresa tu email para Let's Encrypt: " EMAIL
        echo -e "${YELLOW}═══════════════════════════════════════════════════════════${NC}"
        echo ""
    fi
    
    if [ -z "$DOMAIN" ]; then
        log_error "El dominio es requerido para SSL"
        exit 1
    fi
    
    log_success "Dominio configurado: $DOMAIN"
}

check_system() {
    log_info "Verificando sistema operativo..."
    
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$NAME
        VER=$VERSION_ID
    else
        log_error "No se puede detectar el sistema operativo"
        exit 1
    fi
    
    log_success "Sistema: $OS $VER"
}

stop_conflicting_services() {
    log_info "Deteniendo servicios que pueden causar conflictos..."
    
    # Detener y deshabilitar Redis
    systemctl stop redis-server 2>/dev/null || true
    systemctl disable redis-server 2>/dev/null || true
    systemctl stop redis 2>/dev/null || true
    systemctl disable redis 2>/dev/null || true
    
    # Detener y deshabilitar MySQL/MariaDB
    systemctl stop mysql 2>/dev/null || true
    systemctl disable mysql 2>/dev/null || true
    systemctl stop mariadb 2>/dev/null || true
    systemctl disable mariadb 2>/dev/null || true
    
    # Detener y deshabilitar Nginx/Apache (IMPORTANTE: Docker usa sus propios)
    systemctl stop nginx 2>/dev/null || true
    systemctl disable nginx 2>/dev/null || true
    systemctl stop apache2 2>/dev/null || true
    systemctl disable apache2 2>/dev/null || true
    
    # Detener PHP-FPM del sistema (Docker usa su propio PHP)
    systemctl stop php8.1-fpm 2>/dev/null || true
    systemctl disable php8.1-fpm 2>/dev/null || true
    systemctl stop php8.0-fpm 2>/dev/null || true
    systemctl disable php8.0-fpm 2>/dev/null || true
    systemctl stop php7.4-fpm 2>/dev/null || true
    systemctl disable php7.4-fpm 2>/dev/null || true
    
    # Matar procesos que usen los puertos necesarios
    fuser -k 80/tcp 2>/dev/null || true
    fuser -k 443/tcp 2>/dev/null || true
    fuser -k 3306/tcp 2>/dev/null || true
    fuser -k 6379/tcp 2>/dev/null || true
    
    sleep 2
    log_success "Puertos liberados y servicios del sistema deshabilitados"
}

install_dependencies() {
    log_info "Instalando dependencias del sistema..."
    apt-get update
    apt-get install -y curl git unzip dnsutils psmisc
    log_success "Dependencias instaladas"
}

install_docker() {
    log_info "Instalando Docker..."
    
    if command -v docker &> /dev/null; then
        log_success "Docker ya está instalado: $(docker --version)"
    else
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
        
        echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
        
        apt-get update
        apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
        
        systemctl enable docker
        systemctl start docker
        
        log_success "Docker instalado correctamente"
    fi
}

install_certbot() {
    log_info "Instalando Certbot para SSL..."
    
    if command -v certbot &> /dev/null; then
        log_success "Certbot ya está instalado"
    else
        apt-get install -y certbot
        log_success "Certbot instalado"
    fi
}

prepare_project() {
    log_info "Preparando directorio de instalación..."
    
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    
    if [ -f "$SCRIPT_DIR/composer.json" ] && [ -f "$SCRIPT_DIR/artisan" ]; then
        if [ "$SCRIPT_DIR" = "$INSTALL_DIR" ]; then
            log_info "Ejecutando desde el directorio de instalación"
            cd "$INSTALL_DIR"
            return
        fi
        
        if [ -d "$INSTALL_DIR" ]; then
            log_warning "El directorio $INSTALL_DIR ya existe, eliminando..."
            rm -rf "$INSTALL_DIR"
        fi
        
        mkdir -p "$INSTALL_DIR"
        log_info "Copiando archivos del proyecto..."
        cp -r "$SCRIPT_DIR/." "$INSTALL_DIR/"
        cd "$INSTALL_DIR"
    else
        log_error "No se encontró el proyecto APIDIAN"
        exit 1
    fi
    
    log_success "Proyecto preparado en $INSTALL_DIR"
}

obtain_ssl_certificate() {
    log_info "Obteniendo certificado SSL para $DOMAIN..."
    
    certbot certonly --standalone \
        --non-interactive \
        --agree-tos \
        --email "$EMAIL" \
        --domain "$DOMAIN" \
        --preferred-challenges http
    
    if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
        log_success "Certificado SSL obtenido correctamente"
    else
        log_error "No se pudo obtener el certificado SSL"
        exit 1
    fi
}

configure_nginx_ssl() {
    log_info "Configurando Nginx con SSL..."
    
    sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" "$INSTALL_DIR/docker/nginx/sites-available/default.conf"
    
    log_success "Nginx configurado con SSL para $DOMAIN"
}

create_env_file() {
    log_info "Creando archivo de configuración .env..."
    
    cat > "$INSTALL_DIR/.env" << EOF
APP_NAME="APIDIAN"
APP_VERSION=" v2.1"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}
FORCE_HTTPS=true

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="\${APP_NAME}"

ALLOW_PUBLIC_DOWNLOAD=true
APPLY_SEND_CUSTOMER_CREDENTIALS=true
GRAPHIC_REPRESENTATION_TEMPLATE=2
ALLOW_PUBLIC_REGISTER=true
VALIDATE_BEFORE_SENDING=true
SAVE_RESPONSE_DIAN_TO_DB=false
ENABLE_API_REGISTER=true
EOF

    log_success "Archivo .env creado"
}

create_directories() {
    log_info "Creando directorios necesarios..."
    
    mkdir -p "$INSTALL_DIR/docker/mariadb/init"
    mkdir -p "$INSTALL_DIR/storage/framework/cache/data"
    mkdir -p "$INSTALL_DIR/storage/framework/sessions"
    mkdir -p "$INSTALL_DIR/storage/framework/views"
    mkdir -p "$INSTALL_DIR/storage/logs"
    mkdir -p "$INSTALL_DIR/storage/app/public"
    mkdir -p "$INSTALL_DIR/storage/app/certificates"
    mkdir -p "$INSTALL_DIR/storage/app/xml"
    mkdir -p "$INSTALL_DIR/storage/received"
    mkdir -p "$INSTALL_DIR/bootstrap/cache"
    
    cat > "$INSTALL_DIR/docker/mariadb/init/01-init.sql" << EOF
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;
EOF

    log_success "Directorios creados"
}

set_permissions() {
    log_info "Configurando permisos..."
    
    chmod -R 777 "$INSTALL_DIR/storage"
    chmod -R 777 "$INSTALL_DIR/bootstrap/cache"
    
    log_success "Permisos configurados"
}

build_containers() {
    log_info "Construyendo contenedores Docker..."
    
    cd "$INSTALL_DIR"
    docker compose build --no-cache
    
    log_success "Contenedores construidos"
}

start_containers() {
    log_info "Iniciando contenedores..."
    
    cd "$INSTALL_DIR"
    docker compose up -d
    
    log_info "Esperando a que los servicios estén listos..."
    sleep 20
    
    log_success "Contenedores iniciados"
}

wait_for_database() {
    log_info "Esperando a que MariaDB esté lista..."
    
    for i in {1..60}; do
        if docker compose exec -T mariadb mysqladmin ping -h localhost -u root -p"${DB_ROOT_PASSWORD}" --silent 2>/dev/null; then
            log_success "MariaDB está lista"
            return 0
        fi
        echo -n "."
        sleep 2
    done
    
    log_error "MariaDB no respondió a tiempo"
    exit 1
}

run_installation() {
    log_info "Ejecutando instalación de Laravel..."
    
    cd "$INSTALL_DIR"
    
    wait_for_database
    
    # Verificar OpenSSL legacy providers
    log_info "Verificando OpenSSL legacy providers..."
    docker compose exec -T php php -r "print_r(openssl_get_providers());" 2>/dev/null || log_warning "No se pudo verificar OpenSSL"
    
    # Eliminar composer.lock para generar uno nuevo compatible con PHP del contenedor
    log_info "Regenerando dependencias para PHP 8.1..."
    docker compose exec -T php rm -f composer.lock
    
    # Eliminar vendor viejo si existe para forzar reinstalación limpia
    log_info "Limpiando vendor anterior..."
    docker compose exec -T php rm -rf vendor 2>/dev/null || true
    
    log_info "Instalando dependencias de Composer..."
    docker compose exec -T php composer install --no-dev --optimize-autoloader --no-scripts 2>&1 | tail -30
    
    # Ejecutar scripts de composer manualmente (evita error 255)
    log_info "Ejecutando post-install scripts..."
    docker compose exec -T php composer dump-autoload --optimize 2>/dev/null || true
    
    log_info "Generando APP_KEY..."
    docker compose exec -T php php artisan key:generate --force
    
    # Crear archivo de log ANTES de cualquier operación
    log_info "Creando archivos de log..."
    docker compose exec -T php mkdir -p /var/www/html/storage/logs
    docker compose exec -T php touch /var/www/html/storage/logs/laravel.log
    docker compose exec -T php chmod 777 /var/www/html/storage/logs/laravel.log
    
    # Permisos dentro del contenedor (CRÍTICO para mPDF, storage y QR codes)
    log_info "Configurando permisos en contenedor..."
    docker compose exec -T php chmod -R 777 /var/www/html/storage
    docker compose exec -T php chmod -R 777 /var/www/html/bootstrap/cache
    
    # Crear directorios de framework si no existen
    docker compose exec -T php mkdir -p /var/www/html/storage/framework/cache/data
    docker compose exec -T php mkdir -p /var/www/html/storage/framework/sessions
    docker compose exec -T php mkdir -p /var/www/html/storage/framework/views
    docker compose exec -T php chmod -R 777 /var/www/html/storage/framework
    
    # Permisos para mPDF (después de composer install)
    docker compose exec -T php bash -c "if [ -d /var/www/html/vendor/mpdf ]; then chmod -R 777 /var/www/html/vendor/mpdf; fi"
    
    # Permisos para tecnickcom/tcpdf (alternativa a mPDF)
    docker compose exec -T php bash -c "if [ -d /var/www/html/vendor/tecnickcom ]; then chmod -R 777 /var/www/html/vendor/tecnickcom; fi"
    
    # Permisos para dompdf
    docker compose exec -T php bash -c "if [ -d /var/www/html/vendor/dompdf ]; then chmod -R 777 /var/www/html/vendor/dompdf; fi"
    
    if [ -f "$INSTALL_DIR/storage.zip" ]; then
        log_info "Descomprimiendo storage.zip..."
        docker compose exec -T php unzip -o storage.zip -d /var/www/html/ 2>/dev/null || true
        docker compose exec -T php chmod -R 777 /var/www/html/storage
    fi
    
    log_info "Creando storage link..."
    docker compose exec -T php php artisan storage:link --force 2>/dev/null || true
    
    log_info "Ejecutando migraciones..."
    docker compose exec -T php php artisan migrate --seed --force 2>&1 | tail -10
    
    if [ -f "$INSTALL_DIR/urn_on.sh" ]; then
        log_info "Ejecutando urn_on.sh..."
        docker compose exec -T php bash -c "chmod +x /var/www/html/urn_on.sh && /var/www/html/urn_on.sh" 2>/dev/null || true
    fi
    
    # Descubrir paquetes manualmente
    log_info "Descubriendo paquetes Laravel..."
    docker compose exec -T php php artisan package:discover --ansi 2>/dev/null || log_warning "package:discover falló, continuando..."
    
    log_info "Limpiando y optimizando cache..."
    docker compose exec -T php php artisan config:clear 2>/dev/null || true
    docker compose exec -T php php artisan cache:clear 2>/dev/null || true
    docker compose exec -T php php artisan view:clear 2>/dev/null || true
    docker compose exec -T php php artisan route:clear 2>/dev/null || true
    
    # Verificar extensiones PHP instaladas
    log_info "Verificando extensiones PHP..."
    docker compose exec -T php php -m | grep -E "(imagick|redis|soap|gd)" || log_warning "Algunas extensiones pueden no estar instaladas"
    
    # Verificar timezone
    log_info "Verificando timezone..."
    docker compose exec -T php date
    
    log_success "Instalación de Laravel completada"
}

set_admin_password() {
    log_info "Configurando contraseña del administrador..."
    
    ADMIN_PASS="Admin$(openssl rand -base64 6 | tr -dc 'a-zA-Z0-9' | head -c 6)!"
    
    docker compose exec -T php php artisan tinker --execute="\$user = App\User::where('email', 'admin@gmail.com')->first(); if(\$user) { \$user->password = Hash::make('${ADMIN_PASS}'); \$user->save(); }" 2>/dev/null || true
    
    log_success "Contraseña del administrador configurada"
}

setup_ssl_renewal() {
    log_info "Configurando renovación automática de SSL..."
    
    cat > /usr/local/bin/renew-apidian-ssl.sh << 'EOF'
#!/bin/bash
certbot renew --quiet
docker exec apidian_nginx nginx -s reload 2>/dev/null || true
EOF
    
    chmod +x /usr/local/bin/renew-apidian-ssl.sh
    
    (crontab -l 2>/dev/null | grep -v "renew-apidian-ssl"; echo "0 3 * * * /usr/local/bin/renew-apidian-ssl.sh") | crontab -
    
    log_success "Renovación automática de SSL configurada"
}

save_credentials() {
    log_info "Guardando credenciales..."
    
    cat > "$INSTALL_DIR/CREDENCIALES.txt" << EOF
==========================================
CREDENCIALES APIDIAN
Fecha: $(date)
==========================================

URL de la API: https://${DOMAIN}

ADMINISTRADOR:
  Email: admin@gmail.com
  Password: ${ADMIN_PASS}

BASE DE DATOS (solo accesible desde Docker):
  Host: mariadb (interno)
  Database: ${DB_DATABASE}
  Usuario: ${DB_USERNAME}
  Password: ${DB_PASSWORD}
  Root Password: ${DB_ROOT_PASSWORD}

REDIS (solo accesible desde Docker):
  Host: redis (interno)

SSL:
  Certificado: /etc/letsencrypt/live/${DOMAIN}/
  Renovación automática: Configurada (cron diario 3am)

HEALTH CHECK:
  https://${DOMAIN}/api/health
  https://${DOMAIN}/api/health/status

COMANDOS ÚTILES:
  cd /var/www/apidian
  docker compose logs -f          # Ver logs
  docker compose restart          # Reiniciar
  docker compose down             # Detener
  docker compose up -d            # Iniciar
  docker compose exec php bash    # Acceder a PHP

==========================================
EOF

    chmod 600 "$INSTALL_DIR/CREDENCIALES.txt"
    log_success "Credenciales guardadas en $INSTALL_DIR/CREDENCIALES.txt"
}

print_summary() {
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║          ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!            ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "URL de la API: ${CYAN}https://${DOMAIN}${NC}"
    echo ""
    echo -e "Administrador:"
    echo -e "  Email:    ${YELLOW}admin@gmail.com${NC}"
    echo -e "  Password: ${YELLOW}${ADMIN_PASS}${NC}"
    echo ""
    echo -e "Base de datos:"
    echo -e "  Database: ${YELLOW}${DB_DATABASE}${NC}"
    echo -e "  Usuario:  ${YELLOW}${DB_USERNAME}${NC}"
    echo -e "  Password: ${YELLOW}${DB_PASSWORD}${NC}"
    echo ""
    echo -e "Credenciales guardadas en: ${CYAN}$INSTALL_DIR/CREDENCIALES.txt${NC}"
    echo ""
    echo -e "Health Check: ${CYAN}https://${DOMAIN}/api/health${NC}"
    echo ""
}

# ============================================
# MAIN
# ============================================
main() {
    print_banner
    
    check_root
    get_domain
    check_system
    stop_conflicting_services
    install_dependencies
    install_docker
    install_certbot
    prepare_project
    obtain_ssl_certificate
    configure_nginx_ssl
    create_env_file
    create_directories
    set_permissions
    build_containers
    start_containers
    run_installation
    set_admin_password
    setup_ssl_renewal
    save_credentials
    print_summary
}

main "$@"
