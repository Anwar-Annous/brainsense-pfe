<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $doc_id = $data['id'] ?? null;

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

    // Check permissions (only admin or the uploader can delete)
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    if ($user_role !== 'admin' && $document['uploaded_by'] != $user_id) {
        throw new Exception('You do not have permission to delete this document');
    }

    // Soft delete (update status to deleted)
    $query = "UPDATE documents SET status = 'deleted' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $doc_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to delete document');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Document deleted successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 