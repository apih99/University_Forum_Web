<div class="admin-sidebar">
    <div class="admin-logo">
        <i class="fas fa-shield-alt"></i>
        <span>Admin Panel</span>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php" class="nav-item">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="manage_users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Manage Users</span>
        </a>
        <a href="manage_categories.php" class="nav-item">
            <i class="fas fa-folder"></i>
            <span>Manage Categories</span>
        </a>
        <a href="manage_topics.php" class="nav-item">
            <i class="fas fa-comments"></i>
            <span>Manage Topics</span>
        </a>
        <a href="../index.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Return to Forum</span>
        </a>
    </nav>
</div> 