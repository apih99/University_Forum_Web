<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: messages.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$subject = trim($_POST['subject']);
$content = trim($_POST['content']);

if (!empty($subject) && !empty($content)) {
    $sql = "INSERT INTO private_messages (sender_id, receiver_id, subject, content) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $content);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Message sent successfully!";
    } else {
        $_SESSION['error'] = "Error sending message.";
    }
}

header("Location: messages.php");
exit();
?> 
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: messages.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$subject = trim($_POST['subject']);
$content = trim($_POST['content']);

if (!empty($subject) && !empty($content)) {
    $sql = "INSERT INTO private_messages (sender_id, receiver_id, subject, content) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $content);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Message sent successfully!";
    } else {
        $_SESSION['error'] = "Error sending message.";
    }
}

header("Location: messages.php");
exit();
?> 