<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
require_once 'config/config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully<br>";
}

// Print PHP info
phpinfo();
?>