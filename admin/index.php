<?php
/**
 * Admin Dashboard
 * 
 * Dashboard dengan overview statistics.
 */

// Load configuration
$config = require '../config.php';

// Load required classes
require '../core/Database.php';
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

// Get statistics
$total_links = $shortener->getTotalCount($user['id']);
$total_clicks = $shortener->getTotalClicks($user['id']);

// Get recent links
$recent_links = $shortener->getAllShortlinks($user['id'], 5, 0);

// Get clicks today
$clicks_today = $db->fetchColumn(
    "SELECT COUNT(*) FROM clicks_log cl
     JOIN links l ON cl.link_id = l.id
     WHERE l.created_by = ? AND DATE(cl.created_at) = CURDATE()",
    [$user['id']]
);

// Get top links
$top_links = $db->fetchAll(
    "SELECT code, url, click_count FROM links 
     WHERE created_by = ? 
     ORDER BY click_count DESC LIMIT 5",
    [$user['id']]
);

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($config['app_name']); ?></title>
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
                <a href="index.php" class="nav-item active">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="links.php" class="nav-item">
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
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="links.php?action=create" class="btn btn-primary">+ Create Shortlink</a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #3498db;">🔗</div>
                        <div class="stat-content">
                            <h3>Total Shortlinks</h3>
                            <p class="stat-value"><?php echo formatNumber($total_links); ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #27ae60;">👁️</div>
                        <div class="stat-content">
                            <h3>Total Clicks</h3>
                            <p class="stat-value"><?php echo formatNumber($total_clicks); ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #f39c12;">📅</div>
                        <div class="stat-content">
                            <h3>Clicks Today</h3>
                            <p class="stat-value"><?php echo formatNumber($clicks_today); ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #e74c3c;">⚡</div>
                        <div class="stat-content">
                            <h3>Avg Clicks/Link</h3>
                            <p class="stat-value"><?php echo $total_links > 0 ? formatNumber(round($total_clicks / $total_links)) : '0'; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Links -->
                <div class="dashboard-section">
                    <h2>Recent Shortlinks</h2>
                    <?php if ($recent_links): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>URL</th>
                                    <th>Clicks</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_links as $link): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($link['code']); ?></code>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" title="<?php echo htmlspecialchars($link['url']); ?>">
                                            <?php echo truncateString($link['url'], 40); ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatNumber($link['click_count']); ?></td>
                                    <td><?php echo formatDate($link['created_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="links.php?action=edit&id=<?php echo $link['id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="links.php?action=delete&id=<?php echo $link['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this link?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No shortlinks created yet. <a href="links.php?action=create">Create one now</a></p>
                    <?php endif; ?>
                </div>
                
                <!-- Top Links -->
                <div class="dashboard-section">
                    <h2>Top Shortlinks</h2>
                    <?php if ($top_links): ?>
                    <div class="top-links-list">
                        <?php foreach ($top_links as $index => $link): ?>
                        <div class="top-link-item">
                            <div class="rank"><?php echo $index + 1; ?></div>
                            <div class="link-info">
                                <p class="link-code"><?php echo htmlspecialchars($link['code']); ?></p>
                                <p class="link-url"><?php echo truncateString($link['url'], 50); ?></p>
                            </div>
                            <div class="link-clicks"><?php echo formatNumber($link['click_count']); ?> clicks</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
