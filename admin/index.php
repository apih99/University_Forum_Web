<?php
require_once '../config/config.php';

// Check if user is admin
function isAdmin($user_id) {
    global $conn;
    $sql = "SELECT is_admin FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['is_admin'] == 1;
}

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'topics' => $conn->query("SELECT COUNT(*) as count FROM topics")->fetch_assoc()['count'],
    'replies' => $conn->query("SELECT COUNT(*) as count FROM replies")->fetch_assoc()['count'],
    'categories' => $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --bg-light: #f5f6fa;
            --border-color: #dcdde1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            display: flex;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: 280px;
            background: var(--primary-color);
            min-height: 100vh;
            padding: 2rem 0;
            position: fixed;
            left: 0;
            top: 0;
        }

        .admin-logo {
            color: var(--text-light);
            padding: 0 2rem 2rem;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-nav {
            margin-top: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--secondary-color);
            border-left-color: var(--accent-color);
        }

        .nav-item i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: var(--text-dark);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-title {
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .recent-activity {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .activity-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .activity-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
        }

        .activity-table tr:hover {
            background: var(--bg-light);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .return-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            background: var(--accent-color);
            border-radius: 6px;
            margin-top: 1rem;
            transition: background 0.3s ease;
        }

        .return-link:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <div class="admin-logo">
            <i class="fas fa-shield-alt"></i>
            <span>Admin Panel</span>
        </div>
        
        <nav class="admin-nav">
            <a href="index.php" class="nav-item active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
            <a href="manage_categories.php" class="nav-item">
                <i class="fas fa-folder"></i>
                <span>Manage Categories</span>
            </a>
            <a href="manage_topics.php" class="nav-item">
                <i class="fas fa-comments"></i>
                <span>Manage Topics</span>
            </a>
            <a href="../index.php" class="nav-item">
                <i class="fas fa-arrow-left"></i>
                <span>Return to Forum</span>
            </a>
        </nav>
    </div>

    <main class="admin-content">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p>Welcome to the admin panel. Here's an overview of your forum.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">
                    <i class="fas fa-users"></i>
                    Total Users
                </div>
                <div class="stat-number"><?php echo $stats['users']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <i class="fas fa-comments"></i>
                    Total Topics
                </div>
                <div class="stat-number"><?php echo $stats['topics']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <i class="fas fa-reply"></i>
                    Total Replies
                </div>
                <div class="stat-number"><?php echo $stats['replies']; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <i class="fas fa-folder"></i>
                    Categories
                </div>
                <div class="stat-number"><?php echo $stats['categories']; ?></div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="activity-header">
                <h2>Recent Activity</h2>
            </div>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT topics.title, users.username, topics.created_at 
                            FROM topics 
                            JOIN users ON topics.user_id = users.user_id 
                            ORDER BY created_at DESC LIMIT 5";
                    $recent = $conn->query($sql);
                    ?>
                    <?php while ($activity = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['title']); ?></td>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></td>
                        <td><span class="status-badge status-active">Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html> 