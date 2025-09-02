<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Check if database connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // First, let's check if we have any patients
    $check_patients = "SELECT COUNT(*) as count FROM patients";
    $result = mysqli_query($conn, $check_patients);
    $patient_count = mysqli_fetch_assoc($result)['count'];

    if ($patient_count == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No patients found in the database',
            'debug_info' => [
                'patient_count' => 0
            ]
        ]);
        exit;
    }

    // Get the next patient with an upcoming appointment
    $query = "SELECT p.id, p.full_name, p.phone_number, a.appointment_date 
              FROM patients p 
              JOIN appointments a ON p.id = a.patient_id 
              WHERE a.status = 'Confirmed' 
              AND a.appointment_date >= NOW() 
              ORDER BY a.appointment_date ASC 
              LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    
    // Check if query execution was successful
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    if (mysqli_num_rows($result) > 0) {
        $patient = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'patient' => $patient
        ]);
    } else {
        // Let's check why we might not have any appointments
        $check_appointments = "SELECT COUNT(*) as count FROM appointments";
        $result = mysqli_query($conn, $check_appointments);
        $appointment_count = mysqli_fetch_assoc($result)['count'];

        echo json_encode([
            'success' => false,
            'message' => 'No upcoming appointments found',
            'debug_info' => [
                'patient_count' => $patient_count,
                'appointment_count' => $appointment_count,
                'query' => $query
            ]
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_next_patient.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching patient information: ' . $e->getMessage(),
        'debug_info' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} 