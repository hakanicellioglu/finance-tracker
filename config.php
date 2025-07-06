<?php
// Basic database configuration for XAMPP

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'finance_tracker';

// Create a MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

?>
