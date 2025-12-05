<?php
/**
 * API - Entry Point
 * 
 * File ini menangani semua API requests.
 * Endpoints: /api/create, /api/info, /api/delete
 * 
 * Usage:
 *   POST /api/create - Create shortlink
 *   GET /api/info?code=abc123 - Get shortlink info
 *   DELETE /api/delete?code=abc123 - Delete shortlink
 */

// Set JSON header
header('Content-Type: application/json');

// Load configuration
$config = require 'config.php';

// Load required classes
require 'core/Database.php';
require 'core/Validator.php';
require 'core/Security.php';
require 'core/Helpers.php';
require 'app/Shortener.php';
require 'app/Api.php';

try {
    // Check if API is enabled
    if (!$config['api_enabled']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'API is disabled',
            'error_code' => 'API_DISABLED'
        ]);
        exit;
    }
    
    // Create database instance
    $db = new Database($config);
    
    // Create API instance
    $api = new Api($db, $config);
    
    // Get request method & endpoint
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Extract endpoint from path
    // /api/create → create
    // /api/info → info
    // /api/delete → delete
    $path_parts = explode('/', trim($path, '/'));
    $endpoint = end($path_parts);
    
    // Get request data
    $data = [];
    if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?? [];
        }
    }
    
    // Merge GET parameters
    $data = array_merge($_GET, $data);
    
    // Handle request
    $api->handleRequest($method, $endpoint, $data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'error_code' => 'SERVER_ERROR'
    ]);
    exit;
}
