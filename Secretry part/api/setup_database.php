<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Create tables if they don't exist
    check_database_tables();

    // Check if we already have data
    $check_patients = "SELECT COUNT(*) as count FROM patients";
    $result = mysqli_query($conn, $check_patients);
    $patient_count = mysqli_fetch_assoc($result)['count'];

    if ($patient_count == 0) {
        // Add sample doctors
        $doctors = [
            [
                'name' => 'Dr. Amina Aboulmira',
                'specialization' => 'Neurology'
            ],
            [
                'name' => 'Dr. Youssef Benali',
                'specialization' => 'Psychiatry'
            ]
        ];

        foreach ($doctors as $doctor) {
            $query = "INSERT INTO doctors (name, specialization) 
                      VALUES ('" . mysqli_real_escape_string($conn, $doctor['name']) . "', 
                              '" . mysqli_real_escape_string($conn, $doctor['specialization']) . "')";
            mysqli_query($conn, $query);
        }

        // Add sample patients
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

        foreach ($patients as $patient) {
            $query = "INSERT INTO patients (full_name, phone_number, email) 
                      VALUES ('" . mysqli_real_escape_string($conn, $patient['full_name']) . "', 
                              '" . mysqli_real_escape_string($conn, $patient['phone_number']) . "', 
                              '" . mysqli_real_escape_string($conn, $patient['email']) . "')";
            mysqli_query($conn, $query);
        }

        // Add sample appointments
        $appointments = [
            [
                'patient_id' => 1,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'type' => 'Checkup',
                'status' => 'Confirmed'
            ],
            [
                'patient_id' => 2,
                'doctor_id' => 1,
                'appointment_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
                'type' => 'Follow-up',
                'status' => 'Confirmed'
            ],
            [
                'patient_id' => 3,
                'doctor_id' => 2,
                'appointment_date' => date('Y-m-d H:i:s', strtotime('+3 hours')),
                'type' => 'Consultation',
                'status' => 'Confirmed'
            ]
        ];

        foreach ($appointments as $appointment) {
            $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, type, status) 
                      VALUES (" . (int)$appointment['patient_id'] . ", 
                              " . (int)$appointment['doctor_id'] . ",
                              '" . mysqli_real_escape_string($conn, $appointment['appointment_date']) . "', 
                              '" . mysqli_real_escape_string($conn, $appointment['type']) . "',
                              '" . mysqli_real_escape_string($conn, $appointment['status']) . "')";
            mysqli_query($conn, $query);
        }

        // Add sample activities
        $activities = [
            [
                'patient_id' => 1,
                'status' => 'Scheduled'
            ],
            [
                'patient_id' => 2,
                'status' => 'Confirmed'
            ],
            [
                'patient_id' => 3,
                'status' => 'Completed'
            ]
        ];

        foreach ($activities as $activity) {
            $query = "INSERT INTO activities (patient_id, status) 
                      VALUES (" . (int)$activity['patient_id'] . ", 
                              '" . mysqli_real_escape_string($conn, $activity['status']) . "')";
            mysqli_query($conn, $query);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Database setup completed with sample data'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Database already contains data'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error setting up database: ' . $e->getMessage()
    ]);
}
?> 