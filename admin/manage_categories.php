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

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                if (!empty($name)) {
                    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $description);
                    $stmt->execute();
                }
                break;

            case 'edit':
                $category_id = (int)$_POST['category_id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                if (!empty($name)) {
                    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                    $stmt->bind_param("ssi", $name, $description, $category_id);
                    $stmt->execute();
                }
                break;

            case 'delete':
                $category_id = (int)$_POST['category_id'];
                $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                break;
        }
        header('Location: manage_categories.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Manage Categories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Copy the same root and body styles from manage_users.php */
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

        /* Add category form styles */
        .add-category-form {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        /* Table styles */
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .categories-table th,
        .categories-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .categories-table th {
            background: rgba(0, 0, 0, 0.2);
        }

        /* Button styles */
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
        }

        .btn-add {
            background: var(--success-color);
        }

        .btn-edit {
            background: var(--accent-color);
        }

        .btn-delete {
            background: var(--danger-color);
        }

        /* Navigation styles - same as manage_users.php */
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

        .container {
            padding: 20px;
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
            <a href="manage_categories.php" class="nav-link active">
                <i class="fas fa-folder"></i> Manage Categories
            </a>
            <a href="manage_topics.php" class="nav-link">
                <i class="fas fa-comments"></i> Manage Topics
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Add Category Form -->
        <div class="add-category-form">
            <h2>Add New Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit" class="btn btn-add">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <table class="categories-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Topics Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT c.*, COUNT(t.topic_id) as topic_count 
                        FROM categories c 
                        LEFT JOIN topics t ON c.category_id = t.category_id 
                        GROUP BY c.category_id";
                $result = $conn->query($sql);
                while ($category = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td><?php echo $category['topic_count']; ?></td>
                    <td>
                        <button class="btn btn-edit" onclick="editCategory(<?php echo $category['category_id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this category?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                            <button type="submit" class="btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Category Modal -->
    <script>
        function editCategory(categoryId) {
            // You can implement a modal or redirect to an edit page
            // For now, we'll use a simple prompt
            const newName = prompt("Enter new category name:");
            const newDescription = prompt("Enter new category description:");
            
            if (newName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" value="${categoryId}">
                    <input type="hidden" name="name" value="${newName}">
                    <input type="hidden" name="description" value="${newDescription}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 