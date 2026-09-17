#!/usr/bin/env bash

# ==============================================================================
# Auto-Installer & Auto-Deployer: Laravel 11 on Proxmox CT (Debian / Ubuntu)
# Terintegrasi dengan GitHub Actions Self-Hosted Runner
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}======================================================================${NC}"
echo -e "${CYAN}   AUTO-DEPLOYMENT & AUTO-INSTALLER LARAVEL 11 (PROXMOX CT)          ${NC}"
echo -e "${CYAN}            Latiseducation & Tutorindonesia (SB Admin 2)             ${NC}"
echo -e "${BLUE}======================================================================${NC}"
echo ""

# Deteksi hak akses root / sudo
if [ "$EUID" -ne 0 ]; then
    if command -v sudo &> /dev/null; then
        SUDO="sudo"
    else
        echo -e "${RED}[ERROR] Script memerlukan hak akses root atau sudo!${NC}"
        exit 1
    fi
else
    SUDO=""
fi

export DEBIAN_FRONTEND=noninteractive

# 1. Update Repository & Install Prerequisite Dasar
echo -e "${YELLOW}[1/7] Memeriksa dan memperbarui paket sistem dasar...${NC}"
$SUDO apt-get update -y
$SUDO apt-get install -y curl wget git unzip zip software-properties-common lsb-release ca-certificates apt-transport-https gnupg2

# 2. Deteksi OS & Setup Repository PHP
echo -e "${YELLOW}[2/7] Menyiapkan PHP, Nginx & MariaDB...${NC}"
OS_ID=""
VERSION_CODENAME=""
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_ID=$ID
    VERSION_CODENAME=$VERSION_CODENAME
fi

echo -e "Distro Linux terdeteksi: ${CYAN}${OS_ID} (${VERSION_CODENAME})${NC}"

if [ "$OS_ID" = "ubuntu" ]; then
    $SUDO add-apt-repository -y ppa:ondrej/php || true
    $SUDO apt-get update -y
elif [ "$OS_ID" = "debian" ]; then
    curl -sSLo /tmp/debsuryorg.gpg https://packages.sury.org/php/apt.gpg
    $SUDO gpg --dearmor -o /etc/apt/trusted.gpg.d/php.gpg /tmp/debsuryorg.gpg > /dev/null 2>&1 || true
    echo "deb https://packages.sury.org/php/ ${VERSION_CODENAME} main" | $SUDO tee /etc/apt/sources.list.d/php.list
    $SUDO apt-get update -y
fi

# Pasang Nginx & MariaDB
$SUDO apt-get install -y nginx mariadb-server

# Pasang PHP (mencoba PHP 8.3, fallback ke PHP default distro jika 8.3 tidak ada)
if $SUDO apt-get install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath php8.3-intl; then
    PHP_VER="8.3"
elif $SUDO apt-get install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-xml php8.2-curl php8.2-bcmath php8.2-intl; then
    PHP_VER="8.2"
else
    $SUDO apt-get install -y php php-cli php-fpm php-mysql php-zip php-gd php-mbstring php-xml php-curl php-bcmath php-intl
    PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
fi

echo -e "Versi PHP aktif: ${GREEN}$(php -v | head -n 1)${NC}"

# 3. Install Composer (langsung unduh composer.phar yang terverifikasi)
echo -e "${YELLOW}[3/7] Menyiapkan Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo "Mengunduh Composer binary terbaru..."
    $SUDO curl -sSLo /usr/local/bin/composer https://getcomposer.org/composer-stable.phar
    $SUDO chmod +x /usr/local/bin/composer
fi

echo -e "Composer aktif: ${GREEN}$(composer --version)${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer config --global policy.advisories.block false || true

# 4. Setup Database MySQL / MariaDB
echo -e "${YELLOW}[4/7] Mengonfigurasi database MariaDB/MySQL...${NC}"
$SUDO systemctl start mariadb || $SUDO service mariadb start || true
$SUDO systemctl enable mariadb || $SUDO service mariadb enable || true

# Pastikan database ada
$SUDO mysql -e "CREATE DATABASE IF NOT EXISTS student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$SUDO mysql -e "GRANT ALL PRIVILEGES ON student_management.* TO 'root'@'localhost' IDENTIFIED VIA mysql_native_password;" || true
$SUDO mysql -e "FLUSH PRIVILEGES;" || true

# 5. Sinkronisasi Source Code ke /var/www/student_management
APP_DIR="/var/www/student_management"
echo -e "${YELLOW}[5/7] Menyalin kode aplikasi ke $APP_DIR...${NC}"

ENV_BACKUP=""
if [ -f "$APP_DIR/.env" ]; then
    ENV_BACKUP=$(cat "$APP_DIR/.env")
