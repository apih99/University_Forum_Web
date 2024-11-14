<?php
session_start();
session_destroy(); // Remove the user's "ID card"
header("Location: login.php"); // Send them back to login page
exit();
?> 