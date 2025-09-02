<?php
// Prevent any output before JSON response
ob_start();

// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Set proper headers
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/functions.php';

    // Validate input
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid patient ID');
    }

    $patient_id = (int)$_GET['id'];
    
    // Get patient details
    $query = "SELECT * FROM patients WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $patient_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("Failed to get result: " . mysqli_error($conn));
    }
    
    $patient = mysqli_fetch_assoc($result);
    if (!$patient) {
        throw new Exception("Patient not found");
    }
    
    // Format dates
    $patient['date_of_birth'] = date('Y-m-d', strtotime($patient['date_of_birth']));
    $patient['created_at'] = date('Y-m-d H:i:s', strtotime($patient['created_at']));
    
    // Get appointments
    $appointments_query = "SELECT * FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC";
    $stmt = mysqli_prepare($conn, $appointments_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare appointments statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $patient_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute appointments statement: " . mysqli_stmt_error($stmt));
    }
    
    $appointments_result = mysqli_stmt_get_result($stmt);
    if (!$appointments_result) {
        throw new Exception("Failed to get appointments result: " . mysqli_error($conn));
    }
    
    $appointments = [];
    while ($row = mysqli_fetch_assoc($appointments_result)) {
        $row['appointment_date'] = date('Y-m-d H:i:s', strtotime($row['appointment_date']));
        $appointments[] = $row;
    }
    
    // Get documents
    $documents_query = "SELECT * FROM documents WHERE patient_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $documents_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare documents statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $patient_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute documents statement: " . mysqli_stmt_error($stmt));
    }
    
    $documents_result = mysqli_stmt_get_result($stmt);
    if (!$documents_result) {
        throw new Exception("Failed to get documents result: " . mysqli_error($conn));
    }
    
    $documents = [];
    while ($row = mysqli_fetch_assoc($documents_result)) {
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $documents[] = $row;
    }
    
    // Clear any output buffer
    ob_clean();
    
    // Combine all data
    $response = [
        'success' => true,
        'patient' => $patient,
        'appointments' => $appointments,
        'documents' => $documents
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    error_log("Error in get_patient_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // End output buffering
    ob_end_flush();
}
?> 