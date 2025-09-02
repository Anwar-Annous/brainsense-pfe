<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them in JSON

// Set JSON header
header('Content-Type: application/json');

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

try {
    // Validate required fields
    $required_fields = ['full_name', 'email', 'phone', 'national_id', 'date_of_birth', 'gender', 'blood_type'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields.");
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    // Validate phone number (basic validation)
    if (!preg_match('/^[0-9+\-\s()]{10,}$/', $_POST['phone'])) {
        throw new Exception("Please enter a valid phone number.");
    }

    // Handle profile photo upload
    $profile_photo_path = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['profile_photo']['type'], $allowed_types)) {
            throw new Exception("Invalid file type. Please upload a JPG, PNG, or GIF image.");
        }

        if ($_FILES['profile_photo']['size'] > $max_size) {
            throw new Exception("File size too large. Maximum size is 5MB.");
        }

        $upload_dir = 'uploads/patients/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }

        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('patient_') . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
            throw new Exception("Failed to upload profile photo.");
        }
        
        $profile_photo_path = $target_path;
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO patients (
        full_name, email, phone, address, national_id, 
        date_of_birth, gender, blood_type, allergies, 
        medical_conditions, profile_photo, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    // Bind parameters
    if (!mysqli_stmt_bind_param($stmt, "sssssssssss",
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['national_id'],
        $_POST['date_of_birth'],
        $_POST['gender'],
        $_POST['blood_type'],
        $_POST['allergies'],
        $_POST['medical_conditions'],
        $profile_photo_path
    )) {
        throw new Exception("Error binding parameters: " . mysqli_stmt_error($stmt));
    }

    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
    }

    // Get the inserted ID
    $patient_id = mysqli_insert_id($conn);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Patient added successfully',
        'patient_id' => $patient_id
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log("Error in add_patient.php: " . $e->getMessage());
    
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