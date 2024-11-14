<?php
require_once 'config/config.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'all';

function highlightText($text, $search) {
    return preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight">$1</span>', $text);
}

if ($search_query) {
    // Base queries for different types
    $topic_query = "SELECT t.*, u.username, c.name as category_name 
                   FROM topics t 
                   JOIN users u ON t.user_id = u.user_id 
                   JOIN categories c ON t.category_id = c.category_id 
                   WHERE t.title LIKE ? OR t.content LIKE ?";
    
    $post_query = "SELECT r.*, t.title as topic_title, u.username, r.content 
                  FROM replies r 
                  JOIN topics t ON r.topic_id = t.topic_id 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.content LIKE ?";
    
    $user_query = "SELECT * FROM users WHERE username LIKE ? OR full_name LIKE ?";

    $search_param = "%{$search_query}%";
    
    // Execute relevant queries based on search type
    $results = [
        'topics' => [],
        'posts' => [],
        'users' => []
    ];

    if ($search_type == 'all' || $search_type == 'topics') {
        $stmt = $conn->prepare($topic_query);
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $results['topics'] = $stmt->get_result();
    }

    if ($search_type == 'all' || $search_type == 'posts') {
        $stmt = $conn->prepare($post_query);
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $results['posts'] = $stmt->get_result();
    }

    if ($search_type == 'all' || $search_type == 'users') {
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $results['users'] = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Search Results</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-results-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .search-header {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .search-header h2 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .search-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .search-stats {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .results-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-header h3 {
            color: #2c3e50;
            margin: 0;
        }

        .result-item {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-item:hover {
            background-color: #f8f9fa;
        }

        .result-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .result-title a {
            color: #3498db;
            text-decoration: none;
        }

        .result-title a:hover {
            text-decoration: underline;
        }

        .result-meta {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .result-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .result-excerpt {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 0.1rem 0.2rem;
            border-radius: 2px;
        }

        .no-results {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            color: #666;
            text-decoration: none;
        }

        .tab.active {
            background-color: #3498db;
            color: white;
        }

        .tab:hover:not(.active) {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="search-results-container">
        <div class="search-header">
            <h2>Search Results</h2>
            <?php if ($search_query): ?>
                <p>Showing results for: <strong><?php echo htmlspecialchars($search_query); ?></strong></p>
                <div class="search-stats">
                    <?php
                    $total_results = 0;
                    if (isset($results)) {
                        foreach ($results as $type => $result) {
                            if ($result instanceof mysqli_result) {
                                $total_results += $result->num_rows;
                            }
                        }
                    }
                    echo "Found {$total_results} results";
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($search_query): ?>
            <div class="tabs">
                <a href="?q=<?php echo urlencode($search_query); ?>&type=all" 
                   class="tab <?php echo $search_type == 'all' ? 'active' : ''; ?>">All</a>
                <a href="?q=<?php echo urlencode($search_query); ?>&type=topics" 
                   class="tab <?php echo $search_type == 'topics' ? 'active' : ''; ?>">Topics</a>
                <a href="?q=<?php echo urlencode($search_query); ?>&type=posts" 
                   class="tab <?php echo $search_type == 'posts' ? 'active' : ''; ?>">Posts</a>
                <a href="?q=<?php echo urlencode($search_query); ?>&type=users" 
                   class="tab <?php echo $search_type == 'users' ? 'active' : ''; ?>">Users</a>
            </div>

            <?php if ($total_results > 0): ?>
                <?php if (($search_type == 'all' || $search_type == 'topics') && $results['topics']->num_rows > 0): ?>
                    <div class="results-section">
                        <div class="section-header">
                            <i class="fas fa-comments"></i>
                            <h3>Topics</h3>
                        </div>
                        <?php while ($topic = $results['topics']->fetch_assoc()): ?>
                            <div class="result-item">
                                <div class="result-title">
                                    <a href="topic.php?id=<?php echo $topic['topic_id']; ?>">
                                        <?php echo highlightText(htmlspecialchars($topic['title']), $search_query); ?>
                                    </a>
                                </div>
                                <div class="result-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($topic['username']); ?></span>
                                    <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($topic['category_name']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php if (($search_type == 'all' || $search_type == 'posts') && $results['posts']->num_rows > 0): ?>
                    <div class="results-section">
                        <div class="section-header">
                            <i class="fas fa-comment-dots"></i>
                            <h3>Posts</h3>
                        </div>
                        <?php while ($post = $results['posts']->fetch_assoc()): ?>
                            <div class="result-item">
                                <div class="result-title">
                                    <a href="topic.php?id=<?php echo $post['topic_id']; ?>#reply-<?php echo $post['reply_id']; ?>">
                                        Re: <?php echo htmlspecialchars($post['topic_title']); ?>
                                    </a>
                                </div>
                                <div class="result-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['username']); ?></span>
                                </div>
                                <div class="result-excerpt">
                                    <?php echo highlightText(htmlspecialchars(substr($post['content'], 0, 200)) . '...', $search_query); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php if (($search_type == 'all' || $search_type == 'users') && $results['users']->num_rows > 0): ?>
                    <div class="results-section">
                        <div class="section-header">
                            <i class="fas fa-users"></i>
                            <h3>Users</h3>
                        </div>
                        <?php while ($user = $results['users']->fetch_assoc()): ?>
                            <div class="result-item">
                                <div class="result-title">
                                    <a href="profile.php?id=<?php echo $user['user_id']; ?>">
                                        <?php echo highlightText(htmlspecialchars($user['username']), $search_query); ?>
                                    </a>
                                </div>
                                <div class="result-meta">
                                    <span><i class="fas fa-user"></i> <?php echo highlightText(htmlspecialchars($user['full_name']), $search_query); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="results-section">
                    <div class="no-results">
                        <i class="fas fa-search" style="font-size: 2rem; color: #666; margin-bottom: 1rem;"></i>
                        <p>No results found for your search.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 