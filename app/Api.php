<?php
/**
 * Api Class - API Handler
 * 
 * Menangani semua API requests.
 * Endpoints: /api/create, /api/info, /api/delete
 * 
 * Usage:
 *   $api = new Api($db, $config);
 *   $api->handleRequest($method, $endpoint, $data);
 */

class Api
{
    private $db;
    private $config;
    private $shortener;
    
    /**
     * Constructor
     * 
     * @param Database $db Database instance
     * @param array $config Configuration
     */
    public function __construct($db, $config = [])
    {
        $this->db = $db;
        $this->config = $config;
        $this->shortener = new Shortener($db, $config);
    }
    
    /**
     * Handle API request
     * 
     * @param string $method HTTP method
     * @param string $endpoint Endpoint name
     * @param array $data Request data
     * @return void
     */
    public function handleRequest($method, $endpoint, $data = [])
    {
        try {
            // Check rate limit
            $ip = Security::getClientIP();
            $limit = $this->config['api_rate_limit'] ?? 20;
            
            if (!Security::checkRateLimit($this->db, $ip, 'api', $limit, 60)) {
                $this->sendError('Rate limit exceeded', 429);
            }
            
            // Route request
            switch ($endpoint) {
                case 'create':
                    if ($method !== 'POST') {
                        $this->sendError('Method not allowed', 405);
                    }
                    $this->handleCreate($data);
                    break;
                
                case 'info':
                    if ($method !== 'GET') {
                        $this->sendError('Method not allowed', 405);
                    }
                    $this->handleInfo($data);
                    break;
                
                case 'delete':
                    if ($method !== 'DELETE') {
                        $this->sendError('Method not allowed', 405);
                    }
                    $this->handleDelete($data);
                    break;
                
                default:
                    $this->sendError('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Handle create request
     * 
     * @param array $data Request data
     * @return void
     */
    private function handleCreate($data)
    {
        // Validate required fields
        if (!isset($data['url']) || empty($data['url'])) {
            $this->sendError('URL is required', 400);
        }
        
        // Validate URL
        if (!Validator::validateUrl($data['url'])) {
            $this->sendError('Invalid URL format', 400);
        }
        
        // Get optional fields
        $custom_code = $data['custom_code'] ?? null;
        $expiration_days = isset($data['expiration_days']) ? (int)$data['expiration_days'] : null;
        
        try {
            // Create shortlink
            $link = $this->shortener->createShortlink(
                $data['url'],
                $custom_code,
                null,
                $expiration_days
            );
            
            // Build response
            $response = [
                'success' => true,
                'data' => [
                    'id' => $link['id'],
                    'code' => $link['code'],
                    'short_url' => $this->config['base_url'] . '/' . $link['code'],
                    'original_url' => $link['url'],
                    'created_at' => $link['created_at'],
                    'expires_at' => $link['expires_at'],
                ]
            ];
            
            $this->sendResponse($response, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }
    
    /**
     * Handle info request
     * 
     * @param array $data Request data
     * @return void
     */
    private function handleInfo($data)
    {
        // Get code from query string
        $code = $_GET['code'] ?? null;
        
        if (!$code) {
            $this->sendError('Code is required', 400);
        }
        
        // Get shortlink
        $link = $this->shortener->getShortlink($code);
        
        if (!$link) {
            $this->sendError('Shortlink not found', 404);
        }
        
        // Get statistics
        $stats = $this->shortener->getStatistics($link['id']);
        
        // Build response
        $response = [
            'success' => true,
            'data' => [
                'code' => $link['code'],
                'short_url' => $this->config['base_url'] . '/' . $link['code'],
                'original_url' => $link['url'],
                'is_active' => (bool)$link['is_active'],
                'created_at' => $link['created_at'],
                'expires_at' => $link['expires_at'],
                'last_accessed_at' => $link['last_accessed_at'],
                'statistics' => [
                    'total_clicks' => $stats['total_clicks'],
                    'clicks_today' => $stats['clicks_today'],
                    'clicks_week' => $stats['clicks_week'],
                    'clicks_month' => $stats['clicks_month'],
                ]
            ]
        ];
        
        $this->sendResponse($response);
    }
    
    /**
     * Handle delete request
     * 
     * @param array $data Request data
     * @return void
     */
    private function handleDelete($data)
    {
        // Get code from query string or data
        $code = $_GET['code'] ?? $data['code'] ?? null;
        
        if (!$code) {
            $this->sendError('Code is required', 400);
        }
        
        // Get shortlink
        $link = $this->shortener->getShortlink($code);
        
        if (!$link) {
            $this->sendError('Shortlink not found', 404);
        }
        
        try {
            // Delete shortlink
            $this->shortener->deleteShortlink($link['id']);
            
            // Build response
            $response = [
                'success' => true,
                'message' => 'Shortlink deleted successfully'
            ];
            
            $this->sendResponse($response);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }
    
    /**
     * Send success response
     * 
     * @param array $data Response data
     * @param int $code HTTP status code
     * @return void
     */
    private function sendResponse($data, $code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @return void
     */
    private function sendError($message, $code = 400)
    {
        $response = [
            'success' => false,
            'error' => $message,
            'error_code' => $this->getErrorCode($code)
        ];
        
        $this->sendResponse($response, $code);
    }
    
    /**
     * Get error code from HTTP status code
     * 
     * @param int $code HTTP status code
     * @return string Error code
     */
    private function getErrorCode($code)
    {
        $codes = [
            400 => 'BAD_REQUEST',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'SERVER_ERROR',
        ];
        
        return $codes[$code] ?? 'ERROR';
    }
}
