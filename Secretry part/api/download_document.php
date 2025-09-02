<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

try {
    $doc_id = $_GET['id'] ?? null;
    if (!$doc_id) {
        throw new Exception('Document ID is required');
    }

    // Get document information
    $query = "SELECT * FROM documents WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $doc_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $document = mysqli_fetch_assoc($result);

    if (!$document) {
        throw new Exception('Document not found');
    }

    // Check permissions
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    $has_access = false;
    if ($user_role === 'admin') {
        $has_access = true;
    } elseif ($user_role === 'patient' && ($document['patient_id'] == $user_id || $document['shared_with'] === 'patient' || $document['shared_with'] === 'both')) {
        $has_access = true;
    } elseif ($user_role === 'doctor' && ($document['doctor_id'] == $user_id || $document['shared_with'] === 'doctor' || $document['shared_with'] === 'both')) {
        $has_access = true;
    }

    if (!$has_access) {
        throw new Exception('You do not have permission to access this document');
    }

    // Check if file exists
    if (!file_exists($document['file_path'])) {
        throw new Exception('File not found on server');
    }

    // Set headers for download
    header('Content-Type: ' . $document['file_type']);
    header('Content-Disposition: attachment; filename="' . basename($document['file_path']) . '"');
    header('Content-Length: ' . filesize($document['file_path']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output file
    readfile($document['file_path']);
    exit;

} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 