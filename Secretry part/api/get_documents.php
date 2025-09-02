<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Set error reporting to log file only
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Set proper headers
header('Content-Type: application/json');

try {
    // Validate database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Log the request
    error_log("Document request received with filter: " . $filter);
    
    // Base query
    $query = "SELECT d.*, p.full_name as patient_name 
              FROM documents d 
              LEFT JOIN patients p ON d.patient_id = p.id";
    
    // Add filter conditions
    switch($filter) {
        case 'recent':
            $query .= " WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'pending':
            $query .= " WHERE d.status = 'pending'";
            break;
        case 'approved':
            $query .= " WHERE d.status = 'approved'";
            break;
        // 'all' case doesn't need additional conditions
    }
    
    $query .= " ORDER BY d.created_at DESC";
    
    // Log the query
    error_log("Executing query: " . $query);
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    $documents = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => $row['type'],
            'status' => $row['status'],
            'patient_name' => $row['patient_name'],
            'created_at' => $row['created_at'],
            'file_path' => $row['file_path']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_documents.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 