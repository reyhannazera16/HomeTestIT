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
echo -e "${YELLOW}[1/7] Memeriksa paket sistem dasar...${NC}"
$SUDO apt-get update -y > /dev/null
$SUDO apt-get install -y curl wget git unzip zip software-properties-common lsb-release ca-certificates apt-transport-https gnupg2 > /dev/null

# 2. Deteksi OS & Setup PHP 8.3 Repository
echo -e "${YELLOW}[2/7] Memeriksa PHP 8.3, Nginx & MariaDB...${NC}"
OS=$(lsb_release -is | tr '[:upper:]' '[:lower:]')

if ! command -v php &> /dev/null || ! php -v | grep -q "8.3"; then
    echo -e "Menambahkan repository PHP 8.3..."
    if [ "$OS" = "ubuntu" ]; then
        $SUDO add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
    elif [ "$OS" = "debian" ]; then
        curl -sSL https://packages.sury.org/php/README.txt | bash -x > /dev/null 2>&1 || true
        wget -qO - https://packages.sury.org/php/apt.gpg | $SUDO gpg --dearmor -o /etc/apt/trusted.gpg.d/php.gpg > /dev/null 2>&1 || true
        echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | $SUDO tee /etc/apt/sources.list.d/php.list > /dev/null
    fi
    $SUDO apt-get update -y > /dev/null
fi

# Install Nginx, MariaDB, PHP 8.3
$SUDO apt-get install -y nginx mariadb-server \
    php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath php8.3-intl > /dev/null

# 3. Install Composer jika belum ada
echo -e "${YELLOW}[3/7] Memeriksa Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer > /dev/null
fi
export COMPOSER_ALLOW_SUPERUSER=1
composer config --global policy.advisories.block false > /dev/null 2>&1 || true

# 4. Setup Database MySQL / MariaDB
echo -e "${YELLOW}[4/7] Mengonfigurasi database MariaDB/MySQL...${NC}"
$SUDO systemctl enable mariadb > /dev/null 2>&1 || $SUDO service mariadb enable > /dev/null 2>&1 || true
$SUDO systemctl start mariadb > /dev/null 2>&1 || $SUDO service mariadb start > /dev/null 2>&1 || true

$SUDO mysql -e "CREATE DATABASE IF NOT EXISTS student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$SUDO mysql -e "GRANT ALL PRIVILEGES ON student_management.* TO 'root'@'localhost' IDENTIFIED VIA mysql_native_password;" > /dev/null 2>&1 || true
$SUDO mysql -e "FLUSH PRIVILEGES;" > /dev/null 2>&1 || true

# 5. Sinkronisasi Source Code ke /var/www/student_management
APP_DIR="/var/www/student_management"
echo -e "${YELLOW}[5/7] Sinkronisasi kode aplikasi ke $APP_DIR...${NC}"

# Backup file .env yang sudah ada jika aplikasi sudah pernah dideploy sebelumnya
ENV_BACKUP=""
if [ -f "$APP_DIR/.env" ]; then
    ENV_BACKUP=$(cat "$APP_DIR/.env")
fi

CURRENT_DIR=$(pwd)
$SUDO mkdir -p "$APP_DIR"

if [ -f "$CURRENT_DIR/artisan" ] && [ -f "$CURRENT_DIR/composer.json" ]; then
    # Jika dijalankan dari runner atau folder repo
    $SUDO rsync -av --exclude='.git' --exclude='node_modules' --exclude='storage/*.key' "$CURRENT_DIR/" "$APP_DIR/" > /dev/null 2>&1 || $SUDO cp -r "$CURRENT_DIR/." "$APP_DIR/"
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

# Pastikan konfigurasi database di .env mengarah ke lokal
$SUDO sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_PORT=.*/DB_PORT=3306/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_DATABASE=.*/DB_DATABASE=student_management/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' "$APP_DIR/.env"
$SUDO sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=/' "$APP_DIR/.env"
$SUDO sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' "$APP_DIR/.env"
$SUDO sed -i 's/FILESYSTEM_DISK=.*/FILESYSTEM_DISK=public/' "$APP_DIR/.env"

# 6. Jalankan Composer, Migrasi Database, & Optimasi
echo -e "${YELLOW}[6/7] Menjalankan build aplikasi, migrasi database & cache...${NC}"
$SUDO composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate APP_KEY jika belum ada
if ! grep -q "APP_KEY=base64:" "$APP_DIR/.env"; then
    $SUDO php artisan key:generate --force
fi

$SUDO php artisan storage:link --force > /dev/null 2>&1 || true

# Jalankan migrasi dan seeder
$SUDO php artisan migrate --force
$SUDO php artisan db:seed --force

$SUDO php artisan optimize:clear > /dev/null 2>&1 || true
$SUDO php artisan config:cache > /dev/null 2>&1 || true
$SUDO php artisan route:cache > /dev/null 2>&1 || true
$SUDO php artisan view:cache > /dev/null 2>&1 || true

# 7. Konfigurasi Nginx
echo -e "${YELLOW}[7/7] Memastikan Nginx aktif dan melayani aplikasi...${NC}"
$SUDO rm -f /etc/nginx/sites-enabled/default

# Deteksi socket php-fpm
PHP_SOCK="/run/php/php8.3-fpm.sock"
if [ ! -e "$PHP_SOCK" ]; then
    PHP_SOCK=$(ls /run/php/php*-fpm.sock 2>/dev/null | head -n 1)
fi

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

# Restart Nginx & PHP-FPM
$SUDO systemctl restart php8.3-fpm > /dev/null 2>&1 || $SUDO service php8.3-fpm restart > /dev/null 2>&1 || true
$SUDO systemctl restart nginx > /dev/null 2>&1 || $SUDO service nginx restart > /dev/null 2>&1 || true

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
