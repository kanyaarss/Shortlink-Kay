<?php
/**
 * Security Class - Security Functions
 * 
 * Menyediakan security functions untuk password hashing,
 * rate limiting, CSRF protection, token generation, dll.
 * 
 * Usage:
 *   $hash = Security::hashPassword($password);
 *   if (Security::verifyPassword($password, $hash)) { ... }
 *   $token = Security::generateToken();
 *   if (!Security::validateCSRFToken($token)) { throw new Exception('CSRF'); }
 */

class Security
{
    /**
     * Hash password dengan bcrypt
     * 
     * @param string $password Password to hash
     * @param int $cost Bcrypt cost (default: 12)
     * @return string Hashed password
     */
    public static function hashPassword($password, $cost = 12)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Password to verify
     * @param string $hash Hash to compare
     * @return bool True jika password match
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehash
     * 
     * @param string $hash Password hash
     * @param int $cost Bcrypt cost
     * @return bool True jika perlu rehash
     */
    public static function needsRehash($hash, $cost = 12)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    
    /**
     * Generate random token
     * 
     * @param int $length Token length (default: 32)
     * @return string Random token (hex)
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Generate API key
     * 
     * @return string API key
     */
    public static function generateApiKey()
    {
        return 'sk_' . bin2hex(random_bytes(32));
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    public static function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs (take first one)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
    
    /**
     * Check rate limit
     * 
     * @param Database $db Database instance
     * @param string $ip IP address
     * @param string $endpoint Endpoint name
     * @param int $limit Request limit
     * @param int $window Time window in seconds
     * @return bool True jika under limit
     */
    public static function checkRateLimit($db, $ip, $endpoint, $limit = 10, $window = 60)
    {
        try {
            $now = time();
            $reset_time = $now - $window;
            
            // Get current request count
            $record = $db->fetch(
                "SELECT request_count, reset_at FROM rate_limit 
                 WHERE ip = ? AND endpoint = ?",
                [$ip, $endpoint]
            );
            
            if (!$record) {
                // First request
                $db->execute(
                    "INSERT INTO rate_limit (ip, endpoint, request_count, reset_at) 
                     VALUES (?, ?, 1, FROM_UNIXTIME(?))",
                    [$ip, $endpoint, $now + $window]
                );
                return true;
            }
            
            $reset_at = strtotime($record['reset_at']);
            
            if ($now > $reset_at) {
                // Reset window
                $db->execute(
                    "UPDATE rate_limit SET request_count = 1, reset_at = FROM_UNIXTIME(?) 
                     WHERE ip = ? AND endpoint = ?",
                    [$now + $window, $ip, $endpoint]
                );
                return true;
            }
            
            // Check if under limit
            if ($record['request_count'] >= $limit) {
                return false;
            }
            
            // Increment counter
            $db->execute(
                "UPDATE rate_limit SET request_count = request_count + 1 
                 WHERE ip = ? AND endpoint = ?",
                [$ip, $endpoint]
            );
            
            return true;
        } catch (Exception $e) {
            // Log error, allow request
            return true;
        }
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True jika valid
     */
    public static function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize output untuk prevent XSS
     * 
     * @param string $data Data to sanitize
     * @return string Sanitized data
     */
    public static function sanitizeOutput($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape SQL string (for logging only, use prepared statements for queries)
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escapeSql($string)
    {
        return addslashes($string);
    }
    
    /**
     * Generate secure random string
     * 
     * @param int $length Length of string
     * @param string $charset Character set
     * @return string Random string
     */
    public static function generateRandomString($length = 32, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $string = '';
        $charset_len = strlen($charset);
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $charset[random_int(0, $charset_len - 1)];
        }
        
        return $string;
    }
    
    /**
     * Hash data dengan SHA256
     * 
     * @param string $data Data to hash
     * @return string Hashed data
     */
    public static function hashData($data)
    {
        return hash('sha256', $data);
    }
    
    /**
     * Verify data hash
     * 
     * @param string $data Original data
     * @param string $hash Hash to verify
     * @return bool True jika match
     */
    public static function verifyDataHash($data, $hash)
    {
        return hash_equals(self::hashData($data), $hash);
    }
}
