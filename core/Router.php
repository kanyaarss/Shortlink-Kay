<?php
/**
 * Router Class - Redirect Handler
 * 
 * Menangani redirect dari shortlink ke URL asli.
 * Log akses, update click count, handle expiration.
 * 
 * Usage:
 *   $router = new Router($db);
 *   $router->redirect($code);
 */

class Router
{
    private $db;
    private $config;
    
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
    }
    
    /**
     * Handle redirect
     * 
     * @param string $code Short code
     * @return void
     */
    public function redirect($code)
    {
        try {
            // Sanitize code
            $code = preg_replace('/[^a-zA-Z0-9_-]/', '', $code);
            
            if (empty($code)) {
                $this->notFound('Invalid code');
            }
            
            // Query database
            $link = $this->db->fetch(
                "SELECT id, url, is_active, expires_at FROM links 
                 WHERE code = ? AND is_active = TRUE LIMIT 1",
                [$code]
            );
            
            // Check if link exists
            if (!$link) {
                $this->notFound('Link not found');
            }
            
            // Check if expired
            if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
                $this->gone('Link has expired');
            }
            
            // Log access
            $this->logAccess(
                $link['id'],
                Security::getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['HTTP_REFERER'] ?? ''
            );
            
            // Update click count
            $this->updateClickCount($link['id']);
            
            // Redirect dengan HTTP 301 (Moved Permanently)
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link['url']);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            exit;
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }
    
    /**
     * Log access
     * 
     * @param int $link_id Link ID
     * @param string $ip IP address
     * @param string $user_agent User agent
     * @param string $referer Referer
     * @return void
     */
    private function logAccess($link_id, $ip, $user_agent, $referer)
    {
        try {
            $this->db->execute(
                "INSERT INTO clicks_log (link_id, ip, user_agent, referer, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$link_id, $ip, substr($user_agent, 0, 255), substr($referer, 0, 255)]
            );
        } catch (Exception $e) {
            // Log error tapi jangan stop redirect
        }
    }
    
    /**
     * Update click count
     * 
     * @param int $link_id Link ID
     * @return void
     */
    private function updateClickCount($link_id)
    {
        try {
            $this->db->execute(
                "UPDATE links SET click_count = click_count + 1, last_accessed_at = NOW() 
                 WHERE id = ?",
                [$link_id]
            );
        } catch (Exception $e) {
            // Log error tapi jangan stop redirect
        }
    }
    
    /**
     * Send 404 Not Found response
     * 
     * @param string $message Error message
     * @return void
     */
    private function notFound($message = 'Not Found')
    {
        http_response_code(404);
        $this->sendErrorPage(404, 'Not Found', $message);
    }
    
    /**
     * Send 410 Gone response
     * 
     * @param string $message Error message
     * @return void
     */
    private function gone($message = 'Gone')
    {
        http_response_code(410);
        $this->sendErrorPage(410, 'Gone', $message);
    }
    
    /**
     * Send 500 Server Error response
     * 
     * @param string $message Error message
     * @return void
     */
    private function serverError($message = 'Server Error')
    {
        http_response_code(500);
        $this->sendErrorPage(500, 'Server Error', $message);
    }
    
    /**
     * Send error page
     * 
     * @param int $code HTTP status code
     * @param string $title Error title
     * @param string $message Error message
     * @return void
     */
    private function sendErrorPage($code, $title, $message)
    {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $code; ?> - <?php echo htmlspecialchars($title); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f5f5f5; }
                .container { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
                .error-box { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 40px; max-width: 500px; text-align: center; }
                .error-code { font-size: 72px; font-weight: bold; color: #e74c3c; margin-bottom: 10px; }
                .error-title { font-size: 24px; font-weight: 600; color: #333; margin-bottom: 10px; }
                .error-message { font-size: 16px; color: #666; margin-bottom: 30px; }
                .error-link { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; transition: background 0.3s; }
                .error-link:hover { background: #2980b9; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-box">
                    <div class="error-code"><?php echo $code; ?></div>
                    <div class="error-title"><?php echo htmlspecialchars($title); ?></div>
                    <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
                    <a href="/" class="error-link">← Back to Home</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
