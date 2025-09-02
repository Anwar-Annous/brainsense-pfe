<?php
// Prevent any output before JSON response
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

try {
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("Patient ID is required");
    }

    // Sanitize input
    $id = (int)$_GET['id'];
    if ($id <= 0) {
        throw new Exception("Invalid patient ID");
    }

    // Fetch patient details
    $query = "SELECT * FROM patients WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_bind_param($stmt, "i", $id)) {
        throw new Exception("Error binding parameters: " . mysqli_stmt_error($stmt));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);

    if ($patient = mysqli_fetch_assoc($result)) {
        // Format dates
        $patient['date_of_birth'] = date('Y-m-d', strtotime($patient['date_of_birth']));
        $patient['created_at'] = date('M d, Y', strtotime($patient['created_at']));
        $patient['last_password_change'] = date('M d, Y', strtotime($patient['last_password_change']));

        // Calculate age
        $dob = new DateTime($patient['date_of_birth']);
        $now = new DateTime();
        $age = $dob->diff($now)->y;

        // Prepare response data
        $response = [
            'success' => true,
            'id' => (int)$patient['id'],
            'full_name' => $patient['full_name'],
            'email' => $patient['email'],
            'phone' => $patient['phone'],
            'address' => $patient['address'] ?? '',
            'national_id' => $patient['national_id'],
            'date_of_birth' => $patient['date_of_birth'],
            'age' => $age,
            'gender' => $patient['gender'],
            'blood_type' => $patient['blood_type'],
            'allergies' => $patient['allergies'] ?? '',
            'medical_conditions' => $patient['medical_conditions'] ?? '',
            'created_at' => $patient['created_at'],
            'last_password_change' => $patient['last_password_change'],
            'profile_photo' => $patient['profile_photo'] ?? null
        ];

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Return success response
        echo json_encode($response);
    } else {
        throw new Exception("Patient not found");
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log("Error in get_patient_details.php: " . $e->getMessage());
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Return error response
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
    mysqli_close($conn);
}
?> 