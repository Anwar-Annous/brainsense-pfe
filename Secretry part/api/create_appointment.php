<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Combine date and time
        $appointment_date = date('Y-m-d H:i:s', strtotime($data['date'] . ' ' . $data['time']));
        
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, appointment_date, doctor_name, type, notes, status) 
                              VALUES (?, ?, ?, ?, ?, 'Scheduled')");
        
        $stmt->bind_param("issss", 
            $data['patient'],
            $appointment_date,
            $data['doctor'],
            $data['type'],
            $data['notes']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Appointment created successfully']);
        } else {
            throw new Exception("Failed to create appointment");
        }
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create appointment: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 