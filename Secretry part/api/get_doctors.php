<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    $query = "SELECT id, name FROM doctors ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    $doctors = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = array(
            'id' => $row['id'],
            'name' => $row['name']
        );
    }
    
    echo json_encode($doctors);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load doctors']);
}
?> 