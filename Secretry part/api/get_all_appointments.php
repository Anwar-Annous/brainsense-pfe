<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $appointments = getAllUpcomingAppointments();
    echo json_encode($appointments);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch appointments: ' . $e->getMessage()]);
}
?> 