<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <i class="fas fa-graduation-cap"></i> UPNM Forum
    </a>
    
    <div class="search-container">
        <form action="search.php" method="GET" class="search-form">
            <div class="search-wrapper">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="q" 
                           class="search-input" 
                           placeholder="Search topics, posts, or users..." 
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                           autocomplete="off">
                    <div class="search-dropdown">
                        <select name="type" class="search-select">
                            <option value="all">All Categories</option>
                            <option value="topics">Topics</option>
                            <option value="posts">Posts</option>
                            <option value="users">Users</option>
                        </select>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                </div>
                <button type="submit" class="search-button">
                    Search
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="navbar-menu">
        <a href="index.php" class="nav-link">
            <i class="fas fa-home"></i> Home
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="messages.php" class="nav-link">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <?php if ($_SESSION['is_admin']): ?>
                <a href="admin/" class="nav-link">
                    <i class="fas fa-cog"></i> Admin Panel
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="navbar-auth">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="welcome-text">
                <i class="fas fa-user-circle"></i>
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-link">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="nav-link">
                <i class="fas fa-user-plus"></i> Register
            </a>
        <?php endif; ?>
    </div>
</nav> 