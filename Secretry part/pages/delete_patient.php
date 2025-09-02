<?php
// Prevent any HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

try {
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Patient ID is required');
    }

    $patient_id = (int)$_GET['id'];

    // Delete patient record
    $query = "DELETE FROM patients WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $patient_id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Patient record deleted successfully'
            ]);
        } else {
            throw new Exception('Patient not found');
        }
    } else {
        throw new Exception('Error deleting patient record');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Clean up
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 