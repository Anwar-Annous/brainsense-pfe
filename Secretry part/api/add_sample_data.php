<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Sample patients data
$patients = [
    [
        'full_name' => 'John Doe',
        'phone_number' => '0612345678',
        'email' => 'john.doe@example.com'
    ],
    [
        'full_name' => 'Jane Smith',
        'phone_number' => '0623456789',
        'email' => 'jane.smith@example.com'
    ],
    [
        'full_name' => 'Mohammed Ali',
        'phone_number' => '0634567890',
        'email' => 'mohammed.ali@example.com'
    ]
];

// Sample appointments data
$appointments = [
    [
        'patient_id' => 1,
        'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'status' => 'Confirmed'
    ],
    [
        'patient_id' => 2,
        'appointment_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        'status' => 'Confirmed'
    ],
    [
        'patient_id' => 3,
        'appointment_date' => date('Y-m-d H:i:s', strtotime('+3 hours')),
        'status' => 'Confirmed'
    ]
];

try {
    // Insert patients
    foreach ($patients as $patient) {
        $query = "INSERT INTO patients (full_name, phone_number, email) 
                  VALUES ('" . mysqli_real_escape_string($conn, $patient['full_name']) . "', 
                          '" . mysqli_real_escape_string($conn, $patient['phone_number']) . "', 
                          '" . mysqli_real_escape_string($conn, $patient['email']) . "')";
        mysqli_query($conn, $query);
    }

    // Insert appointments
    foreach ($appointments as $appointment) {
        $query = "INSERT INTO appointments (patient_id, appointment_date, status) 
                  VALUES (" . (int)$appointment['patient_id'] . ", 
                          '" . mysqli_real_escape_string($conn, $appointment['appointment_date']) . "', 
                          '" . mysqli_real_escape_string($conn, $appointment['status']) . "')";
        mysqli_query($conn, $query);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sample data added successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding sample data: ' . $e->getMessage()
    ]);
} 