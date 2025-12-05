<?php
/**
 * Shortlink Kay v1 - Application Configuration
 * 
 * File ini membaca environment variables dan menyediakan konfigurasi
 * untuk seluruh aplikasi. Support multiple environments (dev/production).
 * 
 * Usage:
 *   $config = require 'config.php';
 *   echo $config['db_host'];
 */

// ============================================
// DETECT ENVIRONMENT
// ============================================
$environment = getenv('APP_ENV') ?: 'development';
$is_production = ($environment === 'production');

// ============================================
// APPLICATION CONFIGURATION
// ============================================
$config = [
    // Application Settings
    'app_name' => getenv('APP_NAME') ?: 'Shortlink Kay v1',
    'app_version' => getenv('APP_VERSION') ?: '1.0.0',
    'environment' => $environment,
    'debug_mode' => $is_production ? false : (getenv('DEBUG_MODE') === 'true'),
    
    // ============================================
    // DATABASE CONFIGURATION
    // ============================================
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => (int)(getenv('DB_PORT') ?: 3306),
    'db_name' => getenv('DB_NAME') ?: 'shortlink_kay',
    'db_user' => getenv('DB_USER') ?: 'shortlink_user',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_charset' => 'utf8mb4',
    'db_options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
    
    // ============================================
    // APPLICATION URL
    // ============================================
    'base_url' => getenv('BASE_URL') ?: 'http://localhost:8000',
    'admin_path' => '/admin',
    'api_path' => '/api',
    
    // ============================================
    // SECURITY SETTINGS
    // ============================================
    'session_timeout' => (int)(getenv('SESSION_TIMEOUT') ?: 1800),
    'session_name' => 'shortlink_session',
    'password_hash_cost' => (int)(getenv('PASSWORD_HASH_COST') ?: 12),
    'rate_limit_requests' => (int)(getenv('RATE_LIMIT_REQUESTS') ?: 10),
    'rate_limit_window' => (int)(getenv('RATE_LIMIT_WINDOW') ?: 60),
    
    // ============================================
    // SHORT CODE GENERATION
    // ============================================
    'short_code_length' => 6,
    'short_code_charset' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    'short_code_max_length' => 20,
    'short_code_min_length' => 3,
    
    // ============================================
    // LINK SETTINGS
    // ============================================
    'link_expiration_days' => 365,
    'link_max_url_length' => 2000,
    
    // ============================================
    // LOGGING
    // ============================================
    'log_dir' => getenv('LOG_DIR') ?: __DIR__ . '/storage/logs',
    'log_level' => getenv('LOG_LEVEL') ?: ($is_production ? 'error' : 'debug'),
    'log_file_max_size' => 10 * 1024 * 1024, // 10 MB
    
    // ============================================
    // API SETTINGS
    // ============================================
    'api_enabled' => getenv('API_ENABLED') === 'false' ? false : true,
    'api_rate_limit' => (int)(getenv('API_RATE_LIMIT') ?: 20),
    'api_timeout' => 30,
    
    // ============================================
    // CORS SETTINGS
    // ============================================
    'cors_enabled' => getenv('CORS_ENABLED') === 'false' ? false : true,
    'cors_origins' => getenv('CORS_ORIGINS') ?: '*',
    
    // ============================================
    // CACHE SETTINGS
    // ============================================
    'cache_enabled' => !$is_production ? false : true,
    'cache_dir' => __DIR__ . '/storage/cache',
    'cache_ttl' => 3600, // 1 hour
    
    // ============================================
    // PAGINATION
    // ============================================
    'pagination_per_page' => 20,
    'pagination_max_per_page' => 100,
];

// ============================================
// VALIDATE REQUIRED SETTINGS
// ============================================
$required_settings = ['db_host', 'db_name', 'db_user', 'base_url'];
foreach ($required_settings as $setting) {
    if (empty($config[$setting])) {
        throw new Exception("Required configuration missing: {$setting}");
    }
}

return $config;
