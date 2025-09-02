<?php
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['content']) || empty(trim($data['content']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note content is required']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO quick_notes (content, created_by) VALUES (?, ?)");
        $created_by = 1; // Replace with actual user ID from session
        $stmt->bind_param("si", $data['content'], $created_by);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Note saved successfully']);
        } else {
            throw new Exception("Failed to save note");
        }
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save note: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} 