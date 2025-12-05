<?php
/**
 * Shortener Class - Shortlink Management
 * 
 * Menangani pembuatan, validasi, dan management shortlink.
 * 
 * Usage:
 *   $shortener = new Shortener($db, $config);
 *   $link = $shortener->createShortlink($url, $custom_code, $user_id);
 *   $link = $shortener->getShortlink($code);
 */

class Shortener
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
     * Create shortlink
     * 
     * @param string $url Original URL
     * @param string $custom_code Custom code (optional)
     * @param int $user_id User ID (optional)
     * @param int $expiration_days Expiration days (optional)
     * @return array Created link data
     * @throws Exception Jika gagal
     */
    public function createShortlink($url, $custom_code = null, $user_id = null, $expiration_days = null)
    {
        // Validate URL
        if (!Validator::validateUrl($url)) {
            throw new Exception('Invalid URL format');
        }
        
        // Determine code
        if ($custom_code) {
            // Validate custom code
            if (!Validator::validateCode($custom_code)) {
                throw new Exception('Invalid custom code');
            }
            
            // Check if code already exists
            if ($this->codeExists($custom_code)) {
                throw new Exception('Code already exists');
            }
            
            $code = $custom_code;
        } else {
            // Generate random code
            $code = $this->generateUniqueCode();
        }
        
        // Set expiration
        $expires_at = null;
        if ($expiration_days) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expiration_days} days"));
        }
        
        // Insert into database
        try {
            $this->db->execute(
                "INSERT INTO links (code, url, created_by, is_active, expires_at, created_at) 
                 VALUES (?, ?, ?, TRUE, ?, NOW())",
                [$code, $url, $user_id, $expires_at]
            );
            
            $link_id = $this->db->lastInsertId();
            
            // Return created link
            return $this->getShortlinkById($link_id);
        } catch (Exception $e) {
            throw new Exception('Failed to create shortlink: ' . $e->getMessage());
        }
    }
    
    /**
     * Get shortlink by code
     * 
     * @param string $code Short code
     * @return array|null Link data atau null
     */
    public function getShortlink($code)
    {
        return $this->db->fetch(
            "SELECT id, code, url, click_count, is_active, expires_at, created_at, last_accessed_at 
             FROM links WHERE code = ? LIMIT 1",
            [$code]
        );
    }
    
    /**
     * Get shortlink by ID
     * 
     * @param int $id Link ID
     * @return array|null Link data atau null
     */
    public function getShortlinkById($id)
    {
        return $this->db->fetch(
            "SELECT id, code, url, click_count, is_active, expires_at, created_at, last_accessed_at 
             FROM links WHERE id = ? LIMIT 1",
            [$id]
        );
    }
    
    /**
     * Get all shortlinks
     * 
     * @param int $user_id User ID (optional)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Array of links
     */
    public function getAllShortlinks($user_id = null, $limit = 20, $offset = 0)
    {
        $sql = "SELECT id, code, url, click_count, is_active, expires_at, created_at, last_accessed_at 
                FROM links WHERE 1=1";
        $params = [];
        
        if ($user_id) {
            $sql .= " AND created_by = ?";
            $params[] = $user_id;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Update shortlink
     * 
     * @param int $id Link ID
     * @param array $data Data to update
     * @return bool True jika berhasil
     * @throws Exception Jika gagal
     */
    public function updateShortlink($id, $data)
    {
        $allowed_fields = ['url', 'is_active', 'expires_at'];
        $update_fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                if ($key === 'url' && !Validator::validateUrl($value)) {
                    throw new Exception('Invalid URL format');
                }
                
                $update_fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }
        
        if (empty($update_fields)) {
            throw new Exception('No valid fields to update');
        }
        
        $params[] = $id;
        
        $sql = "UPDATE links SET " . implode(', ', $update_fields) . " WHERE id = ?";
        
        try {
            $this->db->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to update shortlink: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete shortlink
     * 
     * @param int $id Link ID
     * @return bool True jika berhasil
     * @throws Exception Jika gagal
     */
    public function deleteShortlink($id)
    {
        try {
            // Delete related logs
            $this->db->execute("DELETE FROM clicks_log WHERE link_id = ?", [$id]);
            
            // Delete link
            $this->db->execute("DELETE FROM links WHERE id = ?", [$id]);
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete shortlink: ' . $e->getMessage());
        }
    }
    
    /**
     * Get shortlink statistics
     * 
     * @param int $id Link ID
     * @return array Statistics data
     */
    public function getStatistics($id)
    {
        $link = $this->getShortlinkById($id);
        
        if (!$link) {
            return null;
        }
        
        // Get click data
        $clicks_today = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM clicks_log WHERE link_id = ? AND DATE(created_at) = CURDATE()",
            [$id]
        );
        
        $clicks_week = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM clicks_log WHERE link_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$id]
        );
        
        $clicks_month = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM clicks_log WHERE link_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$id]
        );
        
        // Get top referrers
        $top_referrers = $this->db->fetchAll(
            "SELECT referer, COUNT(*) as count FROM clicks_log 
             WHERE link_id = ? AND referer IS NOT NULL AND referer != '' 
             GROUP BY referer ORDER BY count DESC LIMIT 5",
            [$id]
        );
        
        // Get top browsers
        $top_browsers = $this->db->fetchAll(
            "SELECT user_agent, COUNT(*) as count FROM clicks_log 
             WHERE link_id = ? GROUP BY user_agent ORDER BY count DESC LIMIT 5",
            [$id]
        );
        
        return [
            'link' => $link,
            'clicks_today' => (int)$clicks_today,
            'clicks_week' => (int)$clicks_week,
            'clicks_month' => (int)$clicks_month,
            'total_clicks' => (int)$link['click_count'],
            'top_referrers' => $top_referrers,
            'top_browsers' => $top_browsers,
        ];
    }
    
    /**
     * Generate unique short code
     * 
     * @return string Unique code
     * @throws Exception Jika gagal generate
     */
    private function generateUniqueCode()
    {
        $max_attempts = 100;
        $length = $this->config['short_code_length'] ?? 6;
        $charset = $this->config['short_code_charset'] ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        for ($i = 0; $i < $max_attempts; $i++) {
            $code = generateRandomCode($length, $charset);
            
            if (!$this->codeExists($code)) {
                return $code;
            }
        }
        
        throw new Exception('Failed to generate unique code');
    }
    
    /**
     * Check if code exists
     * 
     * @param string $code Code to check
     * @return bool True jika exists
     */
    private function codeExists($code)
    {
        $result = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM links WHERE code = ?",
            [$code]
        );
        
        return (int)$result > 0;
    }
    
    /**
     * Get total shortlinks count
     * 
     * @param int $user_id User ID (optional)
     * @return int Total count
     */
    public function getTotalCount($user_id = null)
    {
        $sql = "SELECT COUNT(*) FROM links WHERE 1=1";
        $params = [];
        
        if ($user_id) {
            $sql .= " AND created_by = ?";
            $params[] = $user_id;
        }
        
        return (int)$this->db->fetchColumn($sql, $params);
    }
    
    /**
     * Get total clicks count
     * 
     * @param int $user_id User ID (optional)
     * @return int Total clicks
     */
    public function getTotalClicks($user_id = null)
    {
        $sql = "SELECT SUM(click_count) FROM links WHERE 1=1";
        $params = [];
        
        if ($user_id) {
            $sql .= " AND created_by = ?";
            $params[] = $user_id;
        }
        
        return (int)$this->db->fetchColumn($sql, $params);
    }
}
