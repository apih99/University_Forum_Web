<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $faculty = trim($_POST['faculty']);
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = "profile_" . $user_id . "." . $filetype;
            $upload_path = "uploads/profiles/" . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $upload_path, $user_id);
                $stmt->execute();
            }
        }
    }
    
    // Update other profile information
    $sql = "UPDATE users SET full_name = ?, email = ?, faculty = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $full_name, $email, $faculty, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>UPNM Forum - My Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>

        <div class="profile-container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your personal information</p>
            </div>

            <div class="profile-content">
                <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                    <div class="profile-image-section">
                        <div class="profile-image">
                            <img src="<?php echo $user['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="Profile Picture" id="preview-image">
                            <div class="image-upload">
                                <label for="profile-picture">
                                    <i class="fas fa-camera"></i>
                                    Change Photo
                                </label>
                                <input type="file" id="profile-picture" name="profile_picture" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full-name">Full Name</label>
                            <input type="text" id="full-name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                   class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="faculty">Faculty</label>
                            <input type="text" id="faculty" name="faculty" 
                                   value="<?php echo htmlspecialchars($user['faculty']); ?>" 
                                   class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="student-id">Student ID</label>
                            <input type="text" id="student-id" name="student_id" 
                                   value="<?php echo htmlspecialchars($user['student_id']); ?>" 
                                   class="form-control" readonly>
                            <small class="form-text">Student ID cannot be changed</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 