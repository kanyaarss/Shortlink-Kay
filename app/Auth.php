<?php
/**
 * Auth Class - Authentication & Authorization
 * 
 * Menangani login, logout, session management.
 * 
 * Usage:
 *   $auth = new Auth($db, $config);
 *   $auth->login($username, $password);
 *   if ($auth->isLoggedIn()) { ... }
 *   $auth->logout();
 */

class Auth
{
    private $db;
    private $config;
    private $session_name;
    private $session_timeout;
    
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
        $this->session_name = $config['session_name'] ?? 'shortlink_session';
        $this->session_timeout = $config['session_timeout'] ?? 1800;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->session_name);
            session_start();
        }
        
        // Check session timeout
        $this->checkSessionTimeout();
    }
    
    /**
     * Login user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return bool True jika login berhasil
     * @throws Exception Jika login gagal
     */
    public function login($username, $password)
    {
        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password required');
        }
        
        // Get user from database
        $user = $this->db->fetch(
            "SELECT id, username, password, email, is_active FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
        
        if (!$user) {
            throw new Exception('Invalid username or password');
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            throw new Exception('User account is inactive');
        }
        
        // Verify password
        if (!Security::verifyPassword($password, $user['password'])) {
            throw new Exception('Invalid username or password');
        }
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Log login
        $this->logActivity($user['id'], 'login', 'User logged in');
        
        return true;
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            
            // Log logout
            $this->logActivity($user_id, 'logout', 'User logged out');
        }
        
        // Destroy session
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True jika logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID atau null
     */
    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data atau null
     */
    public function getUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT id, username, email, is_active, created_at FROM users WHERE id = ? LIMIT 1",
            [$this->getUserId()]
        );
    }
    
    /**
     * Require login (redirect if not logged in)
     * 
     * @param string $redirect_to URL to redirect to
     * @return void
     */
    public function requireLogin($redirect_to = '/admin/login.php')
    {
        if (!$this->isLoggedIn()) {
            redirect($redirect_to);
        }
    }
    
    /**
     * Check session timeout
     * 
     * @return void
     */
    private function checkSessionTimeout()
    {
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $current_time = time();
        $last_activity = $_SESSION['last_activity'] ?? $current_time;
        
        // Check if session expired
        if ($current_time - $last_activity > $this->session_timeout) {
            $this->logout();
            throw new Exception('Session expired');
        }
        
        // Update last activity
        $_SESSION['last_activity'] = $current_time;
    }
    
    /**
     * Create new user
     * 
     * @param string $username Username
     * @param string $password Password
     * @param string $email Email
     * @return int User ID
     * @throws Exception Jika gagal
     */
    public function createUser($username, $password, $email)
    {
        // Validate input
        if (!Validator::validateUsername($username)) {
            throw new Exception('Invalid username');
        }
        
        if (!Validator::validatePassword($password)) {
            throw new Exception('Password must be at least 8 characters with uppercase, lowercase, and numbers');
        }
        
        if (!Validator::validateEmail($email)) {
            throw new Exception('Invalid email');
        }
        
        // Check if username exists
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
        
        if ($existing) {
            throw new Exception('Username already exists');
        }
        
        // Check if email exists
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
        
        if ($existing) {
            throw new Exception('Email already exists');
        }
        
        // Hash password
        $password_hash = Security::hashPassword($password, $this->config['password_hash_cost'] ?? 12);
        
        // Insert user
        try {
            $this->db->execute(
                "INSERT INTO users (username, password, email, is_active, created_at) 
                 VALUES (?, ?, ?, TRUE, NOW())",
                [$username, $password_hash, $email]
            );
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Failed to create user: ' . $e->getMessage());
        }
    }
    
    /**
     * Change password
     * 
     * @param int $user_id User ID
     * @param string $old_password Old password
     * @param string $new_password New password
     * @return bool True jika berhasil
     * @throws Exception Jika gagal
     */
    public function changePassword($user_id, $old_password, $new_password)
    {
        // Get user
        $user = $this->db->fetch(
            "SELECT password FROM users WHERE id = ? LIMIT 1",
            [$user_id]
        );
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Verify old password
        if (!Security::verifyPassword($old_password, $user['password'])) {
            throw new Exception('Invalid old password');
        }
        
        // Validate new password
        if (!Validator::validatePassword($new_password)) {
            throw new Exception('Password must be at least 8 characters with uppercase, lowercase, and numbers');
        }
        
        // Hash new password
        $password_hash = Security::hashPassword($new_password, $this->config['password_hash_cost'] ?? 12);
        
        // Update password
        try {
            $this->db->execute(
                "UPDATE users SET password = ? WHERE id = ?",
                [$password_hash, $user_id]
            );
            
            // Log activity
            $this->logActivity($user_id, 'password_change', 'User changed password');
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to change password: ' . $e->getMessage());
        }
    }
    
    /**
     * Log activity
     * 
     * @param int $user_id User ID
     * @param string $action Action
     * @param string $details Details
     * @return void
     */
    private function logActivity($user_id, $action, $details)
    {
        try {
            $this->db->execute(
                "INSERT INTO audit_log (user_id, action, details, ip_address, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $user_id,
                    $action,
                    $details,
                    Security::getClientIP(),
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
                ]
            );
        } catch (Exception $e) {
            // Log error tapi jangan stop process
        }
    }
}
