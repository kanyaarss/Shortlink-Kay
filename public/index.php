<?php
/**
 * Homepage - Shortlink Creator
 * 
 * Halaman utama untuk membuat shortlink.
 */

// Load configuration
$config = require '../config.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');

// Check if form submitted
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Load required classes
        require '../core/Database.php';
        require '../core/Validator.php';
        require '../core/Security.php';
        require '../core/Helpers.php';
        require '../app/Shortener.php';
        
        // Get form data
        $url = $_POST['url'] ?? null;
        $code_type = $_POST['code_type'] ?? 'random';
        $custom_code = $_POST['custom_code'] ?? null;
        
        // Validate URL
        if (!$url) {
            throw new Exception('URL is required');
        }
        
        if (!Validator::validateUrl($url)) {
            throw new Exception('Invalid URL format');
        }
        
        // Connect to database
        $db = new Database($config);
        
        // Create shortener
        $shortener = new Shortener($db, $config);
        
        // Create shortlink
        if ($code_type === 'custom') {
            if (!$custom_code) {
                throw new Exception('Custom code is required');
            }
            
            if (!Validator::validateCode($custom_code)) {
                throw new Exception('Invalid custom code (3-20 alphanumeric characters)');
            }
            
            $link = $shortener->createShortlink($url, $custom_code);
        } else {
            $link = $shortener->createShortlink($url);
        }
        
        // Set result
        $result = [
            'code' => $link['code'],
            'short_url' => $config['base_url'] . '/' . $link['code'],
            'original_url' => $link['url'],
        ];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['app_name']); ?> - Pemendek URL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($config['app_name']); ?></h1>
            <p>Pendekkan URL Anda dengan mudah</p>
        </div>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="url">URL Asli</label>
                    <input 
                        type="url" 
                        id="url" 
                        name="url" 
                        placeholder="https://example.com/very/long/url"
                        value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label>Tipe Short Code</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="code_type" value="random" <?php echo (!isset($_POST['code_type']) || $_POST['code_type'] === 'random') ? 'checked' : ''; ?>>
                            Random Code
                        </label>
                        <label>
                            <input type="radio" name="code_type" value="custom" <?php echo isset($_POST['code_type']) && $_POST['code_type'] === 'custom' ? 'checked' : ''; ?>>
                            Custom Code
                        </label>
                    </div>
                </div>
                
                <div class="form-group" id="customCodeGroup" style="display: none;">
                    <label for="custom_code">Custom Code</label>
                    <input 
                        type="text" 
                        id="custom_code" 
                        name="custom_code" 
                        placeholder="mycode (3-20 karakter)"
                        value="<?php echo isset($_POST['custom_code']) ? htmlspecialchars($_POST['custom_code']) : ''; ?>"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary">Buat Shortlink</button>
            </form>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($result): ?>
        <div class="result-container">
            <div class="result-content">
                <h3>✓ Shortlink Berhasil Dibuat!</h3>
                <div class="result-item">
                    <label>Short URL</label>
                    <div class="result-value">
                        <input type="text" id="shortUrl" value="<?php echo htmlspecialchars($result['short_url']); ?>" readonly>
                        <button type="button" class="btn btn-small" onclick="copyToClipboard('shortUrl')">Copy</button>
                    </div>
                </div>
                <div class="result-item">
                    <label>Original URL</label>
                    <div class="result-value">
                        <input type="text" id="originalUrl" value="<?php echo htmlspecialchars($result['original_url']); ?>" readonly>
                    </div>
                </div>
                <div class="result-item">
                    <label>Short Code</label>
                    <div class="result-value">
                        <input type="text" id="shortCode" value="<?php echo htmlspecialchars($result['code']); ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>Made with ❤️ by Shortlink Kay</p>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>
