<?php
session_start();

// Clear session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Optionally remove the session cookie
setcookie(session_name(), '', time() - 3600, '/');

// Redirect to login page
header("Location: ../login.php");
exit();
?>  