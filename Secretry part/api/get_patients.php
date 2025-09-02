<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    $query = "SELECT id, full_name FROM patients ORDER BY full_name";
    $result = mysqli_query($conn, $query);
    
    $patients = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = array(
            'id' => $row['id'],
            'full_name' => $row['full_name']
        );
    }
    
    echo json_encode($patients);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load patients']);
}
?> 