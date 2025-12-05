<?php
/**
 * Admin Login Page
 * 
 * Form untuk login ke admin panel.
 */

// Load configuration
$config = require '../config.php';

// Load required classes
require '../core/Database.php';
require '../core/Validator.php';
require '../core/Security.php';
require '../core/Helpers.php';
require '../app/Auth.php';

// Initialize variables
$error = null;
$success = null;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        
        // Validate input
        if (!$username || !$password) {
            throw new Exception('Username and password required');
        }
        
        // Connect to database
        $db = new Database($config);
        
        // Create auth instance
        $auth = new Auth($db, $config);
        
        // Attempt login
        $auth->login($username, $password);
        
        // Redirect to dashboard
        redirect($config['base_url'] . '/admin/index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    redirect($config['base_url'] . '/admin/index.php');
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo htmlspecialchars($config['app_name']); ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><?php echo htmlspecialchars($config['app_name']); ?></h1>
                <p>Admin Panel</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Default credentials: <strong>admin</strong> / <strong>admin123</strong></p>
                <p><a href="/">Back to Home</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
