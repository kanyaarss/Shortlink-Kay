<?php
/**
 * Router - Main Redirect Handler
 * 
 * File ini menangani semua redirect dari shortlink.
 * Diakses via Nginx rewrite rule: try_files $uri $uri/ /router.php?code=$uri;
 * 
 * Usage:
 *   https://domain.com/abc123 → router.php?code=abc123
 */

// Load configuration
$config = require 'config.php';

// Load required classes
require 'core/Database.php';
require 'core/Security.php';
require 'core/Router.php';
require 'core/Helpers.php';

try {
    // Create database instance
    $db = new Database($config);
    
    // Create router instance
    $router = new Router($db, $config);
    
    // Extract code dari URL
    $code = isset($_GET['code']) ? trim($_GET['code']) : null;
    
    // Handle redirect
    if ($code) {
        $router->redirect($code);
    } else {
        // Redirect ke homepage
        header('Location: ' . $config['base_url']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Server error";
    exit;
}
