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
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    error_log("Received JSON input: " . $json);
    
    if (!$json) {
        throw new Exception("No input received");
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Validate required fields
    $required_fields = ['subject', 'message', 'recipient_id', 'recipient_type'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if messages table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'messages'");
    if (!$table_check) {
        throw new Exception("Error checking messages table: " . mysqli_error($conn));
    }
    if (mysqli_num_rows($table_check) == 0) {
        throw new Exception("Messages table does not exist");
    }

    // Sanitize input
    $subject = mysqli_real_escape_string($conn, $data['subject']);
    $message = mysqli_real_escape_string($conn, $data['message']);
    $recipient_id = (int)$data['recipient_id'];
    $recipient_type = mysqli_real_escape_string($conn, $data['recipient_type']);
    $priority = isset($data['priority']) ? mysqli_real_escape_string($conn, $data['priority']) : 'medium';
    
    // Validate recipient type
    if (!in_array($recipient_type, ['patient', 'doctor'])) {
        throw new Exception("Invalid recipient type: $recipient_type");
    }

    // Validate priority
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $priority = 'medium';
    }

    // Set sender information (replace with actual secretary ID from session)
    $sender_id = 1; // Temporary hardcoded value
    $sender_type = 'secretary';

    // Log the sanitized data
    error_log("Sanitized data: " . print_r([
        'subject' => $subject,
        'message' => $message,
        'recipient_id' => $recipient_id,
        'recipient_type' => $recipient_type,
        'priority' => $priority,
        'sender_id' => $sender_id,
        'sender_type' => $sender_type
    ], true));

    // Prepare and execute the query
    $query = "INSERT INTO messages (subject, message, sender_id, sender_type, recipient_id, recipient_type, priority) 
             VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    error_log("Preparing query: " . $query);
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssisiss", 
        $subject,
        $message,
        $sender_id,
        $sender_type,
        $recipient_id,
        $recipient_type,
        $priority
    );

    error_log("Executing statement with parameters: " . 
        "subject=$subject, message=$message, sender_id=$sender_id, " .
        "sender_type=$sender_type, recipient_id=$recipient_id, " .
        "recipient_type=$recipient_type, priority=$priority");

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }

    // Clear any previous output
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);

} catch(Exception $e) {
    error_log("Error in save_message.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clear any previous output
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message: ' . $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

// End output buffering
ob_end_flush(); 