<?php
/**
 * Admin Links Management
 * 
 * CRUD untuk shortlink management.
 */

// Load configuration
$config = require '../config.php';

// Load required classes
require '../core/Database.php';
require '../core/Validator.php';
require '../core/Security.php';
require '../core/Helpers.php';
require '../app/Auth.php';
require '../app/Shortener.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name']);
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirect($config['base_url'] . '/admin/login.php');
}

// Connect to database
$db = new Database($config);

// Create instances
$auth = new Auth($db, $config);
$shortener = new Shortener($db, $config);

// Get current user
$user = $auth->getUser();

if (!$user) {
    $auth->logout();
    redirect($config['base_url'] . '/admin/login.php');
}

// Initialize variables
$action = $_GET['action'] ?? 'list';
$error = null;
$success = null;
$link = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'list';
        
        if ($action === 'create') {
            // Create shortlink
            $url = $_POST['url'] ?? null;
            $custom_code = $_POST['custom_code'] ?? null;
            
            if (!$url) {
                throw new Exception('URL is required');
            }
            
            if (!Validator::validateUrl($url)) {
                throw new Exception('Invalid URL format');
            }
            
            $link = $shortener->createShortlink($url, $custom_code, $user['id']);
            $success = 'Shortlink created successfully!';
            $action = 'list';
        } elseif ($action === 'update') {
            // Update shortlink
            $link_id = $_POST['link_id'] ?? null;
            $url = $_POST['url'] ?? null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (!$link_id || !$url) {
                throw new Exception('Link ID and URL required');
            }
            
            if (!Validator::validateUrl($url)) {
                throw new Exception('Invalid URL format');
            }
            
            $shortener->updateShortlink($link_id, [
                'url' => $url,
                'is_active' => $is_active
            ]);
            
            $success = 'Shortlink updated successfully!';
            $action = 'list';
        } elseif ($action === 'delete') {
            // Delete shortlink
            $link_id = $_POST['link_id'] ?? null;
            
            if (!$link_id) {
                throw new Exception('Link ID required');
            }
            
            $shortener->deleteShortlink($link_id);
            $success = 'Shortlink deleted successfully!';
            $action = 'list';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get link for edit
if ($action === 'edit' && isset($_GET['id'])) {
    $link = $shortener->getShortlinkById($_GET['id']);
    if (!$link) {
        $error = 'Link not found';
        $action = 'list';
    }
}

// Get all links
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$all_links = $shortener->getAllShortlinks($user['id'], $per_page, $offset);
$total_links = $shortener->getTotalCount($user['id']);
$total_pages = ceil($total_links / $per_page);

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shortlinks - <?php echo htmlspecialchars($config['app_name']); ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars($config['app_name']); ?></h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="links.php" class="nav-item active">
                    <span class="icon">🔗</span>
                    <span>Shortlinks</span>
                </a>
                <a href="stats.php" class="nav-item">
                    <span class="icon">📈</span>
                    <span>Statistics</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">👤</div>
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="btn btn-small btn-danger">Logout</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-title">
                    <h1><?php echo $action === 'create' ? 'Create Shortlink' : ($action === 'edit' ? 'Edit Shortlink' : 'Shortlinks'); ?></h1>
                </div>
                <div class="header-actions">
                    <?php if ($action !== 'create' && $action !== 'edit'): ?>
                    <a href="links.php?action=create" class="btn btn-primary">+ Create Shortlink</a>
                    <?php else: ?>
                    <a href="links.php" class="btn btn-secondary">← Back</a>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'create' || $action === 'edit'): ?>
                <!-- Form -->
                <div class="form-container">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="url">Original URL</label>
                            <input 
                                type="url" 
                                id="url" 
                                name="url" 
                                placeholder="https://example.com/very/long/url"
                                value="<?php echo $link ? htmlspecialchars($link['url']) : ''; ?>"
                                required
                            >
                        </div>
                        
                        <?php if ($action === 'create'): ?>
                        <div class="form-group">
                            <label for="custom_code">Custom Code (Optional)</label>
                            <input 
                                type="text" 
                                id="custom_code" 
                                name="custom_code" 
                                placeholder="Leave empty for random code"
                            >
                            <small>3-20 alphanumeric characters</small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($action === 'edit'): ?>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" <?php echo $link['is_active'] ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $action === 'create' ? 'Create Shortlink' : 'Update Shortlink'; ?>
                            </button>
                            <a href="links.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                
                <?php else: ?>
                <!-- List -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>URL</th>
                                <th>Clicks</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($all_links): ?>
                                <?php foreach ($all_links as $l): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($l['code']); ?></code>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($l['url']); ?>">
                                            <?php echo truncateString($l['url'], 40); ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatNumber($l['click_count']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $l['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $l['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($l['created_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="links.php?action=edit&id=<?php echo $l['id']; ?>" class="btn btn-small">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this link?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="link_id" value="<?php echo $l['id']; ?>">
                                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No shortlinks found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="links.php?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
