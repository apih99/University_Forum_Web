<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$topic_id = (int)$_POST['topic_id'];
$content = trim($_POST['content']);

// Validate input
if (empty($content)) {
    $_SESSION['error'] = "Reply content cannot be empty.";
    header("Location: topic.php?id=" . $topic_id);
    exit();
}

// Create the reply
$sql = "INSERT INTO replies (topic_id, user_id, content) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $topic_id, $user_id, $content);

if ($stmt->execute()) {
    $_SESSION['success'] = "Reply posted successfully!";
} else {
    $_SESSION['error'] = "Error posting reply: " . $conn->error;
}

header("Location: topic.php?id=" . $topic_id);
exit();
?> 