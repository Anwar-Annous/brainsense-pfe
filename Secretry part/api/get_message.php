<?php
// Suppress errors
error_reporting(0);
ini_set('display_errors', 0);

// Ensure no output before headers
ob_start();

require_once __DIR__ . '/db_connect.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Message ID is required');
        }

        $message_id = (int)$_GET['id'];
        
        // Get message details with sender and recipient information
        $query = "SELECT m.*, 
                 CASE 
                     WHEN m.sender_type = 'patient' THEN ps.full_name
                     WHEN m.sender_type = 'doctor' THEN ds.name
                     WHEN m.sender_type = 'secretary' THEN 'Secretary'
                 END as sender_name,
                 CASE 
                     WHEN m.recipient_type = 'patient' THEN pr.full_name
                     WHEN m.recipient_type = 'doctor' THEN dr.name
                     WHEN m.recipient_type = 'secretary' THEN 'Secretary'
                 END as recipient_name
                 FROM messages m 
                 LEFT JOIN patients ps ON m.sender_id = ps.id AND m.sender_type = 'patient'
                 LEFT JOIN patients pr ON m.recipient_id = pr.id AND m.recipient_type = 'patient'
                 LEFT JOIN doctors ds ON m.sender_id = ds.id AND m.sender_type = 'doctor'
                 LEFT JOIN doctors dr ON m.recipient_id = dr.id AND m.recipient_type = 'doctor'
                 WHERE m.id = ?";
                 
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $message_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $message = mysqli_fetch_assoc($result);

        if (!$message) {
            throw new Exception('Message not found');
        }

        // Mark message as read
        $update_query = "UPDATE messages SET status = 'read' WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $message_id);
        mysqli_stmt_execute($update_stmt);

        // Clear any previous output
        ob_clean();

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);

    } catch(Exception $e) {
        // Clear any previous output
        ob_clean();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Clear any previous output
    ob_clean();
    
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
} 

// End output buffering
ob_end_flush(); 