<?php
function getLastActiveTime($category_id) {
    global $conn;
    
    // Get the latest activity time from topics and replies
    $sql = "SELECT 
            GREATEST(
                COALESCE(MAX(t.created_at), '1970-01-01'),
                COALESCE(MAX(r.created_at), '1970-01-01')
            ) as last_active
            FROM categories c
            LEFT JOIN topics t ON c.category_id = t.category_id
            LEFT JOIN replies r ON t.topic_id = r.topic_id
            WHERE c.category_id = $category_id";
            
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    
    if (!$data || $data['last_active'] == '1970-01-01') {
        return 'No activity';
    }
    
    $timestamp = strtotime($data['last_active']);
    return formatTimeAgo($timestamp);
}

function formatTimeAgo($timestamp) {
    $current_time = time();
    $diff = $current_time - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

function get_avatar_url($user_id) {
    global $conn;
    $sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['profile_picture'] ?? 'assets/images/default-avatar.png';
}
?>