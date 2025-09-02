<?php
header('Content-Type: application/json');
require_once '../includes/document_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
$log_dir = __DIR__ . '/../logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'upload':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Validate required fields
            if (empty($_POST['name']) || empty($_POST['category_id'])) {
                throw new Exception('Name and category are required');
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $error = isset($_FILES['file']) ? $_FILES['file']['error'] : 'No file uploaded';
                throw new Exception('File upload error: ' . $error);
            }

            $result = uploadDocument(
                $_POST['name'],
                $_POST['description'] ?? '',
                $_POST['category_id'],
                $_POST['patient_id'] ?? null,
                $_FILES['file']
            );

            if (!$result['success']) {
                throw new Exception($result['error']);
            }

            echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
            break;
        
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? 0;
                $result = deleteDocument($id);
                echo json_encode(['success' => $result]);
            }
            break;
        
        case 'list':
            $category_id = $_GET['category_id'] ?? null;
            $search = $_GET['search'] ?? '';
            $documents = getDocuments($category_id, $search);
            echo json_encode(['success' => true, 'documents' => $documents]);
            break;
        
        case 'stats':
            $stats = getDocumentStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
        
        case 'categories':
            $categories = getDocumentCategories();
            echo json_encode(['success' => true, 'categories' => $categories]);
            break;

        case 'create_category':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if (empty($name)) {
                    echo json_encode(['success' => false, 'error' => 'Category name is required']);
                    break;
                }
                
                global $conn;
                $name = mysqli_real_escape_string($conn, $name);
                $description = mysqli_real_escape_string($conn, $description);
                
                $query = "INSERT INTO document_categories (name, description) VALUES ('$name', '$description')";
                $result = mysqli_query($conn, $query);
                
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create category']);
                }
            }
            break;
        
        case 'download':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $document = getDocumentById($id);
                if ($document) {
                    $file_path = $document['file_path'];
                    if (file_exists($file_path)) {
                        header('Content-Type: ' . $document['file_type']);
                        header('Content-Disposition: attachment; filename="' . basename($document['name']) . '"');
                        header('Content-Length: ' . filesize($file_path));
                        readfile($file_path);
                        exit;
                    }
                }
                echo json_encode(['success' => false, 'error' => 'File not found']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Document ID is required']);
            }
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Document operation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 