<?php
// Suppress errors
error_reporting(0);
ini_set('display_errors', 0);

// Ensure no output before headers
ob_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'brainsense';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Database connection failed: " . mysqli_connect_error()
    ]);
    exit();
}

// Set charset to utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Error setting charset: " . mysqli_error($conn)
    ]);
    exit();
}

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_GET['type'])) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Recipient type is required']);
    exit();
}

$type = $_GET['type'];

try {
    if ($type === 'patient') {
        $query = "SELECT id, full_name as name FROM patients ORDER BY full_name ASC";
    } elseif ($type === 'doctor') {
        $query = "SELECT id, name FROM doctors ORDER BY name ASC";
    } else {
        throw new Exception("Invalid recipient type");
    }
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    $recipients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $recipients[] = $row;
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'recipients' => $recipients
    ]);
} catch(Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch recipients: ' . $e->getMessage()
    ]);
} 

// End output buffering
ob_end_flush(); 