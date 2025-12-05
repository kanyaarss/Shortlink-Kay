<?php
/**
 * Admin Logout
 * 
 * Handle user logout.
 */

// Load configuration
$config = require '../config.php';

// Load required classes
require '../core/Database.php';
require '../core/Helpers.php';
require '../app/Auth.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name']);
    session_start();
}

// Connect to database
$db = new Database($config);

// Create auth instance
$auth = new Auth($db, $config);

// Logout
$auth->logout();

// Redirect to home
redirect($config['base_url']);
