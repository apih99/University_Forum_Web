<?php
require_once 'config/config.php';

// Get topic ID from URL
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get topic details with user and category info
$sql = "SELECT topics.*, users.username, categories.name as category_name 
        FROM topics 
        JOIN users ON topics.user_id = users.user_id 
        JOIN categories ON topics.category_id = categories.category_id 
        WHERE topic_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$topic = $stmt->get_result()->fetch_assoc();

if (!$topic) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - <?php echo htmlspecialchars($topic['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .topic-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .topic-header {
            padding: 2rem;
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-bottom: 1px solid #e9ecef;
        }

        .topic-title {
            font-size: 2rem;
            color: #1a2b3c;
            margin: 0 0 1rem 0;
            font-weight: 700;
            line-height: 1.3;
        }

        .topic-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6c757d;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-details {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-weight: 600;
            color: #3498db;
        }

        .post-date {
            font-size: 0.875rem;
            color: #8795a1;
        }

        .topic-category {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            color: #495057;
        }

        .topic-content {
            padding: 2rem;
            font-size: 1.1rem;
            line-height: 1.7;
            color: #2c3e50;
        }

        .topic-content p {
            margin-bottom: 1.5rem;
        }

        .topic-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .replies-section {
            margin-top: 1rem;
            padding: 0 1rem;
        }

        .replies-section h3 {
            font-size: 1.1rem;
            color: #1a2b3c;
            margin-bottom: 1rem;
        }

        .replies-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .reply-card {
            display: flex;
            gap: 0.75rem;
            padding: 0.5rem 0;
        }

        .reply-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .reply-content-wrapper {
            flex: 1;
        }

        .reply-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .author-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1a2b3c;
        }

        .reply-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .reply-content {
            font-size: 0.95rem;
            line-height: 1.4;
            color: #2c3e50;
        }

        .reply-form-container {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .reply-form {
            display: flex;
            flex: 1;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .reply-form textarea {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            resize: none;
            min-height: 36px;
            max-height: 120px;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .reply-form button {
            background: #3498db;
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .reply-form button:hover {
            background: #2980b9;
        }

        .reply-form button i {
            font-size: 0.9rem;
        }

        /* Optional: Add hover state for reply cards */
        .reply-card:hover {
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
        }

        .login-prompt {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .login-prompt a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="topic-container">
            <div class="topic-header">
                <h1 class="topic-title"><?php echo htmlspecialchars($topic['title']); ?></h1>
                <div class="topic-meta">
                    <div class="author-info">
                        <img src="assets/images/default-avatar.png" class="author-avatar" alt="Author">
                        <div class="author-details">
                            <span class="author-name"><?php echo htmlspecialchars($topic['username']); ?></span>
                            <span class="post-date"><?php echo date('F j, Y \a\t g:i a', strtotime($topic['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="topic-category">
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($topic['category_name']); ?>
                    </div>
                </div>
            </div>
            
            <div class="topic-content">
                <?php echo $topic['content']; ?>
            </div>

            <!-- Replies Section -->
            <div class="replies-section">
                <h3>Replies</h3>
                
                <?php
                // Fetch replies for this topic
                $sql = "SELECT replies.*, users.username, users.profile_picture 
                        FROM replies 
                        JOIN users ON replies.user_id = users.user_id 
                        WHERE topic_id = ? 
                        ORDER BY created_at ASC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                $replies = $stmt->get_result();
                ?>

                <div class="replies-list">
                    <?php while ($reply = $replies->fetch_assoc()): ?>
                        <div class="reply-card">
                            <img src="<?php echo $reply['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="Avatar" class="reply-avatar">
                            <div class="reply-content-wrapper">
                                <div class="reply-header">
                                    <span class="author-name"><?php echo htmlspecialchars($reply['username']); ?></span>
                                    <span class="reply-date"><?php echo date('M j', strtotime($reply['created_at'])); ?></span>
                                </div>
                                <div class="reply-content">
                                    <?php echo $reply['content']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="reply-form-container">
                        <img src="<?php echo $_SESSION['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                             alt="Your Avatar" class="reply-avatar">
                        <form action="process_reply.php" method="POST" class="reply-form">
                            <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
                            <textarea name="content" placeholder="Write your reply..." required></textarea>
                            <button type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 