<?php
/**
 * Admin Statistics
 * 
 * Analytics dan statistics dashboard.
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

// Get clicks by day (last 7 days)
$clicks_by_day = $db->fetchAll(
    "SELECT DATE(cl.created_at) as date, COUNT(*) as count 
     FROM clicks_log cl
     JOIN links l ON cl.link_id = l.id
     WHERE l.created_by = ? AND cl.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(cl.created_at)
     ORDER BY date ASC",
    [$user['id']]
);

// Get top referrers
$top_referrers = $db->fetchAll(
    "SELECT referer, COUNT(*) as count 
     FROM clicks_log cl
     JOIN links l ON cl.link_id = l.id
     WHERE l.created_by = ? AND referer IS NOT NULL AND referer != ''
     GROUP BY referer
     ORDER BY count DESC
     LIMIT 10",
    [$user['id']]
);

// Get browser statistics
$browser_stats = $db->fetchAll(
    "SELECT user_agent, COUNT(*) as count 
     FROM clicks_log cl
     JOIN links l ON cl.link_id = l.id
     WHERE l.created_by = ?
     GROUP BY user_agent
     ORDER BY count DESC
     LIMIT 10",
    [$user['id']]
);

// Get top IPs
$top_ips = $db->fetchAll(
    "SELECT ip, COUNT(*) as count 
     FROM clicks_log cl
     JOIN links l ON cl.link_id = l.id
     WHERE l.created_by = ?
     GROUP BY ip
     ORDER BY count DESC
     LIMIT 10",
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
    <title>Statistics - <?php echo htmlspecialchars($config['app_name']); ?></title>
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
                <a href="links.php" class="nav-item">
                    <span class="icon">🔗</span>
                    <span>Shortlinks</span>
                </a>
                <a href="stats.php" class="nav-item active">
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
                    <h1>Statistics</h1>
                    <p>Analytics and insights</p>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Summary Cards -->
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
                </div>
                
                <!-- Clicks by Day -->
                <div class="dashboard-section">
                    <h2>Clicks by Day (Last 7 Days)</h2>
                    <div class="chart-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clicks</th>
                                    <th>Chart</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($clicks_by_day): ?>
                                    <?php 
                                    $max_clicks = max(array_column($clicks_by_day, 'count'));
                                    foreach ($clicks_by_day as $day): 
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($day['date'], 'M d, Y'); ?></td>
                                        <td><?php echo formatNumber($day['count']); ?></td>
                                        <td>
                                            <div class="bar-chart">
                                                <div class="bar" style="width: <?php echo ($day['count'] / $max_clicks) * 100; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Top Referrers -->
                <div class="dashboard-section">
                    <h2>Top Referrers</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Referrer</th>
                                    <th>Clicks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_referrers): ?>
                                    <?php foreach ($top_referrers as $referrer): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($referrer['referer']); ?>" target="_blank">
                                                <?php echo truncateString($referrer['referer'], 50); ?>
                                            </a>
                                        </td>
                                        <td><?php echo formatNumber($referrer['count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Browser Statistics -->
                <div class="dashboard-section">
                    <h2>Browser Statistics</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User Agent</th>
                                    <th>Clicks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($browser_stats): ?>
                                    <?php foreach ($browser_stats as $browser): ?>
                                    <tr>
                                        <td><?php echo truncateString($browser['user_agent'], 50); ?></td>
                                        <td><?php echo formatNumber($browser['count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Top IPs -->
                <div class="dashboard-section">
                    <h2>Top IP Addresses</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Clicks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_ips): ?>
                                    <?php foreach ($top_ips as $ip): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ip['ip']); ?></td>
                                        <td><?php echo formatNumber($ip['count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/admin.js"></script>
</body>
</html>
