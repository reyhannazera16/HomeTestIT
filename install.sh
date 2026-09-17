#!/usr/bin/env bash

# ==============================================================================
# Auto-Installer: Aplikasi Pendataan Siswa (Laravel 11 & SB Admin 2)
# Khusus Proxmox VE Container (Debian 11/12 atau Ubuntu 20.04/22.04/24.04)
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

clear
echo -e "${BLUE}======================================================================${NC}"
echo -e "${CYAN}   AUTO-INSTALLER APLIKASI PENDATAAN SISWA (LARAVEL 11 + MYSQL)       ${NC}"
echo -e "${CYAN}            Latiseducation & Tutorindonesia (SB Admin 2)             ${NC}"
echo -e "${BLUE}======================================================================${NC}"
echo ""

# 1. Pastikan user adalah root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}[ERROR] Script ini harus dijalankan sebagai ROOT di Proxmox Container!${NC}"
  echo -e "Silakan ketik: ${YELLOW}sudo bash install.sh${NC} atau login sebagai ${YELLOW}root${NC}."
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

echo -e "${YELLOW}[1/7] Memperbarui package repository sistem...${NC}"
apt-get update -y > /dev/null
apt-get install -y curl wget git unzip zip software-properties-common lsb-release ca-certificates apt-transport-https gnupg2 > /dev/null

# 2. Deteksi OS & Setup PHP 8.3 Repository
echo -e "${YELLOW}[2/7] Menyiapkan PHP 8.3 & ekstensi yang diperlukan...${NC}"
OS=$(lsb_release -is | tr '[:upper:]' '[:lower:]')

if [ "$OS" = "ubuntu" ]; then
    add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
elif [ "$OS" = "debian" ]; then
    curl -sSL https://packages.sury.org/php/README.txt | bash -x > /dev/null 2>&1 || true
    wget -qO - https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/php.gpg > /dev/null 2>&1 || true
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
fi

apt-get update -y > /dev/null

# Install Nginx, MariaDB, PHP 8.3
apt-get install -y nginx mariadb-server \
    php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath php8.3-intl > /dev/null

# 3. Install Composer
echo -e "${YELLOW}[3/7] Menginstal Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer > /dev/null
fi
export COMPOSER_ALLOW_SUPERUSER=1
composer config --global policy.advisories.block false > /dev/null 2>&1 || true

# 4. Setup Database MySQL / MariaDB
echo -e "${YELLOW}[4/7] Mengonfigurasi database MariaDB/MySQL...${NC}"
systemctl enable mariadb > /dev/null 2>&1 || service mariadb enable > /dev/null 2>&1 || true
systemctl start mariadb > /dev/null 2>&1 || service mariadb start > /dev/null 2>&1 || true

mysql -e "CREATE DATABASE IF NOT EXISTS student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "GRANT ALL PRIVILEGES ON student_management.* TO 'root'@'localhost' IDENTIFIED VIA mysql_native_password;" > /dev/null 2>&1 || true
mysql -e "FLUSH PRIVILEGES;" > /dev/null 2>&1 || true

# 5. Siapkan Source Code Aplikasi
echo -e "${YELLOW}[5/7] Menyiapkan source code aplikasi di /var/www/student_management...${NC}"
APP_DIR="/var/www/student_management"

# Jika script dijalankan dari dalam repo yang sudah di-clone:
CURRENT_DIR=$(pwd)
if [ -f "$CURRENT_DIR/artisan" ] && [ -f "$CURRENT_DIR/composer.json" ]; then
    mkdir -p "$APP_DIR"
    cp -r "$CURRENT_DIR/." "$APP_DIR/"
else
    # Clone dari GitHub
    rm -rf "$APP_DIR"
    git clone https://github.com/reyhannazera16/HomeTestIT.git "$APP_DIR"
fi

cd "$APP_DIR"

# Salin konfigurasi .env jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Pastikan config .env sesuai database lokal
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=student_management/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=/' .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/FILESYSTEM_DISK=.*/FILESYSTEM_DISK=public/' .env

# Install dependencies composer
echo -e "${YELLOW}[6/7] Menjalankan composer install, migrasi & seeder...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan key:generate --force
php artisan storage:link --force
php artisan migrate:fresh --seed --force
php artisan optimize:clear > /dev/null 2>&1 || true

# 6. Konfigurasi Nginx Web Server
echo -e "${YELLOW}[7/7] Mengonfigurasi Nginx Web Server...${NC}"

# Matikan default site jika ada
rm -f /etc/nginx/sites-enabled/default

# Tentukan socket PHP-FPM yang aktif
PHP_SOCK="/run/php/php8.3-fpm.sock"
if [ ! -e "$PHP_SOCK" ]; then
    PHP_SOCK=$(ls /run/php/php*-fpm.sock 2>/dev/null | head -n 1)
fi

cat > /etc/nginx/sites-available/student_management <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name _;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;
    client_max_body_size 20M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/student_management /etc/nginx/sites-enabled/

# Set permission folder storage & cache
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Restart Nginx & PHP-FPM
systemctl restart php8.3-fpm > /dev/null 2>&1 || service php8.3-fpm restart > /dev/null 2>&1 || true
systemctl restart nginx > /dev/null 2>&1 || service nginx restart > /dev/null 2>&1 || true

# Ambil IP Container Proxmox
IP_ADDRESS=$(hostname -I | awk '{print $1}')
if [ -z "$IP_ADDRESS" ]; then
    IP_ADDRESS="IP_CONTAINER_ANDA"
fi

echo ""
echo -e "${GREEN}======================================================================${NC}"
echo -e "${GREEN}  ✔ INSTALASI SUKSES! APLIKASI TELAH AKTIF DAN SIAP DIGUNAKAN         ${NC}"
echo -e "${GREEN}======================================================================${NC}"
echo ""
echo -e "Aplikasi dapat langsung diakses melalui browser Anda di:"
echo -e "🌐 ${CYAN}http://${IP_ADDRESS}${NC}"
echo ""
echo -e "🔑 ${YELLOW}Kredensial Login Administrator:${NC}"
echo -e "   Email    : ${GREEN}admin@latis.com${NC}"
echo -e "   Password : ${GREEN}password123${NC}"
echo ""
echo -e "${BLUE}======================================================================${NC}"
