<?php
session_start();
require_once '../config/config.php';

// Check admin privileges
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verify admin role
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

// Handle topic actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $topic_id = (int)$_POST['topic_id'];
                // Delete replies first due to foreign key constraint
                $stmt = $conn->prepare("DELETE FROM replies WHERE topic_id = ?");
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                // Then delete the topic
                $stmt = $conn->prepare("DELETE FROM topics WHERE topic_id = ?");
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                break;

            case 'toggle_status':
                $topic_id = (int)$_POST['topic_id'];
                $status = $_POST['status'] === 'closed' ? 'open' : 'closed';
                $stmt = $conn->prepare("UPDATE topics SET status = ? WHERE topic_id = ?");
                $stmt->bind_param("si", $status, $topic_id);
                $stmt->execute();
                break;
        }
        header('Location: manage_topics.php');
        exit();
    }
}

// Get search query if any
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE t.title LIKE '%$search%'" : "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Manage Topics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a2b3c;
            --secondary-color: #2c3e50;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --success-color: #2ecc71;
            --bg-dark: #1a2b3c;
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

        .container {
            padding: 20px;
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

        /* Search section */
        .search-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        /* Topics table */
        .topics-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .topics-table th,
        .topics-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .topics-table th {
            background: rgba(0, 0, 0, 0.2);
            font-weight: 500;
        }

        .topics-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Status badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-open {
            background: var(--success-color);
            color: white;
        }

        .status-closed {
            background: var(--danger-color);
            color: white;
        }

        /* Buttons */
        .btn {
            padding: 8px 15px;
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

        .btn-view {
            background: var(--accent-color);
        }

        .btn-toggle {
            background: var(--warning-color);
            color: #000;
        }

        .btn-delete {
            background: var(--danger-color);
        }

        /* Pagination */
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-links">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="index.php" class="nav-link">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="manage_users.php" class="nav-link">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="manage_categories.php" class="nav-link">
                <i class="fas fa-folder"></i> Manage Categories
            </a>
            <a href="manage_topics.php" class="nav-link active">
                <i class="fas fa-comments"></i> Manage Topics
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search topics..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-view">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <!-- Topics Table -->
        <table class="topics-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Created Date</th>
                    <th>Replies</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT t.*, c.name as category_name, u.username, 
                        (SELECT COUNT(*) FROM replies r WHERE r.topic_id = t.topic_id) as reply_count 
                        FROM topics t 
                        JOIN categories c ON t.category_id = c.category_id 
                        JOIN users u ON t.user_id = u.user_id 
                        $where_clause 
                        ORDER BY t.created_at DESC 
                        LIMIT ? OFFSET ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $items_per_page, $offset);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($topic = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($topic['title']); ?></td>
                    <td><?php echo htmlspecialchars($topic['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($topic['username']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                    <td><?php echo $topic['reply_count']; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $topic['status']; ?>">
                            <?php echo ucfirst($topic['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="../topic.php?id=<?php echo $topic['topic_id']; ?>" class="btn btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="topic_id" value="<?php echo $topic['topic_id']; ?>">
                            <input type="hidden" name="status" value="<?php echo $topic['status']; ?>">
                            <button type="submit" class="btn btn-toggle">
                                <i class="fas fa-lock<?php echo $topic['status'] === 'closed' ? '-open' : ''; ?>"></i>
                                <?php echo $topic['status'] === 'closed' ? 'Open' : 'Close'; ?>
                            </button>
                        </form>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this topic?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="topic_id" value="<?php echo $topic['topic_id']; ?>">
                            <button type="submit" class="btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        // Get total topics count
        $count_sql = "SELECT COUNT(*) as count FROM topics t $where_clause";
        $count_result = $conn->query($count_sql);
        $total_topics = $count_result->fetch_assoc()['count'];
        $total_pages = ceil($total_topics / $items_per_page);
        ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                   class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html> 