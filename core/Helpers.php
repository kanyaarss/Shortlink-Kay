<?php
/**
 * Helper Functions
 * 
 * Kumpulan helper functions yang digunakan di berbagai tempat.
 * 
 * Usage:
 *   $code = generateRandomCode();
 *   echo formatDate($date);
 *   echo getTimeAgo($date);
 */

/**
 * Generate random short code
 * 
 * @param int $length Code length
 * @param string $charset Character set
 * @return string Random code
 */
function generateRandomCode($length = 6, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
{
    $code = '';
    $charset_len = strlen($charset);
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $charset[random_int(0, $charset_len - 1)];
    }
    
    return $code;
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s')
{
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Get time ago (e.g., "2 hours ago")
 * 
 * @param string $date Date string
 * @return string Time ago
 */
function getTimeAgo($date)
{
    if (empty($date)) {
        return '-';
    }
    
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 0) {
        return 'in the future';
    } elseif ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Truncate string
 * 
 * @param string $string String to truncate
 * @param int $length Max length
 * @param string $suffix Suffix (default: "...")
 * @return string Truncated string
 */
function truncateString($string, $length = 50, $suffix = '...')
{
    if (strlen($string) <= $length) {
        return $string;
    }
    
    return substr($string, 0, $length) . $suffix;
}

/**
 * Format bytes to human readable
 * 
 * @param int $bytes Bytes
 * @return string Formatted bytes
 */
function formatBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Format number with thousand separator
 * 
 * @param int $number Number to format
 * @return string Formatted number
 */
function formatNumber($number)
{
    return number_format($number, 0, '.', ',');
}

/**
 * Parse user agent
 * 
 * @param string $user_agent User agent string
 * @return array Parsed user agent
 */
function parseUserAgent($user_agent)
{
    $result = [
        'browser' => 'Unknown',
        'os' => 'Unknown',
        'device' => 'desktop'
    ];
    
    if (empty($user_agent)) {
        return $result;
    }
    
    // Detect browser
    if (preg_match('/Chrome/', $user_agent)) {
        $result['browser'] = 'Chrome';
    } elseif (preg_match('/Firefox/', $user_agent)) {
        $result['browser'] = 'Firefox';
    } elseif (preg_match('/Safari/', $user_agent)) {
        $result['browser'] = 'Safari';
    } elseif (preg_match('/Edge/', $user_agent)) {
        $result['browser'] = 'Edge';
    } elseif (preg_match('/Opera/', $user_agent)) {
        $result['browser'] = 'Opera';
    }
    
    // Detect OS
    if (preg_match('/Windows/', $user_agent)) {
        $result['os'] = 'Windows';
    } elseif (preg_match('/Macintosh/', $user_agent)) {
        $result['os'] = 'macOS';
    } elseif (preg_match('/Linux/', $user_agent)) {
        $result['os'] = 'Linux';
    } elseif (preg_match('/iPhone/', $user_agent)) {
        $result['os'] = 'iOS';
        $result['device'] = 'mobile';
    } elseif (preg_match('/Android/', $user_agent)) {
        $result['os'] = 'Android';
        $result['device'] = 'mobile';
    }
    
    return $result;
}

/**
 * Get domain from URL
 * 
 * @param string $url URL
 * @return string Domain
 */
function getDomainFromUrl($url)
{
    $parsed = parse_url($url);
    return $parsed['host'] ?? '';
}

/**
 * Check if URL is valid and accessible
 * 
 * @param string $url URL to check
 * @param int $timeout Timeout in seconds
 * @return bool True jika accessible
 */
function isUrlAccessible($url, $timeout = 5)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code >= 200 && $http_code < 400;
}

/**
 * Convert array to JSON
 * 
 * @param array $data Data to convert
 * @param int $options JSON options
 * @return string JSON string
 */
function toJson($data, $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
{
    return json_encode($data, $options);
}

/**
 * Convert JSON to array
 * 
 * @param string $json JSON string
 * @return array Array
 */
function fromJson($json)
{
    return json_decode($json, true);
}

/**
 * Check if string starts with
 * 
 * @param string $haystack String to search in
 * @param string $needle String to search for
 * @return bool True jika starts with
 */
function startsWith($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}

/**
 * Check if string ends with
 * 
 * @param string $haystack String to search in
 * @param string $needle String to search for
 * @return bool True jika ends with
 */
function endsWith($haystack, $needle)
{
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Check if string contains
 * 
 * @param string $haystack String to search in
 * @param string $needle String to search for
 * @return bool True jika contains
 */
function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}

/**
 * Get value from array with default
 * 
 * @param array $array Array
 * @param string $key Key
 * @param mixed $default Default value
 * @return mixed Value or default
 */
function arrayGet($array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 * @param int $code HTTP status code
 * @return void
 */
function redirect($url, $code = 302)
{
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Send JSON response
 * 
 * @param array $data Data to send
 * @param int $code HTTP status code
 * @return void
 */
function sendJson($data, $code = 200)
{
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * Log message
 * 
 * @param string $message Message to log
 * @param string $level Log level (debug, info, warning, error)
 * @param string $log_dir Log directory
 * @return void
 */
function logMessage($message, $level = 'info', $log_dir = null)
{
    if ($log_dir === null) {
        $log_dir = __DIR__ . '/../storage/logs';
    }
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/' . date('Y-m-d') . '.log';
    $log_entry = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
