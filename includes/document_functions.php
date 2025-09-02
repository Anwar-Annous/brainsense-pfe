<?php
require_once 'db_connection.php';

function getDocumentStats() {
    global $conn;
    
    $stats = [
        'total_documents' => 0,
        'total_size' => 0,
        'recent_uploads' => 0,
        'categories' => []
    ];
    
    // Get total documents and size
    $query = "SELECT COUNT(*) as total, SUM(file_size) as size FROM documents";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['total_documents'] = $row['total'];
        $stats['total_size'] = $row['size'] ?? 0;
    }
    
    // Get recent uploads (today)
    $query = "SELECT COUNT(*) as recent FROM documents WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['recent_uploads'] = $row['recent'];
    }
    
    // Get category counts
    $query = "SELECT c.id, c.name, COUNT(d.id) as count 
              FROM document_categories c 
              LEFT JOIN documents d ON c.id = d.category_id 
              GROUP BY c.id, c.name";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['categories'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'count' => $row['count']
        ];
    }
    
    return $stats;
}

function getDocumentCategories() {
    global $conn;
    
    $categories = [];
    $query = "SELECT id, name, description FROM document_categories ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

function getDocuments($category_id = '', $search = '') {
    global $conn;
    
    $query = "SELECT d.*, c.name as category_name, p.full_name as patient_name 
              FROM documents d 
              LEFT JOIN document_categories c ON d.category_id = c.id 
              LEFT JOIN patients p ON d.patient_id = p.id 
              WHERE 1=1";
    
    if ($category_id) {
        $category_id = mysqli_real_escape_string($conn, $category_id);
        $query .= " AND d.category_id = '$category_id'";
    }
    
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $query .= " AND (d.name LIKE '%$search%' OR d.description LIKE '%$search%')";
    }
    
    $query .= " ORDER BY d.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    $documents = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    
    return $documents;
}

function createDocument($file, $name, $category_id, $patient_id = null, $description = '', $uploaded_by = null) {
    global $conn;
    
    // Validate file
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    // Validate file type and size
    $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/documents';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to save file'];
    }

    // Insert into database
    $name = mysqli_real_escape_string($conn, $name);
    $description = mysqli_real_escape_string($conn, $description);
    $filepath = mysqli_real_escape_string($conn, $filepath);
    $category_id = (int)$category_id;
    $patient_id = $patient_id ? (int)$patient_id : 'NULL';
    $uploaded_by = $uploaded_by ? (int)$uploaded_by : 'NULL';

    $query = "INSERT INTO documents (name, category_id, patient_id, description, file_path, file_type, file_size, uploaded_by) 
              VALUES ('$name', $category_id, $patient_id, '$description', '$filepath', '$file_type', {$file['size']}, $uploaded_by)";

    if (mysqli_query($conn, $query)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    }

    // If database insert fails, delete the uploaded file
    unlink($filepath);
    return ['success' => false, 'message' => 'Failed to save document information'];
}

function deleteDocument($id) {
    global $conn;
    
    // Get file path
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT file_path FROM documents WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $filepath = dirname(__DIR__) . '/' . $row['file_path'];
        
        // Delete file
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Delete from database
        $query = "DELETE FROM documents WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            return ['success' => true];
        }
    }
    
    return ['success' => false, 'error' => 'Failed to delete document'];
}

function createCategory($name, $description = '') {
    global $conn;
    
    $name = mysqli_real_escape_string($conn, $name);
    $description = mysqli_real_escape_string($conn, $description);
    
    $query = "INSERT INTO document_categories (name, description, created_at) 
              VALUES ('$name', '$description', NOW())";
    
    if (mysqli_query($conn, $query)) {
        return ['success' => true, 'id' => mysqli_insert_id($conn)];
    } else {
        return ['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)];
    }
}
?> 