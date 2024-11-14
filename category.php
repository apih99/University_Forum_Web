<?php
require_once 'config/config.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get category details
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

// Get topics for this category with user info and reply count
$sql = "SELECT t.*, u.username, u.profile_picture, 
        (SELECT COUNT(*) FROM replies WHERE topic_id = t.topic_id) as reply_count 
        FROM topics t 
        JOIN users u ON t.user_id = u.user_id 
        WHERE t.category_id = ? 
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$topics = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - <?php echo htmlspecialchars($category['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .category-header {
            background: #1a2b3c;
            padding: 2rem;
            color: white;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .category-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .category-description {
            color: #e0e0e0;
        }

        .new-topic-btn {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 1.5rem;
            transition: background 0.3s;
        }

        .new-topic-btn:hover {
            background: #c0392b;
        }

        .topics-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .topic-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .topic-item:hover {
            background-color: #f8f9fa;
        }

        .topic-item:last-child {
            border-bottom: none;
        }

        .topic-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .topic-content {
            flex: 1;
        }

        .topic-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .topic-meta {
            font-size: 0.9rem;
            color: #666;
        }

        .topic-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        /* Make entire topic item clickable */
        .topic-link {
            display: flex;
            width: 100%;
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="category-header">
            <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_topic.php?category_id=<?php echo $category_id; ?>" class="new-topic-btn">
                <i class="fas fa-plus"></i> Create New Topic
            </a>
        <?php endif; ?>

        <div class="topics-list">
            <?php if ($topics->num_rows > 0): ?>
                <?php while ($topic = $topics->fetch_assoc()): ?>
                    <div class="topic-item">
                        <a href="topic.php?id=<?php echo $topic['topic_id']; ?>" class="topic-link">
                            <img src="<?php echo $topic['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="Avatar" class="topic-avatar">
                            <div class="topic-content">
                                <div class="topic-title">
                                    <?php echo htmlspecialchars($topic['title']); ?>
                                </div>
                                <div class="topic-meta">
                                    Posted by <?php echo htmlspecialchars($topic['username']); ?> • 
                                    <?php echo date('M j, Y', strtotime($topic['created_at'])); ?> • 
                                    <?php echo $topic['reply_count']; ?> replies
                                </div>
                            </div>
                            <div class="topic-stats">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-message">
                    <i class="fas fa-comments"></i>
                    <p>No topics yet. Be the first to create a topic!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 