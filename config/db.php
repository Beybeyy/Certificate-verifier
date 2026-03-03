<?php
// Show errors (helpful for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
$servername = "localhost";      // MySQL server (usually localhost)
$username   = "root";           // MySQL username (default XAMPP)
$password   = "";               // MySQL password (default empty in XAMPP)
$database   = "certificatev";   // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
} else {
    // Uncomment for testing
    // echo "✅ DB connected successfully!";
}
