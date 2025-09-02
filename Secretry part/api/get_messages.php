<?php
// Prevent any output before headers
ob_start();

// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . '/../includes/db_connect.php';

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

try {
    // Check if messages table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'messages'");
    if (!$table_check) {
        throw new Exception("Error checking messages table: " . mysqli_error($conn));
    }
    if (mysqli_num_rows($table_check) == 0) {
        throw new Exception("Messages table does not exist");
    }

    // Get messages where secretary is either sender or recipient
    $query = "SELECT m.*, 
              CASE 
                  WHEN m.sender_type = 'patient' THEN ps.full_name
                  WHEN m.recipient_type = 'patient' THEN pr.full_name
                  WHEN m.sender_type = 'doctor' THEN d.name
                  WHEN m.recipient_type = 'doctor' THEN d.name
              END as contact_name,
              CASE 
                  WHEN m.sender_type = 'patient' THEN 'Patient'
                  WHEN m.recipient_type = 'patient' THEN 'Patient'
                  WHEN m.sender_type = 'doctor' THEN 'Doctor'
                  WHEN m.recipient_type = 'doctor' THEN 'Doctor'
              END as contact_type
              FROM messages m 
              LEFT JOIN patients ps ON m.sender_id = ps.id AND m.sender_type = 'patient'
              LEFT JOIN patients pr ON m.recipient_id = pr.id AND m.recipient_type = 'patient'
              LEFT JOIN doctors d ON (m.sender_id = d.id AND m.sender_type = 'doctor') 
                                OR (m.recipient_id = d.id AND m.recipient_type = 'doctor')
              WHERE m.sender_type = 'secretary' OR m.recipient_type = 'secretary'
              ORDER BY m.created_at DESC 
              LIMIT 50";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format dates
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $row['updated_at'] = date('Y-m-d H:i:s', strtotime($row['updated_at']));
        $messages[] = $row;
    }
    
    // Clear any previous output
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);

} catch(Exception $e) {
    error_log("Error in get_messages.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clear any previous output
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch messages: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush(); 