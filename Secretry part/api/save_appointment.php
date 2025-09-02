<?php
require_once '../../config/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['patient_id']) || !isset($data['doctor_id']) || !isset($data['appointment_date']) || !isset($data['location'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, location, notes, created_by) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        $created_by = 1; // Replace with actual secretary ID from session
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt->bind_param("iisssi", 
            $data['patient_id'],
            $data['doctor_id'],
            $data['appointment_date'],
            $data['location'],
            $notes,
            $created_by
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully']);
        } else {
            throw new Exception("Failed to schedule appointment");
        }
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to schedule appointment: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} 