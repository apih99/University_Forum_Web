<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Get all categories with topic and reply counts
$sql = "SELECT c.*, 
        COUNT(DISTINCT t.topic_id) as topic_count,
        COUNT(DISTINCT r.reply_id) as reply_count,
        c.admin_only
        FROM categories c
        LEFT JOIN topics t ON c.category_id = t.category_id
        LEFT JOIN replies r ON t.topic_id = r.topic_id
        GROUP BY c.category_id
        ORDER BY c.category_id ASC";
$categories = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="forum-header">
        <h1>UPNM Forum</h1>
        <p>Connect, Share, and Learn with the UPNM Community</p>
    </div>

    <div class="categories-container">
        <?php while ($category = $categories->fetch_assoc()): ?>
            <div class="category-card">
                <div class="category-icon">
                    <?php
                    $icon = match($category['name']) {
                        'General Discussion' => 'fa-comments',
                        'Academic' => 'fa-graduation-cap',
                        'Campus Life' => 'fa-university',
                        'Announcements' => 'fa-bullhorn',
                        'Extracurriculum' => 'fa-futbol',
                        default => 'fa-folder'
                    };
                    ?>
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="category-content">
                    <h3>
                        <a href="category.php?id=<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                            <?php if ($category['admin_only']): ?>
                                <span class="admin-badge">Admin Only</span>
                            <?php endif; ?>
                        </a>
                    </h3>
                    <p class="category-description">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>
                    <div class="category-stats">
                        <span>
                            <i class="fas fa-file-alt"></i>
                            <?php echo $category['topic_count']; ?> Topics
                        </span>
                        <span>
                            <i class="fas fa-reply"></i>
                            <?php echo $category['reply_count']; ?> Replies
                        </span>
                        <span>
                            <i class="fas fa-clock"></i>
                            <?php echo getLastActiveTime($category['category_id']); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> UPNM Forum. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 