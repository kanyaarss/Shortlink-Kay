# 🔗 Shortlink Kay v1

**URL Shortener System dengan Admin Panel & API**

Shortlink Kay adalah aplikasi web untuk membuat, mengelola, dan melacak short URL (shortlink). Aplikasi ini dilengkapi dengan admin panel yang user-friendly, API REST, dan sistem tracking yang komprehensif.

---

## 📋 Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
3. [Struktur Project](#struktur-project)
4. [Penjelasan File & Folder](#penjelasan-file--folder)
5. [Instalasi & Setup](#instalasi--setup)
6. [Konfigurasi](#konfigurasi)
7. [Penggunaan](#penggunaan)
8. [API Documentation](#api-documentation)
9. [Deployment](#deployment)
10. [Troubleshooting](#troubleshooting)

---

## ✨ Fitur Utama

### 🎯 Shortlink Management
- **Buat Shortlink** - Konversi URL panjang menjadi short URL
- **Custom Code** - Pilih custom code atau generate otomatis
- **Link Expiration** - Set tanggal kadaluarsa untuk shortlink
- **Link Status** - Enable/disable shortlink kapan saja
- **Bulk Operations** - Kelola multiple links sekaligus

### 📊 Analytics & Tracking
- **Click Tracking** - Catat setiap klik pada shortlink
- **Statistics** - Lihat jumlah klik, tanggal, dan IP address
- **Real-time Dashboard** - Dashboard dengan statistik real-time
- **Export Data** - Export data dalam format CSV/JSON

### 🔐 Security & Authentication
- **User Authentication** - Login dengan username & password
- **Session Management** - Session timeout yang configurable
- **Password Hashing** - Menggunakan bcrypt untuk keamanan
- **Rate Limiting** - Proteksi dari brute force attack
- **CORS Support** - API dengan CORS configuration

### 🔌 API REST
- **Create Shortlink** - POST /api/create
- **Get Shortlink** - GET /api/shortlink/{code}
- **List Shortlinks** - GET /api/shortlinks
- **Update Shortlink** - PUT /api/shortlink/{code}
- **Delete Shortlink** - DELETE /api/shortlink/{code}
- **Get Statistics** - GET /api/stats/{code}

### 🎨 User Interface
- **Responsive Design** - Mobile-friendly interface
- **Modern UI** - Clean dan intuitive design
- **Admin Dashboard** - Comprehensive admin panel
- **Real-time Updates** - Live data updates

---

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 8.3** - Server-side scripting language
- **MySQL/MariaDB** - Relational database
- **PDO** - Database abstraction layer
- **Nginx** - Web server

### Frontend
- **HTML5** - Markup language
- **CSS3** - Styling
- **JavaScript (Vanilla)** - Client-side scripting
- **Bootstrap/Custom CSS** - Responsive framework

### Tools & Libraries
- **Git** - Version control
- **Composer** - PHP package manager (optional)
- **aaPanel** - Server management panel

---

## 📁 Struktur Project

```
Shortlink-Kay/
├── admin/                          # Admin panel
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css          # Admin styling
│   │   └── js/
│   │       └── admin.js           # Admin JavaScript
│   ├── index.php                  # Admin dashboard
│   ├── login.php                  # Admin login page
│   ├── links.php                  # Link management
│   ├── stats.php                  # Statistics page
│   └── logout.php                 # Logout handler
│
├── app/                            # Application classes
│   ├── Shortener.php              # Shortlink creation & management
│   ├── Auth.php                   # Authentication logic
│   └── Api.php                    # API handler
│
├── core/                           # Core classes
│   ├── Database.php               # PDO database wrapper
│   ├── Router.php                 # URL routing
│   ├── Validator.php              # Input validation
│   ├── Security.php               # Security functions
│   └── Helpers.php                # Helper functions
│
├── public/                         # Frontend
│   ├── index.php                  # Homepage
│   ├── css/
│   │   └── style.css              # Frontend styling
│   └── js/
│       └── script.js              # Frontend JavaScript
│
├── config/                         # Configuration files
│   ├── database.sql               # Database schema
│   ├── nginx.conf                 # Nginx configuration
│   └── requirements.txt           # System requirements
│
├── storage/                        # Storage directory
│   ├── logs/                      # Application logs
│   └── cache/                     # Cache files
│
├── doc/                            # Documentation
│   └── EXPLAINED.md               # Deployment guide
│
├── config.php                      # Main configuration file
├── router.php                      # Redirect handler
├── api.php                         # API entry point
├── .env.example                    # Environment variables template
├── .gitignore                      # Git ignore rules
└── README.md                       # This file
```

---

## 📖 Penjelasan File & Folder

### 🔧 Root Files

#### `config.php` (4.6 KB)
**Fungsi:** File konfigurasi utama aplikasi
- Membaca environment variables dari file `.env`
- Menyimpan konfigurasi database, security, logging, dll
- Support multiple environments (development/production)
- Validasi konfigurasi yang diperlukan

**Digunakan oleh:** Semua file PHP di aplikasi

**Contoh penggunaan:**
```php
$config = require 'config.php';
echo $config['db_host'];
echo $config['base_url'];
```

#### `router.php` (1.0 KB)
**Fungsi:** Handler untuk redirect shortlink
- Menerima request ke shortlink (misal: `/abc123`)
- Mengambil data shortlink dari database
- Redirect ke URL asli
- Mencatat click untuk analytics

**Alur kerja:**
1. User akses `https://kanyaars.cloud/abc123`
2. Nginx route ke `router.php?code=abc123`
3. Router cari shortlink dengan code `abc123`
4. Redirect ke URL asli
5. Catat click di database

#### `api.php` (2.1 KB)
**Fungsi:** Entry point untuk API REST
- Menerima request API (POST, GET, PUT, DELETE)
- Route request ke API handler
- Return JSON response
- Handle error dan validation

**Contoh request:**
```bash
POST /api.php?action=create
GET /api.php?action=get&code=abc123
```

#### `.env.example` (1.6 KB)
**Fungsi:** Template untuk environment variables
- Berisi contoh konfigurasi untuk semua environment
- Harus di-copy ke `.env` dan di-edit sesuai environment
- Jangan commit file `.env` ke git

**Isi file:**
```env
APP_ENV=production
DB_HOST=localhost
DB_NAME=shortlink_kay
DB_USER=shortlink_user
DB_PASS=your_password
BASE_URL=https://kanyaars.cloud
```

#### `.gitignore` (3.0 KB)
**Fungsi:** Git ignore rules
- Exclude file yang tidak perlu di-commit
- Exclude `.env` file (security)
- Exclude `storage/` folder
- Exclude `node_modules/` (jika ada)

---

### 📦 Core Classes (`core/` folder)

#### `Database.php` (5.4 KB)
**Fungsi:** PDO wrapper untuk database operations
- Koneksi ke MySQL/MariaDB
- Execute query dengan prepared statements
- Fetch single row atau multiple rows
- Handle error dan exception

**Method utama:**
- `fetch($query, $params)` - Ambil 1 row
- `fetchAll($query, $params)` - Ambil multiple rows
- `execute($query, $params)` - Execute query (INSERT/UPDATE/DELETE)
- `lastInsertId()` - Ambil ID terakhir yang di-insert
- `beginTransaction()` - Mulai transaction
- `commit()` - Commit transaction
- `rollback()` - Rollback transaction

**Contoh penggunaan:**
```php
$db = new Database($config);
$link = $db->fetch("SELECT * FROM links WHERE code = ?", ['abc123']);
$links = $db->fetchAll("SELECT * FROM links WHERE user_id = ?", [1]);
$db->execute("INSERT INTO links (code, url) VALUES (?, ?)", ['code', 'url']);
```

#### `Validator.php` (6.5 KB)
**Fungsi:** Input validation untuk semua data
- Validate URL format
- Validate shortlink code
- Validate email
- Validate username
- Validate password strength
- Validate custom code

**Method utama:**
- `validateUrl($url)` - Validate URL format
- `validateCode($code)` - Validate shortlink code
- `validateEmail($email)` - Validate email
- `validateUsername($username)` - Validate username
- `validatePassword($password)` - Validate password
- `validateCustomCode($code)` - Validate custom code

**Contoh penggunaan:**
```php
if (!Validator::validateUrl($url)) {
    throw new Exception('Invalid URL');
}
```

#### `Security.php` (7.0 KB)
**Fungsi:** Security functions untuk proteksi aplikasi
- Hash password dengan bcrypt
- Verify password
- Generate random token
- Sanitize input
- Prevent SQL injection
- CSRF token generation

**Method utama:**
- `hashPassword($password)` - Hash password
- `verifyPassword($password, $hash)` - Verify password
- `generateToken()` - Generate random token
- `sanitize($input)` - Sanitize input
- `generateCsrfToken()` - Generate CSRF token
- `validateCsrfToken($token)` - Validate CSRF token

**Contoh penggunaan:**
```php
$hash = Security::hashPassword('password123');
if (Security::verifyPassword('password123', $hash)) {
    // Password correct
}
```

#### `Router.php` (6.6 KB)
**Fungsi:** URL routing untuk shortlink redirect
- Parse shortlink code dari URL
- Cari shortlink di database
- Redirect ke URL asli
- Handle 404 error
- Log click untuk analytics

**Method utama:**
- `handleRedirect($code)` - Handle redirect
- `getShortlink($code)` - Get shortlink data
- `recordClick($code, $ip)` - Record click
- `isExpired($shortlink)` - Check if expired
- `isActive($shortlink)` - Check if active

#### `Helpers.php` (8.3 KB)
**Fungsi:** Helper functions untuk common tasks
- Format URL
- Format date/time
- Generate random code
- Sanitize output
- Truncate string
- Convert bytes to human readable

**Method utama:**
- `generateRandomCode($length)` - Generate random code
- `formatUrl($url)` - Format URL
- `formatDate($date)` - Format date
- `truncate($string, $length)` - Truncate string
- `sanitizeOutput($text)` - Sanitize output
- `getClientIp()` - Get client IP address

---

### 🎯 App Classes (`app/` folder)

#### `Shortener.php` (10.2 KB)
**Fungsi:** Business logic untuk shortlink management
- Create shortlink
- Get shortlink
- Update shortlink
- Delete shortlink
- Check code availability
- Generate unique code

**Method utama:**
- `createShortlink($url, $custom_code, $user_id)` - Create shortlink
- `getShortlink($code)` - Get shortlink by code
- `updateShortlink($code, $data)` - Update shortlink
- `deleteShortlink($code)` - Delete shortlink
- `isCodeAvailable($code)` - Check code availability
- `generateUniqueCode()` - Generate unique code
- `getShortlinksByUser($user_id)` - Get user's shortlinks
- `getExpiredShortlinks()` - Get expired shortlinks

**Contoh penggunaan:**
```php
$shortener = new Shortener($db, $config);
$link = $shortener->createShortlink('https://example.com', 'mycode', 1);
$link = $shortener->getShortlink('mycode');
$shortener->deleteShortlink('mycode');
```

#### `Auth.php` (9.4 KB)
**Fungsi:** Authentication & authorization logic
- User login
- User logout
- Session management
- Password reset
- User registration
- Check user permission

**Method utama:**
- `login($username, $password)` - Login user
- `logout()` - Logout user
- `register($username, $password, $email)` - Register user
- `isLoggedIn()` - Check if user logged in
- `getCurrentUser()` - Get current user data
- `resetPassword($email)` - Reset password
- `changePassword($user_id, $old_password, $new_password)` - Change password

**Contoh penggunaan:**
```php
$auth = new Auth($db, $config);
if ($auth->login('admin', 'password123')) {
    // Login successful
}
```

#### `Api.php` (7.9 KB)
**Fungsi:** API handler untuk REST endpoints
- Handle API request
- Validate API key
- Rate limiting
- Response formatting
- Error handling

**Method utama:**
- `handleRequest($action, $method, $data)` - Handle API request
- `validateApiKey($key)` - Validate API key
- `checkRateLimit($ip)` - Check rate limit
- `response($data, $status)` - Format response
- `error($message, $code)` - Format error response

**Contoh penggunaan:**
```php
$api = new Api($db, $config);
$response = $api->handleRequest('create', 'POST', $_POST);
```

---

### 👨‍💼 Admin Panel (`admin/` folder)

#### `login.php` (3.9 KB)
**Fungsi:** Admin login page
- Form untuk login admin
- Validate username & password
- Create session setelah login
- Redirect ke dashboard
- Show error message jika gagal

**Fitur:**
- Remember me checkbox (optional)
- Forgot password link
- Responsive design
- CSRF protection

#### `index.php` (9.5 KB)
**Fungsi:** Admin dashboard
- Tampilkan overview statistik
- Total shortlinks
- Total clicks
- Recent activity
- System status

**Fitur:**
- Real-time statistics
- Quick actions
- Recent links list
- System health check
- User profile

#### `links.php` (13.2 KB)
**Fungsi:** Link management page
- List semua shortlinks
- Create shortlink
- Edit shortlink
- Delete shortlink
- Search & filter
- Pagination

**Fitur:**
- CRUD operations
- Bulk actions
- Search functionality
- Filter by status
- Sort by column
- Export to CSV

#### `stats.php` (12.2 KB)
**Fungsi:** Statistics & analytics page
- View click statistics
- Chart visualization
- Click history
- Geographic data
- Device information
- Referrer tracking

**Fitur:**
- Real-time charts
- Date range filter
- Export statistics
- Click details
- IP tracking
- User agent analysis

#### `logout.php` (0.6 KB)
**Fungsi:** Logout handler
- Destroy session
- Clear cookies
- Redirect ke login page
- Log logout event

#### `assets/css/admin.css`
**Fungsi:** Admin panel styling
- Dashboard layout
- Form styling
- Table styling
- Responsive design
- Dark/light theme support

#### `assets/js/admin.js`
**Fungsi:** Admin panel JavaScript
- Form validation
- AJAX requests
- Dynamic updates
- Modal dialogs
- Confirmation dialogs
- Chart initialization

---

### 🌐 Frontend (`public/` folder)

#### `index.php` (6.3 KB)
**Fungsi:** Homepage - shortlink creator
- Form untuk membuat shortlink
- Display hasil shortlink
- Copy to clipboard
- QR code generation
- Recent shortlinks

**Fitur:**
- Simple & clean UI
- Real-time validation
- Copy button
- QR code
- URL preview
- Mobile responsive

#### `css/style.css` (4.9 KB)
**Fungsi:** Frontend styling
- Homepage layout
- Form styling
- Button styling
- Responsive design
- Animation & transitions

#### `js/script.js` (7.4 KB)
**Fungsi:** Frontend JavaScript
- Form validation
- AJAX submission
- Copy to clipboard
- QR code generation
- Real-time validation
- Error handling

---

### ⚙️ Configuration Files (`config/` folder)

#### `database.sql` (12.1 KB)
**Fungsi:** Database schema
- Table definitions
- Indexes
- Constraints
- Sample data
- Stored procedures (optional)

**Tables:**
- `users` - User accounts
- `links` - Shortlinks
- `clicks_log` - Click tracking
- `rate_limit` - Rate limiting
- `api_keys` - API keys
- `settings` - Application settings
- `audit_log` - Audit trail

#### `nginx.conf` (5.8 KB)
**Fungsi:** Nginx configuration
- Server block
- SSL configuration
- Rewrite rules
- Caching
- Security headers
- Gzip compression

#### `requirements.txt` (7.4 KB)
**Fungsi:** System requirements
- PHP version
- PHP extensions
- Database version
- Server requirements
- Disk space
- Memory requirements

---

### 💾 Storage Directory (`storage/` folder)

#### `logs/`
**Fungsi:** Application logs
- Error logs
- Access logs
- Debug logs
- Audit logs

#### `cache/`
**Fungsi:** Cache files
- Query cache
- Session cache
- Template cache

---

### 📚 Documentation (`doc/` folder)

#### `EXPLAINED.md`
**Fungsi:** Deployment guide
- VPS setup
- aaPanel installation
- Domain configuration
- Database setup
- Application deployment
- Testing & verification
- Troubleshooting

---

## 🚀 Instalasi & Setup

### Requirement

- **PHP 8.3+** dengan extensions: PDO, MySQL, JSON, OpenSSL
- **MySQL 5.7+** atau **MariaDB 10.3+**
- **Nginx** atau **Apache**
- **Git** (untuk clone repository)
- **Minimal 1GB RAM** dan **2GB Disk Space**

### Step 1: Clone Repository

```bash
# Clone dari GitHub
git clone https://github.com/yourusername/Shortlink-Kay.git
cd Shortlink-Kay

# Atau download ZIP dan extract
```

### Step 2: Setup Environment

```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan text editor
# Sesuaikan database credentials dan base URL
```

### Step 3: Setup Database

```bash
# Login ke MySQL
mysql -u root -p

# Create database
CREATE DATABASE shortlink_kay;
CREATE USER 'shortlink_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON shortlink_kay.* TO 'shortlink_user'@'localhost';
FLUSH PRIVILEGES;

# Import schema
mysql -u shortlink_user -p shortlink_kay < config/database.sql
```

### Step 4: Set Permissions

```bash
# Set folder permissions
chmod -R 755 .
chmod -R 775 storage
chmod 600 .env
```

### Step 5: Configure Web Server

**Untuk Nginx:**
```bash
# Copy nginx config
sudo cp config/nginx.conf /etc/nginx/sites-available/shortlink-kay

# Enable site
sudo ln -s /etc/nginx/sites-available/shortlink-kay /etc/nginx/sites-enabled/

# Test & restart
sudo nginx -t
sudo systemctl restart nginx
```

**Untuk Apache:**
```bash
# Copy .htaccess (jika ada)
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 6: Test Installation

```bash
# Test homepage
curl http://localhost

# Test API
curl -X POST http://localhost/api.php?action=create \
  -d "url=https://example.com"

# Test admin
curl http://localhost/admin/login.php
```

---

## ⚙️ Konfigurasi

### File `.env`

```env
# Application
APP_ENV=production                    # development atau production
APP_NAME=Shortlink Kay v1
DEBUG_MODE=false                      # true untuk development

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=shortlink_kay
DB_USER=shortlink_user
DB_PASS=your_secure_password

# Application URL
BASE_URL=https://kanyaars.cloud

# Security
SESSION_TIMEOUT=1800                  # 30 minutes
PASSWORD_HASH_COST=12                 # Bcrypt cost
RATE_LIMIT_REQUESTS=10                # Requests per window
RATE_LIMIT_WINDOW=60                  # Time window in seconds

# CORS
CORS_ENABLED=true
CORS_ORIGINS=https://kanyaars.cloud

# Logging
LOG_LEVEL=error                       # debug, info, warning, error
LOG_DIR=/path/to/storage/logs

# API
API_ENABLED=true
API_RATE_LIMIT=20                     # Requests per minute
```

### File `config.php`

Jangan edit file ini langsung. Semua konfigurasi harus di-set via `.env` file.

---

## 📖 Penggunaan

### Homepage - Buat Shortlink

1. Buka `https://kanyaars.cloud`
2. Masukkan URL panjang di form
3. Pilih tipe code (random atau custom)
4. Klik "Create Shortlink"
5. Copy shortlink yang dibuat
6. Share shortlink

### Admin Panel - Manage Shortlinks

1. Buka `https://kanyaars.cloud/admin/login.php`
2. Login dengan username: `admin`, password: `admin123`
3. Di dashboard, lihat overview statistik
4. Klik "Links" untuk manage shortlinks
5. Create, edit, atau delete shortlinks
6. Klik "Stats" untuk lihat analytics

### API - Programmatic Access

#### Create Shortlink

```bash
curl -X POST https://kanyaars.cloud/api.php?action=create \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com",
    "code": "mycode",
    "expiration_days": 30
  }'
```

Response:
```json
{
  "success": true,
  "data": {
    "code": "mycode",
    "url": "https://example.com",
    "short_url": "https://kanyaars.cloud/mycode",
    "created_at": "2025-12-07 10:30:00"
  }
}
```

#### Get Shortlink

```bash
curl https://kanyaars.cloud/api.php?action=get&code=mycode
```

#### List Shortlinks

```bash
curl https://kanyaars.cloud/api.php?action=list&page=1&limit=20
```

#### Update Shortlink

```bash
curl -X PUT https://kanyaars.cloud/api.php?action=update&code=mycode \
  -H "Content-Type: application/json" \
  -d '{
    "is_active": false
  }'
```

#### Delete Shortlink

```bash
curl -X DELETE https://kanyaars.cloud/api.php?action=delete&code=mycode
```

#### Get Statistics

```bash
curl https://kanyaars.cloud/api.php?action=stats&code=mycode
```

---

## 🔌 API Documentation

### Base URL
```
https://kanyaars.cloud/api.php
```

### Authentication
API tidak memerlukan authentication untuk public endpoints. Untuk protected endpoints, gunakan API key di header:
```
X-API-Key: your_api_key
```

### Response Format

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "abc123",
    "url": "https://example.com"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Invalid URL format",
  "code": 400
}
```

### Endpoints

#### 1. Create Shortlink
```
POST /api.php?action=create
Content-Type: application/json

{
  "url": "https://example.com",
  "code": "mycode",           // optional
  "expiration_days": 30       // optional
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "mycode",
    "url": "https://example.com",
    "short_url": "https://kanyaars.cloud/mycode",
    "created_at": "2025-12-07 10:30:00",
    "expires_at": "2026-01-06 10:30:00"
  }
}
```

#### 2. Get Shortlink
```
GET /api.php?action=get&code=mycode
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "mycode",
    "url": "https://example.com",
    "short_url": "https://kanyaars.cloud/mycode",
    "is_active": true,
    "created_at": "2025-12-07 10:30:00",
    "clicks": 42
  }
}
```

#### 3. List Shortlinks
```
GET /api.php?action=list&page=1&limit=20&sort=created_at&order=desc
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "abc123",
      "url": "https://example.com",
      "clicks": 42,
      "created_at": "2025-12-07 10:30:00"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "pages": 5
  }
}
```

#### 4. Update Shortlink
```
PUT /api.php?action=update&code=mycode
Content-Type: application/json

{
  "is_active": false,
  "expiration_days": 60
}
```

#### 5. Delete Shortlink
```
DELETE /api.php?action=delete&code=mycode
```

#### 6. Get Statistics
```
GET /api.php?action=stats&code=mycode&days=30
```

**Response:**
```json
{
  "success": true,
  "data": {
    "code": "mycode",
    "total_clicks": 42,
    "clicks_today": 5,
    "clicks_this_week": 15,
    "clicks_this_month": 42,
    "top_referrers": [
      {
        "referrer": "google.com",
        "clicks": 20
      }
    ],
    "top_countries": [
      {
        "country": "Indonesia",
        "clicks": 30
      }
    ]
  }
}
```

---

## 🚀 Deployment

Untuk deployment ke production server, lihat file `doc/EXPLAINED.md` yang berisi panduan lengkap:

1. **VPS Setup** - Setup server dari awal
2. **aaPanel Installation** - Install aaPanel
3. **Domain Configuration** - Setup domain
4. **Database Setup** - Create database
5. **Application Deployment** - Deploy aplikasi
6. **SSL Certificate** - Setup HTTPS
7. **Testing & Verification** - Test aplikasi
8. **Monitoring & Maintenance** - Monitor aplikasi

---

## 🐛 Troubleshooting

### Problem: Database Connection Error

**Solusi:**
1. Verifikasi `.env` file:
   - `DB_HOST=localhost`
   - `DB_NAME=shortlink_kay`
   - `DB_USER=shortlink_user`
   - `DB_PASS=correct_password`

2. Test koneksi:
   ```bash
   mysql -h localhost -u shortlink_user -p shortlink_kay
   ```

3. Restart PHP & database:
   ```bash
   sudo systemctl restart php-fpm
   sudo systemctl restart mysql
   ```

### Problem: 404 Not Found

**Solusi:**
1. Check Nginx config - pastikan rewrite rules benar
2. Check file permissions - pastikan readable
3. Check `.htaccess` (jika Apache) - pastikan ada
4. Restart web server:
   ```bash
   sudo systemctl restart nginx
   ```

### Problem: Permission Denied

**Solusi:**
```bash
# Set permissions
chmod -R 755 .
chmod -R 775 storage
chmod 600 .env

# Set ownership (jika perlu)
sudo chown -R www-data:www-data .
```

### Problem: Slow Performance

**Solusi:**
1. Enable OPcache di PHP
2. Enable Gzip compression di Nginx
3. Enable caching di aplikasi
4. Check database indexes
5. Monitor system resources

### Problem: SSL Certificate Error

**Solusi:**
1. Check certificate expiry:
   ```bash
   openssl x509 -in /path/to/cert.pem -noout -dates
   ```

2. Renew certificate:
   ```bash
   certbot renew
   ```

3. Restart Nginx:
   ```bash
   sudo systemctl restart nginx
   ```

---

## 📝 License

MIT License - Bebas digunakan untuk keperluan komersial maupun non-komersial.

---

## 👨‍💻 Support & Contribution

Untuk pertanyaan, bug report, atau contribution:
1. Buka issue di GitHub
2. Buat pull request dengan improvement
3. Hubungi developer

---

## 🎯 Roadmap

- [ ] Two-factor authentication
- [ ] Custom domain support
- [ ] Advanced analytics
- [ ] Webhook integration
- [ ] Mobile app
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Advanced caching

---

**Version:** 1.0.0  
**Last Updated:** 2025-12-07  
**Status:** ✅ Production Ready  
**Author:** Shortlink Kay Development Team
