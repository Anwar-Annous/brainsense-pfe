<?php
require_once 'db_connect.php';

function getDocumentById($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM documents WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getDocuments($category_id = null, $search = '') {
    global $conn;
    $where = [];
    
    if ($category_id) {
        $category_id = mysqli_real_escape_string($conn, $category_id);
        $where[] = "category_id = '$category_id'";
    }
    
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $where[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
    }
    
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT d.*, c.name as category_name, p.full_name as patient_name 
              FROM documents d 
              LEFT JOIN document_categories c ON d.category_id = c.id 
              LEFT JOIN patients p ON d.patient_id = p.id 
              $whereClause 
              ORDER BY d.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $documents = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    
    return $documents;
}

function getDocumentCategories() {
    global $conn;
    $query = "SELECT * FROM document_categories ORDER BY name";
    $result = mysqli_query($conn, $query);
    $categories = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

function uploadDocument($name, $description, $category_id, $patient_id, $file) {
    global $conn;
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $category_id = mysqli_real_escape_string($conn, $category_id);
        $patient_id = $patient_id ? mysqli_real_escape_string($conn, $patient_id) : 'NULL';
        $file_type = $file['type'];
        $file_size = $file['size'];
        
        $query = "INSERT INTO documents (name, description, file_path, file_type, file_size, category_id, patient_id, uploaded_by, created_at) 
                  VALUES ('$name', '$description', '$file_path', '$file_type', '$file_size', " . 
                  ($category_id ? "'$category_id'" : "NULL") . ", " . 
                  ($patient_id !== 'NULL' ? "'$patient_id'" : "NULL") . ", 1, NOW())";
        
        if (mysqli_query($conn, $query)) {
            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document_id' => mysqli_insert_id($conn)
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'Failed to upload document'
    ];
}

function deleteDocument($id) {
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    
    // Get file path before deleting
    $query = "SELECT file_path FROM documents WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $document = mysqli_fetch_assoc($result);
    
    if ($document && file_exists($document['file_path'])) {
        unlink($document['file_path']); // Delete the file
    }
    
    // Delete from database
    $query = "DELETE FROM documents WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Failed to delete document'];
}

function getDocumentStats() {
    global $conn;
    
    // Get total documents
    $query = "SELECT COUNT(*) as total FROM documents";
    $result = mysqli_query($conn, $query);
    $total = mysqli_fetch_assoc($result)['total'];
    
    // Get total size
    $query = "SELECT SUM(file_size) as total_size FROM documents";
    $result = mysqli_query($conn, $query);
    $total_size = mysqli_fetch_assoc($result)['total_size'] ?? 0;
    
    // Get documents per category
    $query = "SELECT c.name, COUNT(d.id) as count 
              FROM document_categories c 
              LEFT JOIN documents d ON c.id = d.category_id 
              GROUP BY c.id";
    $result = mysqli_query($conn, $query);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    // Get recent uploads (today)
    $query = "SELECT COUNT(*) as recent FROM documents WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $query);
    $recent = mysqli_fetch_assoc($result)['recent'];
    
    return [
        'total_documents' => $total,
        'total_size' => $total_size,
        'categories' => $categories,
        'recent_uploads' => $recent
    ];
}
?> 