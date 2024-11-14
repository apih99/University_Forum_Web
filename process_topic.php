<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$category_id = (int)$_POST['category_id'];
$title = trim($_POST['title']);
$content = trim($_POST['content']);

// Sanitize HTML content
$allowed_tags = '<p><br><strong><em><u><s><blockquote><code><h1><h2><ol><ul><li><sub><sup><img><a>';
$content = strip_tags($content, $allowed_tags);

// Validate input
if (empty($title) || empty($content)) {
    $_SESSION['error'] = "Title and content are required.";
    header("Location: create_topic.php?category_id=" . $category_id);
    exit();
}

// Create the topic
$sql = "INSERT INTO topics (category_id, user_id, title, content) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $category_id, $user_id, $title, $content);

if ($stmt->execute()) {
    $_SESSION['success'] = "Topic created successfully!";
    header("Location: topic.php?id=" . $conn->insert_id);
} else {
    $_SESSION['error'] = "Error creating topic: " . $conn->error;
    header("Location: create_topic.php?category_id=" . $category_id);
}
exit();
?> 