fi

CURRENT_DIR=$(pwd)
$SUDO mkdir -p "$APP_DIR"

if [ -f "$CURRENT_DIR/artisan" ] && [ -f "$CURRENT_DIR/composer.json" ]; then
    # Jika dijalankan dari runner workspace
    $SUDO cp -r "$CURRENT_DIR/." "$APP_DIR/"
else
    # Jika dijalankan via curl langsung
    $SUDO rm -rf "$APP_DIR"
    $SUDO git clone https://github.com/reyhannazera16/HomeTestIT.git "$APP_DIR"
fi

cd "$APP_DIR"

# Restore atau generate .env
if [ -n "$ENV_BACKUP" ]; then
    echo "$ENV_BACKUP" | $SUDO tee "$APP_DIR/.env" > /dev/null
elif [ ! -f "$APP_DIR/.env" ]; then
    $SUDO cp "$APP_DIR/.env.example" "$APP_DIR/.env"
fi

# Pastikan konfigurasi database di .env mengarah ke MySQL lokal
$SUDO sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_PORT=.*/DB_PORT=3306/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_DATABASE=.*/DB_DATABASE=student_management/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=/' "$APP_DIR/.env"
$SUDO sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' "$APP_DIR/.env"
$SUDO sed -i 's/FILESYSTEM_DISK=.*/FILESYSTEM_DISK=public/' "$APP_DIR/.env"

# 6. Jalankan Composer, Migrasi Database, & Optimasi
echo -e "${YELLOW}[6/7] Menjalankan composer install, migrasi & seeder...${NC}"
$SUDO composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate APP_KEY jika belum ada
if ! grep -q "APP_KEY=base64:" "$APP_DIR/.env"; then
    $SUDO php artisan key:generate --force
fi

$SUDO php artisan storage:link --force || true

# Jalankan migrasi dan seeder
$SUDO php artisan migrate --force
$SUDO php artisan db:seed --force

$SUDO php artisan optimize:clear || true
$SUDO php artisan config:cache || true
$SUDO php artisan route:cache || true
$SUDO php artisan view:cache || true

# 7. Konfigurasi Nginx Web Server
echo -e "${YELLOW}[7/7] Mengonfigurasi Nginx Web Server...${NC}"
$SUDO rm -f /etc/nginx/sites-enabled/default

# Pastikan PHP-FPM aktif dan deteksi socket
$SUDO systemctl restart php${PHP_VER}-fpm || $SUDO service php${PHP_VER}-fpm restart || $SUDO service php-fpm restart || true
PHP_SOCK=$(find /run/php/ -name "php*-fpm.sock" 2>/dev/null | head -n 1)

if [ -z "$PHP_SOCK" ]; then
    PHP_SOCK="/run/php/php${PHP_VER}-fpm.sock"
fi
echo -e "Socket PHP-FPM: ${CYAN}${PHP_SOCK}${NC}"

$SUDO bash -c "cat > /etc/nginx/sites-available/student_management <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name _;
    root $APP_DIR/public;

    add_header X-Frame-Options \"SAMEORIGIN\";
    add_header X-Content-Type-Options \"nosniff\";

    index index.php index.html;

    charset utf-8;
    client_max_body_size 20M;

    location / {
        try_files \\\$uri \\\$uri/ /index.php?\\\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php\\$ {
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_param SCRIPT_FILENAME \\\$realpath_root\\\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
EOF"

$SUDO ln -sf /etc/nginx/sites-available/student_management /etc/nginx/sites-enabled/

# Hak akses www-data
$SUDO chown -R www-data:www-data "$APP_DIR"
$SUDO chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Restart Nginx
$SUDO systemctl restart nginx || $SUDO service nginx restart || true

# Ambil IP Container Proxmox
IP_ADDRESS=$(hostname -I | awk '{print $1}')
if [ -z "$IP_ADDRESS" ]; then
    IP_ADDRESS="IP_CONTAINER_ANDA"
fi

echo ""
echo -e "${GREEN}======================================================================${NC}"
echo -e "${GREEN}  ✔ AUTO-DEPLOY SUKSES! APLIKASI TELAH AKTIF DI PROXMOX CT            ${NC}"
echo -e "${GREEN}======================================================================${NC}"
echo ""
echo -e "Aplikasi siap diakses melalui browser:"
echo -e "🌐 ${CYAN}http://${IP_ADDRESS}${NC}"
echo ""
echo -e "🔑 ${YELLOW}Kredensial Login Administrator:${NC}"
echo -e "   Email    : ${GREEN}admin@latis.com${NC}"
echo -e "   Password : ${GREEN}password123${NC}"
echo ""
echo -e "${BLUE}======================================================================${NC}"
