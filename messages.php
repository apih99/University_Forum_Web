<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all messages (inbox)
$sql = "SELECT pm.*, u.username as sender_name 
        FROM private_messages pm 
        JOIN users u ON pm.sender_id = u.user_id 
        WHERE pm.receiver_id = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Get all users for the new message form
$sql = "SELECT user_id, username FROM users WHERE user_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - Messages</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>

        <div class="messages-container">
            <div class="messages-header">
                <h2>My Messages</h2>
                <button onclick="showNewMessageForm()" class="new-message-btn">New Message</button>
            </div>

            <!-- New Message Form (Hidden by default) -->
            <div id="newMessageForm" class="new-message-form" style="display: none;">
                <h3>Send New Message</h3>
                <form method="POST" action="send_message.php">
                    <div class="form-group">
                        <label>To:</label>
                        <select name="receiver_id" required>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <option value="<?php echo $user['user_id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject:</label>
                        <input type="text" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label>Message:</label>
                        <textarea name="content" rows="4" required></textarea>
                    </div>
                    <button type="submit">Send Message</button>
                </form>
            </div>

            <!-- Messages List -->
            <div class="messages-list">
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="message <?php echo $message['read_status'] ? 'read' : 'unread'; ?>">
                        <div class="message-header">
                            <span class="sender">From: <?php echo htmlspecialchars($message['sender_name']); ?></span>
                            <span class="date"><?php echo date('M j, Y H:i', strtotime($message['created_at'])); ?></span>
                        </div>
                        <div class="message-subject">
                            <a href="view_message.php?id=<?php echo $message['message_id']; ?>">
                                <?php echo htmlspecialchars($message['subject']); ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
    function showNewMessageForm() {
        document.getElementById('newMessageForm').style.display = 'block';
    }
    </script>
</body>
</html> 