<?php
/**
 * Validator Class - Input Validation
 * 
 * Menyediakan static methods untuk validasi semua input.
 * Prevent SQL injection, XSS, dan invalid data.
 * 
 * Usage:
 *   if (!Validator::validateUrl($url)) { throw new Exception('Invalid URL'); }
 *   if (!Validator::validateCode($code)) { throw new Exception('Invalid code'); }
 */

class Validator
{
    /**
     * Validate URL format
     * 
     * @param string $url URL to validate
     * @return bool True jika valid
     */
    public static function validateUrl($url)
    {
        // Check if empty
        if (empty($url)) {
            return false;
        }
        
        // Check length (max 2000 chars)
        if (strlen($url) > 2000) {
            return false;
        }
        
        // Check if valid URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if HTTP or HTTPS
        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate short code
     * 
     * @param string $code Short code to validate
     * @param int $min_length Minimum length
     * @param int $max_length Maximum length
     * @return bool True jika valid
     */
    public static function validateCode($code, $min_length = 3, $max_length = 20)
    {
        // Check if empty
        if (empty($code)) {
            return false;
        }
        
        // Check length
        if (strlen($code) < $min_length || strlen($code) > $max_length) {
            return false;
        }
        
        // Check if alphanumeric + underscore + dash
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $code)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email
     * 
     * @param string $email Email to validate
     * @return bool True jika valid
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate username
     * 
     * @param string $username Username to validate
     * @param int $min_length Minimum length
     * @param int $max_length Maximum length
     * @return bool True jika valid
     */
    public static function validateUsername($username, $min_length = 3, $max_length = 50)
    {
        // Check if empty
        if (empty($username)) {
            return false;
        }
        
        // Check length
        if (strlen($username) < $min_length || strlen($username) > $max_length) {
            return false;
        }
        
        // Check if alphanumeric + underscore
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @param int $min_length Minimum length
     * @return bool True jika valid
     */
    public static function validatePassword($password, $min_length = 8)
    {
        // Check if empty
        if (empty($password)) {
            return false;
        }
        
        // Check length
        if (strlen($password) < $min_length) {
            return false;
        }
        
        // Check if has uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check if has lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check if has number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate integer
     * 
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return bool True jika valid
     */
    public static function validateInteger($value, $min = null, $max = null)
    {
        if (!is_numeric($value) || intval($value) != $value) {
            return false;
        }
        
        $value = intval($value);
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate boolean
     * 
     * @param mixed $value Value to validate
     * @return bool True jika valid
     */
    public static function validateBoolean($value)
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeInput($input)
    {
        // Trim whitespace
        $input = trim($input);
        
        // Remove special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Sanitize URL
     * 
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitizeUrl($url)
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * Check if valid JSON
     * 
     * @param string $string String to check
     * @return bool True jika valid JSON
     */
    public static function isValidJSON($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Validate array keys
     * 
     * @param array $array Array to validate
     * @param array $required_keys Required keys
     * @return bool True jika semua required keys ada
     */
    public static function validateArrayKeys($array, $required_keys)
    {
        if (!is_array($array)) {
            return false;
        }
        
        foreach ($required_keys as $key) {
            if (!isset($array[$key]) || empty($array[$key])) {
                return false;
            }
        }
        
        return true;
    }
}
