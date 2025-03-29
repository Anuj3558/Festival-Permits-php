<?php
// Include the functions file
require_once 'auth/functions.php';

// Call the logout function
logout();

// Optional: Redirect after logout
header("Location: login.php"); // Redirect to login page
exit();
?>