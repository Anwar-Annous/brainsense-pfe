<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['file'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $shared_with = $_POST['shared_with'] ?? 'both';
    $patient_id = !empty($_POST['patient_id']) ? (int)$_POST['patient_id'] : null;
    $doctor_id = !empty($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : null;

    // Validate file
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Allowed types: PDF, JPEG, PNG, DOC, DOCX');
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }

    // Insert document record
    $query = "INSERT INTO documents (title, description, file_path, file_type, file_size, uploaded_by, shared_with, patient_id, doctor_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssssisiii', 
        $title, 
        $description, 
        $filepath, 
        $file['type'], 
        $file['size'], 
        $_SESSION['user_id'], 
        $shared_with, 
        $patient_id, 
        $doctor_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to save document record');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Document uploaded successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 