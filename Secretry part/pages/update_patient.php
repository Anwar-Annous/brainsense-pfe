<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

try {
    // Log the received POST data
    error_log("Received POST data: " . print_r($_POST, true));

    // Check if all required fields are present
    $required_fields = ['id', 'full_name', 'email', 'phone', 'national_id', 'date_of_birth', 'gender', 'blood_type'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize and validate input
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $national_id = mysqli_real_escape_string($conn, $_POST['national_id']);
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $blood_type = mysqli_real_escape_string($conn, $_POST['blood_type']);
    $allergies = mysqli_real_escape_string($conn, $_POST['allergies'] ?? '');
    $medical_conditions = mysqli_real_escape_string($conn, $_POST['medical_conditions'] ?? '');

    // Log the sanitized data
    error_log("Sanitized data: " . print_r([
        'id' => $id,
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'national_id' => $national_id,
        'date_of_birth' => $date_of_birth,
        'gender' => $gender,
        'blood_type' => $blood_type,
        'allergies' => $allergies,
        'medical_conditions' => $medical_conditions
    ], true));

    // Handle profile photo upload
    $photoName = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid file type. Allowed: jpg, jpeg, png, gif.");
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception("File size too large. Maximum size is 5MB.");
        }
        
        $uploadDir = '../uploads/profiles';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'patient_' . $id . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $photoName = $VIPath;
        } else {
            throw new Exception("Failed to upload profile photo.");
        }
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate date format
    if (!strtotime($date_of_birth)) {
        throw new Exception("Invalid date format");
    }

    // Check if patient exists
    $check_query = "SELECT id, profile_photo FROM patients WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $patient = mysqli_fetch_assoc($result);
    mysqli_stmt_close($check_stmt);

    if (!$patient) {
        throw new Exception("Patient not found");
    }

    // Update patient record
    if ($photoName) {
        $update_query = "UPDATE patients SET 
            full_name = ?,
            email = ?,
            phone = ?,
            address = ?,
            national_id = ?,
            date_of_birth = ?,
            gender = ?,
            blood_type = ?,
            allergies = ?,
            medical_conditions = ?,
            profile_photo = ?,
            last_password_change = CURRENT_TIMESTAMP
            WHERE id = ?";

        $update_stmt = mysqli_prepare($conn, $update_query);
        if (!$update_stmt) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($update_stmt, "sssssssssssi", 
            $full_name,
            $email,
            $phone,
            $address,
            $national_id,
            $date_of_birth,
            $gender,
            $blood_type,
            $allergies,
            $medical_conditions,
            $photoName,
            $id
        );
    } else {
        $update_query = "UPDATE patients SET 
            full_name = ?,
            email = ?,
            phone = ?,
            address = ?,
            national_id = ?,
            date_of_birth = ?,
            gender = ?,
            blood_type = ?,
            allergies = ?,
            medical_conditions = ?,
            last_password_change = CURRENT_TIMESTAMP
            WHERE id = ?";

        $update_stmt = mysqli_prepare($conn, $update_query);
        if (!$update_stmt) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($update_stmt, "ssssssssssi", 
            $full_name,
            $email,
            $phone,
            $address,
            $national_id,
            $date_of_birth,
            $gender,
            $blood_type,
            $allergies,
            $medical_conditions,
            $id
        );
    }

    // Log the query and parameters
    error_log("Executing update query with parameters: " . print_r([
        'query' => $update_query,
        'params' => [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'national_id' => $national_id,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'blood_type' => $blood_type,
            'allergies' => $allergies,
            'medical_conditions' => $medical_conditions,
            'id' => $id
        ]
    ], true));

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Error updating patient record: " . mysqli_error($conn));
    }

    // Check if any rows were affected
    $affected_rows = mysqli_stmt_affected_rows($update_stmt);
    error_log("Affected rows: " . $affected_rows);

    if ($affected_rows === 0) {
        throw new Exception("No changes were made to the patient record");
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Patient record updated successfully',
        'affected_rows' => $affected_rows
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Error in update_patient.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Clean up
    if (isset($update_stmt)) {
        mysqli_stmt_close($update_stmt);
    }
    mysqli_close($conn);
} 