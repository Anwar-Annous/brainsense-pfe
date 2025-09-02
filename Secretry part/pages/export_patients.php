<?php
require_once 'includes/db_connect.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="patient_records_' . date('Y-m-d') . '.csv"');

// Create file pointer for output
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'ID',
    'Full Name',
    'Date of Birth',
    'Gender',
    'National ID',
    'Blood Type',
    'Email',
    'Phone',
    'Address',
    'Medical Conditions',
    'Allergies',
    'Registration Date'
]);

// Get all patients
$query = "SELECT * FROM patients ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Write data rows
while ($patient = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $patient['id'],
        $patient['full_name'],
        $patient['date_of_birth'],
        $patient['gender'],
        $patient['national_id'],
        $patient['blood_type'],
        $patient['email'],
        $patient['phone'],
        $patient['address'],
        $patient['medical_conditions'] ?? 'None',
        $patient['allergies'] ?? 'None',
        $patient['created_at']
    ]);
}

// Close the file pointer
fclose($output);
?> 