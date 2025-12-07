# 🚀 Shortlink Kay v1 - Complete Deployment Guide

**Panduan Lengkap Deploy Shortlink Kay dari Awal hingga Live**

Panduan ini mengikuti metode **Waterfall** - tahapan yang jelas dan berurutan dari awal hingga akhir. Cocok untuk pemula sekalipun.

---

## 📋 Daftar Isi

1. [Persiapan Awal](#persiapan-awal)
2. [Tahap 1: VPS Setup](#tahap-1-vps-setup)
3. [Tahap 2: Install aaPanel](#tahap-2-install-aapanel)
4. [Tahap 3: Setup Domain](#tahap-3-setup-domain)
5. [Tahap 4: Setup Database](#tahap-4-setup-database)
6. [Tahap 5: Deploy Aplikasi](#tahap-5-deploy-aplikasi)
7. [Tahap 6: Konfigurasi Nginx](#tahap-6-konfigurasi-nginx)
8. [Tahap 7: Setup SSL Certificate](#tahap-7-setup-ssl-certificate)
9. [Tahap 8: Testing & Verifikasi](#tahap-8-testing--verifikasi)
10. [Tahap 9: Monitoring & Maintenance](#tahap-9-monitoring--maintenance)
11. [Troubleshooting](#troubleshooting)

---

## 📌 Persiapan Awal

Sebelum memulai deployment, pastikan Anda memiliki:

### ✅ Checklist Persiapan

- [ ] **VPS/Server** - Sudah disewa atau tersedia
- [ ] **Domain** - Sudah terdaftar (misal: kanyaars.cloud)
- [ ] **SSH Access** - Bisa login ke server via SSH
- [ ] **Root Password** - Tahu password root server
- [ ] **Project Files** - Shortlink-Kay sudah siap di-upload
- [ ] **Database Backup** - Ada file database.sql
- [ ] **Text Editor** - Untuk edit file konfigurasi

### 📊 Server Information

Contoh konfigurasi yang akan digunakan:

```
IP Address:      202.10.37.31
Domain:          kanyaars.cloud
OS:              Ubuntu 24.04.3 LTS
RAM:             2GB (minimum)
Disk:            20GB (minimum)
Database:        MySQL/MariaDB
Web Server:      Nginx
PHP Version:     8.3
Panel:           aaPanel
```

### 🔑 Credentials yang Akan Dibuat

Selama proses deployment, Anda akan membuat credentials berikut:

```
Database Name:   knowing_me_from_hotel_k
DB User:         kanyaarss
DB Password:     [your_secure_password]
Admin Username:  admin
Admin Password:  admin123
```

---

## 🔧 Tahap 1: VPS Setup

### Langkah 1.1: Login ke Server via SSH

**Untuk Windows (gunakan PuTTY atau Windows Terminal):**

```bash
# Buka Command Prompt atau PowerShell
ssh root@202.10.37.31

# Masukkan password root
```

**Untuk Mac/Linux:**

```bash
ssh root@202.10.37.31
```

**Hasil yang diharapkan:**
```
Welcome to Ubuntu 24.04.3 LTS
root@server:~#
```

### Langkah 1.2: Update System

Setelah login, jalankan command berikut untuk update sistem:

```bash
# Update package list
apt update

# Upgrade installed packages
apt upgrade -y

# Install essential tools
apt install -y curl wget git nano vim
```

**Penjelasan:**
- `apt update` - Refresh daftar package yang tersedia
- `apt upgrade -y` - Update semua package (otomatis jawab yes)
- `curl wget git` - Tools untuk download dan version control
- `nano vim` - Text editor untuk edit file

**Waktu:** ~5-10 menit

### Langkah 1.3: Setup Firewall

Konfigurasi firewall untuk membuka port yang diperlukan:

```bash
# Enable firewall
ufw enable

# Allow SSH (port 22)
ufw allow 22/tcp

# Allow HTTP (port 80)
ufw allow 80/tcp

# Allow HTTPS (port 443)
ufw allow 443/tcp

# Allow aaPanel (port 36469)
ufw allow 36469/tcp

# Check status
ufw status
```

**Hasil yang diharapkan:**
```
Status: active

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
80/tcp                     ALLOW       Anywhere
443/tcp                    ALLOW       Anywhere
36469/tcp                  ALLOW       Anywhere
```

### Langkah 1.4: Setup Swap Memory (Optional tapi Recommended)

Jika RAM server kurang dari 2GB, tambahkan swap:

```bash
# Check current swap
free -h

# Create 2GB swap file
fallocate -l 2G /swapfile

# Set permissions
chmod 600 /swapfile

# Setup swap
mkswap /swapfile
swapon /swapfile

# Make permanent (edit /etc/fstab)
echo '/swapfile none swap sw 0 0' >> /etc/fstab

# Verify
free -h
```

**Hasil yang diharapkan:**
```
              total        used        free
Mem:          2.0Gi       500Mi       1.5Gi
Swap:         2.0Gi          0       2.0Gi
```

---

## 📦 Tahap 2: Install aaPanel

aaPanel adalah control panel berbasis web untuk manage server. Jauh lebih mudah daripada command line.

### Langkah 2.1: Download & Install aaPanel

Jalankan command berikut di server:

```bash
# Download aaPanel installer
wget -O install.sh http://www.aapanel.com/script/install_7.0_en.sh

# Run installer
bash install.sh
```

**Penjelasan:**
- Script ini akan download dan install aaPanel
- Proses instalasi memakan waktu 5-10 menit
- Setelah selesai, akan muncul informasi login

**Tunggu sampai selesai!**

### Langkah 2.2: Catat Informasi Login aaPanel

Setelah instalasi selesai, Anda akan melihat output seperti ini:

```
======================================
aaPanel Installation Complete!
======================================
aaPanel URL: http://202.10.37.31:36469
Username: admin
Password: [random_password]
======================================
```

**PENTING:** Catat informasi ini di tempat aman!

### Langkah 2.3: Login ke aaPanel

1. Buka browser: `http://202.10.37.31:36469`
2. Masukkan username dan password yang dicatat tadi
3. Klik **Login**

**Hasil yang diharapkan:**
Anda akan melihat dashboard aaPanel dengan menu:
- File Manager
- Database
- Website
- SSL Certificate
- Settings
- Logs

### Langkah 2.4: Install Required Software

Di aaPanel dashboard, klik **Software Manager** dan install:

**Wajib diinstall:**
- ✅ **Nginx** - Web server
- ✅ **PHP 8.3** - Runtime
- ✅ **MySQL 8.0** atau **MariaDB 10.11** - Database
- ✅ **Pure-Ftpd** - FTP server (optional)

**Cara install:**
1. Klik **Software Manager**
2. Cari software yang ingin diinstall
3. Klik **Install**
4. Tunggu sampai selesai (5-15 menit per software)

**Verifikasi instalasi:**
```bash
# Check Nginx
nginx -v

# Check PHP
php -v

# Check MySQL
mysql --version
```

---

## 🌐 Tahap 3: Setup Domain

### Langkah 3.1: Update DNS Records

Sebelum setup domain di aaPanel, pastikan DNS sudah pointing ke server Anda.

**Di registrar domain (misal: Namecheap, GoDaddy, dll):**

1. Login ke akun registrar
2. Cari **DNS Settings** atau **Nameservers**
3. Update A record:
   ```
   Type: A
   Name: @ (atau kanyaars.cloud)
   Value: 202.10.37.31
   TTL: 3600
   ```

4. Juga tambahkan untuk www:
   ```
   Type: A
   Name: www
   Value: 202.10.37.31
   TTL: 3600
   ```

5. Klik **Save**

**Catatan:** DNS propagation memakan waktu 5 menit - 48 jam. Biasanya selesai dalam 30 menit.

### Langkah 3.2: Verifikasi DNS

Tunggu DNS propagate, kemudian test:

```bash
# Test DNS resolution
nslookup kanyaars.cloud

# Atau gunakan ping
ping kanyaars.cloud
```

**Hasil yang diharapkan:**
```
Name:   kanyaars.cloud
Address: 202.10.37.31
```

### Langkah 3.3: Add Website di aaPanel

1. Di aaPanel dashboard, klik **Website**
2. Klik tombol **+ Add Site**
3. Isi form:
   ```
   Domain Name:       kanyaars.cloud
   Domain Alias:      www.kanyaars.cloud
   PHP Version:       8.3
   Database:          (skip untuk sekarang)
   ```
4. Klik **Submit**

**Hasil yang diharapkan:**
Website akan dibuat dengan folder root:
```
/www/wwwroot/kanyaars.cloud
```

---

## 🗄️ Tahap 4: Setup Database

### Langkah 4.1: Buat Database

1. Di aaPanel dashboard, klik **Database**
2. Klik tombol **+ Add Database**
3. Isi form:
   ```
   Database Name: knowing_me_from_hotel_k
   Database User: kanyaarss
   Password:      [pilih_password_yang_kuat]
   ```
4. Klik **Submit**

**Hasil yang diharapkan:**
```
Database: knowing_me_from_hotel_k
User: kanyaarss
Password: [password_yang_Anda_buat]
Host: localhost
Port: 3306
```

### Langkah 4.2: Import Database Schema

Sekarang kita import struktur database dari file `config/database.sql`:

1. Di aaPanel, klik **Database** → **Manage** pada database `knowing_me_from_hotel_k`
2. Pilih tab **Import**
3. Klik **Choose File** dan pilih file `config/database.sql` dari komputer Anda
4. Klik **Import**

**Tunggu sampai selesai!**

**Hasil yang diharapkan:**
```
Import successful
Tables created: 7
```

### Langkah 4.3: Verifikasi Database

1. Di aaPanel, klik **Database** → **Manage** pada database `knowing_me_from_hotel_k`
2. Pilih tab **Tables**
3. Pastikan sudah ada tabel:
   - ✅ users
   - ✅ links
   - ✅ clicks_log
   - ✅ rate_limit
   - ✅ api_keys
   - ✅ settings
   - ✅ audit_log

**Jika semua ada, database setup selesai!**

---

## 📤 Tahap 5: Deploy Aplikasi

### Langkah 5.1: Prepare GitHub Repository

Sebelum deploy, pastikan project sudah di GitHub:

**Di komputer lokal Anda:**

```bash
# Navigate ke folder project
cd D:\Repo\Shortlink-Kay

# Initialize git (jika belum)
git init

# Add semua file
git add .

# Commit
git commit -m "Initial commit: Shortlink Kay v1"

# Add remote (ganti dengan URL repository Anda)
git remote add origin https://github.com/yourusername/Shortlink-Kay.git

# Push ke GitHub
git push -u origin main
```

### Langkah 5.2: Clone Repository ke Server

1. Di aaPanel, klik **Terminal**
2. Jalankan command:

```bash
# Navigate ke folder website
cd /www/wwwroot/kanyaars.cloud

# Remove default index.html (jika ada)
rm -f index.html

# Clone repository
git clone https://github.com/yourusername/Shortlink-Kay.git .
```

**Penjelasan:**
- `.` berarti clone ke folder saat ini (bukan subfolder)
- Semua file akan di-download dari GitHub

**Tunggu sampai selesai!**

### Langkah 5.3: Setup Environment File

1. Di aaPanel, klik **File Manager**
2. Navigate ke `/www/wwwroot/kanyaars.cloud`
3. Cari file `.env.example`
4. Klik **Rename** → ubah menjadi `.env`
5. Klik **Edit** dan isi dengan:

```env
APP_ENV=production
APP_NAME=Shortlink Kay v1
APP_VERSION=1.0.0
DEBUG_MODE=false

DB_HOST=localhost
DB_PORT=3306
DB_NAME=knowing_me_from_hotel_k
DB_USER=kanyaarss
DB_PASS=[password_yang_Anda_buat_tadi]

BASE_URL=https://kanyaars.cloud

SESSION_TIMEOUT=1800
PASSWORD_HASH_COST=12
RATE_LIMIT_REQUESTS=10
RATE_LIMIT_WINDOW=60

CORS_ENABLED=true
CORS_ORIGINS=https://kanyaars.cloud

LOG_LEVEL=error
LOG_DIR=/www/wwwroot/kanyaars.cloud/storage/logs

API_ENABLED=true
API_RATE_LIMIT=20
```

6. Klik **Save**

### Langkah 5.4: Set File Permissions

1. Di aaPanel File Manager, select semua file
2. Klik **Batch Operation** → **Change Permission**
3. Set:
   ```
   Files: 644
   Directories: 755
   ```
4. Klik **Submit**

**Khusus folder storage:**
1. Navigate ke `/www/wwwroot/kanyaars.cloud/storage`
2. Klik folder `storage`
3. Klik **Change Permission**
4. Set:
   ```
   Files: 644
   Directories: 775
   ```
5. Klik **Submit**

**Khusus file .env:**
1. Cari file `.env`
2. Klik **Change Permission**
3. Set: `600`
4. Klik **Submit**

### Langkah 5.5: Verifikasi File Sudah Ter-Upload

Di aaPanel Terminal, jalankan:

```bash
# List semua file
ls -la /www/wwwroot/kanyaars.cloud

# Check folder penting
ls -la /www/wwwroot/kanyaars.cloud/admin
ls -la /www/wwwroot/kanyaars.cloud/app
ls -la /www/wwwroot/kanyaars.cloud/core
ls -la /www/wwwroot/kanyaars.cloud/public
ls -la /www/wwwroot/kanyaars.cloud/config
ls -la /www/wwwroot/kanyaars.cloud/storage
```

**Hasil yang diharapkan:**
Semua folder dan file sudah ada.

---

## ⚙️ Tahap 6: Konfigurasi Nginx

### Langkah 6.1: Buka Nginx Config

1. Di aaPanel dashboard, klik **Website**
2. Cari `kanyaars.cloud` → Klik **Manage**
3. Pilih tab **Nginx Config**

### Langkah 6.2: Edit Konfigurasi

Ganti seluruh isi dengan konfigurasi berikut:

```nginx
# HTTP to HTTPS Redirect
server {
    listen 80;
    listen [::]:80;
    server_name kanyaars.cloud www.kanyaars.cloud;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server Block
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name kanyaars.cloud www.kanyaars.cloud;
    
    # SSL Certificate (akan di-update setelah setup SSL)
    ssl_certificate /www/server/panel/vhost/cert/kanyaars.cloud/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/kanyaars.cloud/privkey.pem;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Document Root
    root /www/wwwroot/kanyaars.cloud/public;
    index index.php;
    
    # Logging
    access_log /www/wwwlogs/kanyaars.cloud_access.log;
    error_log /www/wwwlogs/kanyaars.cloud_error.log;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript;
    
    # Static Files Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny Access to Sensitive Files
    location ~ /\. {
        deny all;
    }
    
    location ~ /config\.php$ {
        deny all;
    }
    
    location ~ /\.env$ {
        deny all;
    }
    
    location ~ /storage/ {
        deny all;
    }
    
    location ~ /core/ {
        deny all;
    }
    
    location ~ /app/ {
        deny all;
    }
    
    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-83.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Redirect Handler (Shortlink Router)
    location / {
        try_files $uri $uri/ /router.php?code=$uri;
    }
    
    # API Endpoints
    location /api/ {
        try_files $uri $uri/ /api.php?$query_string;
    }
    
    # Admin Panel
    location /admin/ {
        try_files $uri $uri/ /admin/index.php?$query_string;
    }
}
```

### Langkah 6.3: Save & Restart Nginx

1. Klik **Save**
2. Klik **Restart Nginx**

**Tunggu sampai Nginx restart selesai (5-10 detik)**

---

## 🔒 Tahap 7: Setup SSL Certificate

SSL Certificate membuat website Anda HTTPS (aman).

### Langkah 7.1: Generate SSL Certificate

1. Di aaPanel dashboard, klik **SSL Certificate**
2. Klik **+ Add Certificate**
3. Pilih **Let's Encrypt** sebagai provider
4. Isi form:
   ```
   Domain:     kanyaars.cloud
   Sub Domain: www.kanyaars.cloud
   Email:      your_email@example.com
   ```
5. Klik **Apply**

**Tunggu 1-2 menit** - Certificate akan di-generate otomatis

### Langkah 7.2: Verifikasi SSL

1. Di aaPanel, klik **SSL Certificate**
2. Cari `kanyaars.cloud`
3. Pastikan status **Active** (hijau)
4. Lihat tanggal expiry

### Langkah 7.3: Setup Auto-Renewal

1. Klik **Settings** pada certificate
2. Enable **Auto Renewal**
3. Klik **Save**

**Sekarang SSL akan auto-renew sebelum expiry!**

---

## ✅ Tahap 8: Testing & Verifikasi

Sekarang saatnya test aplikasi!

### Test 1: Homepage

1. Buka browser: `https://kanyaars.cloud`
2. Pastikan halaman homepage muncul
3. Tidak ada error di console (F12)

**Hasil yang diharapkan:**
- Halaman muncul dengan form "Create Shortlink"
- URL di address bar adalah `https://kanyaars.cloud`
- Tidak ada warning atau error

### Test 2: Admin Panel

1. Buka: `https://kanyaars.cloud/admin/login.php`
2. Login dengan:
   ```
   Username: admin
   Password: admin123
   ```
3. Pastikan dashboard muncul

**Hasil yang diharapkan:**
- Login berhasil
- Dashboard muncul dengan statistik
- Menu admin panel berfungsi

### Test 3: Create Shortlink

1. Di homepage, masukkan URL:
   ```
   https://www.google.com
   ```
2. Klik **Create Shortlink**
3. Catat short code yang dibuat (misal: `abc123`)

**Hasil yang diharapkan:**
- Short URL dibuat
- Muncul pesan sukses
- Short code bisa di-copy

### Test 4: Test Redirect

1. Buka URL shortlink yang dibuat:
   ```
   https://kanyaars.cloud/abc123
   ```
2. Pastikan redirect ke URL asli (Google)

**Hasil yang diharapkan:**
- Redirect berhasil
- Halaman Google terbuka
- Click tercatat di admin panel

### Test 5: API Test

Buka Terminal/Command Prompt dan jalankan:

```bash
# Test API create
curl -X POST https://kanyaars.cloud/api.php?action=create \
  -H "Content-Type: application/json" \
  -d '{"url":"https://example.com"}'
```

**Hasil yang diharapkan:**
```json
{
  "success": true,
  "data": {
    "code": "xyz789",
    "url": "https://example.com",
    "short_url": "https://kanyaars.cloud/xyz789"
  }
}
```

### Test 6: Check Logs

1. Di aaPanel, klik **Logs**
2. Lihat **Nginx Error Log** - pastikan tidak ada error
3. Lihat **PHP Error Log** - pastikan tidak ada error

**Hasil yang diharapkan:**
- Tidak ada error messages
- Hanya access logs yang normal

---

## 📊 Tahap 9: Monitoring & Maintenance

Setelah aplikasi live, lakukan monitoring rutin.

### Monitoring Harian

**Setiap hari, check:**

1. **System Resources** (di aaPanel dashboard):
   - CPU Usage - harus < 80%
   - Memory Usage - harus < 80%
   - Disk Usage - harus < 80%

2. **Website Status**:
   - Buka homepage - pastikan bisa diakses
   - Check admin panel - pastikan bisa login
   - Test shortlink creation - pastikan berfungsi

3. **Error Logs**:
   - Klik **Logs** → **Nginx Error Log**
   - Klik **Logs** → **PHP Error Log**
   - Pastikan tidak ada error baru

### Backup Database

**Setup auto-backup:**

1. Di aaPanel, klik **Database**
2. Cari `knowing_me_from_hotel_k` → Klik **Settings**
3. Enable **Auto Backup**
4. Set frequency: **Daily** atau **Weekly**
5. Klik **Save**

**Manual backup:**

1. Di aaPanel, klik **Database**
2. Cari `knowing_me_from_hotel_k` → Klik **Backup**
3. Klik **Backup Now**

### Backup Website Files

**Setup auto-backup:**

1. Di aaPanel, klik **Website**
2. Cari `kanyaars.cloud` → Klik **Settings**
3. Enable **Auto Backup**
4. Set frequency: **Weekly**
5. Klik **Save**

### SSL Certificate Renewal

SSL certificate auto-renew sudah di-setup di Tahap 7.3.

**Verifikasi:**

1. Di aaPanel, klik **SSL Certificate**
2. Cari `kanyaars.cloud`
3. Pastikan **Auto Renewal** enabled
4. Check expiry date

---

## 🚨 Troubleshooting

### Problem: 502 Bad Gateway

**Gejala:** Akses website muncul error 502

**Solusi:**

```bash
# Di aaPanel Terminal, jalankan:
cd /www/wwwroot/kanyaars.cloud

# Check PHP config
php -r "require 'config.php'; echo 'Config OK';"

# Restart PHP
systemctl restart php-fpm

# Restart Nginx
systemctl restart nginx
```

**Atau di aaPanel GUI:**
1. Klik **Website** → `kanyaars.cloud` → **Manage**
2. Klik **Restart PHP**
3. Klik **Restart Nginx**
4. Refresh browser

### Problem: Database Connection Error

**Gejala:** Error "Cannot connect to database"

**Solusi:**

1. Verifikasi `.env` file:
   ```bash
   cat /www/wwwroot/kanyaars.cloud/.env | grep DB_
   ```

2. Pastikan nilai benar:
   ```
   DB_HOST=localhost
   DB_NAME=knowing_me_from_hotel_k
   DB_USER=kanyaarss
   DB_PASS=[password_yang_benar]
   ```

3. Test koneksi:
   ```bash
   mysql -h localhost -u kanyaarss -p knowing_me_from_hotel_k
   ```

4. Jika error, check MySQL status:
   ```bash
   systemctl status mysql
   systemctl restart mysql
   ```

### Problem: File Permission Error

**Gejala:** Error "Permission denied" saat upload atau create file

**Solusi:**

```bash
cd /www/wwwroot/kanyaars.cloud

# Set permissions
chmod -R 755 .
chmod -R 775 storage
chmod 600 .env

# Set ownership
chown -R www-data:www-data .
```

### Problem: SSL Certificate Error

**Gejala:** Browser warning "Not secure" atau SSL error

**Solusi:**

1. Di aaPanel, klik **SSL Certificate**
2. Cari `kanyaars.cloud`
3. Klik **Renew** jika sudah expired
4. Restart Nginx:
   ```bash
   systemctl restart nginx
   ```

### Problem: Slow Performance

**Gejala:** Website lambat, response time lama

**Solusi:**

1. **Enable OPcache:**
   - Di aaPanel, klik **PHP** → **8.3** → **Settings**
   - Enable **OPcache**
   - Restart PHP

2. **Enable Gzip Compression:**
   - Sudah di-set di Nginx config (Tahap 6)
   - Restart Nginx

3. **Check System Resources:**
   - Di aaPanel dashboard, lihat CPU, Memory, Disk
   - Jika penuh, upgrade server

4. **Check Database:**
   ```bash
   # Login MySQL
   mysql -u kanyaarss -p knowing_me_from_hotel_k
   
   # Check table status
   SHOW TABLE STATUS;
   
   # Optimize tables
   OPTIMIZE TABLE links;
   OPTIMIZE TABLE clicks_log;
   ```

### Problem: 404 Not Found

**Gejala:** Akses shortlink muncul 404

**Solusi:**

1. Verifikasi Nginx config - pastikan rewrite rules benar
2. Check shortlink ada di database:
   ```bash
   mysql -u kanyaarss -p knowing_me_from_hotel_k
   SELECT * FROM links WHERE code = 'abc123';
   ```

3. Restart Nginx:
   ```bash
   systemctl restart nginx
   ```

---

## 📋 Deployment Checklist

Gunakan checklist ini untuk memastikan semua tahap selesai:

### Tahap 1: VPS Setup
- [ ] Server sudah login via SSH
- [ ] System sudah di-update
- [ ] Firewall sudah dikonfigurasi
- [ ] Swap memory sudah di-setup (jika perlu)

### Tahap 2: Install aaPanel
- [ ] aaPanel sudah terinstall
- [ ] Bisa login ke aaPanel
- [ ] Nginx sudah terinstall
- [ ] PHP 8.3 sudah terinstall
- [ ] MySQL/MariaDB sudah terinstall

### Tahap 3: Setup Domain
- [ ] DNS sudah pointing ke server
- [ ] Domain sudah di-add di aaPanel
- [ ] Website folder sudah dibuat

### Tahap 4: Setup Database
- [ ] Database sudah dibuat
- [ ] Database user sudah dibuat
- [ ] Database schema sudah di-import
- [ ] Semua table sudah ada

### Tahap 5: Deploy Aplikasi
- [ ] Repository sudah di-clone
- [ ] `.env` file sudah dibuat dan dikonfigurasi
- [ ] File permissions sudah di-set
- [ ] Semua file sudah ter-upload

### Tahap 6: Konfigurasi Nginx
- [ ] Nginx config sudah di-update
- [ ] Nginx sudah di-restart
- [ ] Tidak ada error di Nginx

### Tahap 7: Setup SSL
- [ ] SSL certificate sudah di-generate
- [ ] SSL status Active (hijau)
- [ ] Auto-renewal sudah enabled

### Tahap 8: Testing & Verifikasi
- [ ] Homepage bisa diakses
- [ ] Admin panel bisa login
- [ ] Shortlink bisa dibuat
- [ ] Redirect berfungsi
- [ ] API berfungsi
- [ ] Tidak ada error di logs

### Tahap 9: Monitoring & Maintenance
- [ ] Auto-backup database sudah di-setup
- [ ] Auto-backup website sudah di-setup
- [ ] Monitoring system resources sudah di-setup
- [ ] SSL auto-renewal sudah di-verify

---

## 🎯 Quick Reference

### Important Paths
```
Website Root:    /www/wwwroot/kanyaars.cloud
Public Folder:   /www/wwwroot/kanyaars.cloud/public
Config File:     /www/wwwroot/kanyaars.cloud/config.php
Environment:     /www/wwwroot/kanyaars.cloud/.env
Storage Logs:    /www/wwwroot/kanyaars.cloud/storage/logs
Nginx Config:    /www/server/nginx/conf/nginx.conf
PHP Config:      /www/server/php/83/etc/php.ini
```

### Important Ports
```
aaPanel:         36469
HTTP:            80
HTTPS:           443
MySQL:           3306
FTP:             21
SSH:             22
```

### Important Credentials
```
Database:        knowing_me_from_hotel_k
DB User:         kanyaarss
Admin Username:  admin
Admin Password:  admin123
```

### Important Commands
```bash
# Restart services
systemctl restart nginx
systemctl restart php-fpm
systemctl restart mysql

# Check logs
tail -f /www/wwwlogs/kanyaars.cloud_error.log
tail -f /www/wwwlogs/kanyaars.cloud_access.log

# Check database
mysql -u kanyaarss -p knowing_me_from_hotel_k

# Check file permissions
ls -la /www/wwwroot/kanyaars.cloud
```

---

## 🎉 Deployment Selesai!

Jika semua tahap di atas sudah selesai, website Anda sekarang **LIVE** dan bisa diakses:

```
🌐 Homepage:     https://kanyaars.cloud
👨‍💼 Admin Panel:   https://kanyaars.cloud/admin/login.php
🔌 API:          https://kanyaars.cloud/api.php
```

### Next Steps

1. **Customize Admin Password:**
   - Login ke admin panel
   - Ubah password default `admin123` ke password yang kuat

2. **Setup Custom Branding:**
   - Edit homepage (public/index.php)
   - Edit admin panel (admin/index.php)
   - Upload logo dan customize warna

3. **Monitor Aplikasi:**
   - Check logs setiap hari
   - Monitor system resources
   - Backup database secara rutin

4. **Optimize Performance:**
   - Enable caching
   - Optimize database
   - Compress static files

---

**Version:** 1.0  
**Date:** 2025-12-07  
**Status:** ✅ Complete Deployment Guide  
**Method:** Waterfall (Sequential Steps)  
**Difficulty:** Beginner-Friendly
