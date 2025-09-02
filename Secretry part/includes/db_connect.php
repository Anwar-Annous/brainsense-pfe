<?php
// Prevent any output before headers
ob_start();

// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'brainsense';

// Create connection with database name
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]));
}
    
    // Set charset to utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    error_log("Error setting charset: " . mysqli_error($conn));
    die(json_encode([
        'success' => false,
        'message' => 'Error setting database charset'
    ]));
}
    
    // Set timezone
    date_default_timezone_set('UTC');
?> 