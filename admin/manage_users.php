<?php
// Start the session if not already started
session_start();

// Include database configuration
require_once '../config/config.php';

// Debug line - remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, verify if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Then, check if user is admin by querying the database
$user_id = $_SESSION['user_id'];
$check_admin = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$check_admin->bind_param("i", $user_id);
$check_admin->execute();
$result = $check_admin->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'change_role':
                $current_role = $_POST['current_role'];
                $new_role = ($current_role === 'admin') ? 'student' : 'admin';
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_role, $user_id);
                $stmt->execute();
                break;

            case 'ban_user':
                $stmt = $conn->prepare("UPDATE users SET is_banned = NOT is_banned WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_id != ?");
                $stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
                $stmt->execute();
                break;
        }
        
        // Redirect to refresh the page
        header('Location: manage_users.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a2b3c;
            --secondary-color: #2c3e50;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --success-color: #2ecc71;
            --bg-dark: #1a2b3c; /* Dark blue background */
            --text-light: #ecf0f1;
            --border-color: #34495e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-dark);
            color: var(--text-light);
        }

        .manage-users-container {
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .page-header h1 {
            color: var(--text-light);
            margin: 0;
            font-size: 1.8rem;
        }

        .search-container {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            width: 300px;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
        }

        .users-table th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 500;
        }

        .users-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--accent-color);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            color: var(--text-light);
            font-weight: 500;
        }

        .user-email {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9em;
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            display: inline-block;
        }

        .role-admin {
            background: #3498db;
            color: white;
        }

        .role-student {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-active {
            background: var(--success-color);
            color: white;
        }

        .status-banned {
            background: var(--danger-color);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-change-role {
            background: var(--accent-color);
        }

        .btn-ban {
            background: var(--warning-color);
            color: #000;
        }

        .btn-delete {
            background: var(--danger-color);
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .page-link {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-light);
            transition: background 0.2s;
        }

        .page-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .page-link.active {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }

        /* Navigation styles */
        .admin-nav {
            background: var(--primary-color);
            padding: 10px 0;
            margin-bottom: 20px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            padding: 0 20px;
        }

        .nav-link {
            color: var(--text-light);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Add navigation menu -->
    <nav class="admin-nav">
        <div class="nav-links">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="index.php" class="nav-link">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="manage_users.php" class="nav-link active">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="manage_categories.php" class="nav-link">
                <i class="fas fa-folder"></i> Manage Categories
            </a>
            <a href="manage_topics.php" class="nav-link">
                <i class="fas fa-comments"></i> Manage Topics
            </a>
        </div>
    </nav>

    <div class="manage-users-container">
        <div class="page-header">
            <h1>Manage Users</h1>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" class="search-input" placeholder="Search users..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                $where_clause = $search ? "WHERE username LIKE '%$search%' OR email LIKE '%$search%'" : "";
                
                $sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
                $result = $conn->query($sql);
                
                while ($user = $result->fetch_assoc()):
                ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <img src="<?php echo $user['profile_picture'] ?? '../assets/images/default-avatar.png'; ?>" 
                                 class="user-avatar" alt="User avatar">
                            <div>
                                <div><?php echo htmlspecialchars($user['username']); ?></div>
                                <div style="color: #666; font-size: 0.9em;">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['is_banned'] ? 'status-banned' : 'status-active'; ?>">
                            <?php echo $user['is_banned'] ? 'Banned' : 'Active'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="current_role" value="<?php echo $user['role']; ?>">
                                <input type="hidden" name="action" value="change_role">
                                <button type="submit" class="btn btn-change-role">
                                    <i class="fas fa-user-shield"></i> Change Role
                                </button>
                            </form>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="ban_user">
                                <button type="submit" class="btn btn-ban">
                                    <i class="fas <?php echo $user['is_banned'] ? 'fa-user-check' : 'fa-ban'; ?>"></i>
                                    <?php echo $user['is_banned'] ? 'Unban' : 'Ban'; ?>
                                </button>
                            </form>

                            <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 