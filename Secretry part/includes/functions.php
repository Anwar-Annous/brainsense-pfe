<?php
require_once 'db_connect.php';

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    
    $stats = [];
    
    // Get new patients this week
    $query = "SELECT COUNT(*) as count FROM patients 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = mysqli_query($conn, $query);
    $stats['new_patients'] = mysqli_fetch_assoc($result)['count'];
    
    // Get today's appointments
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE DATE(appointment_date) = CURDATE()";
    $result = mysqli_query($conn, $query);
    $stats['today_appointments'] = mysqli_fetch_assoc($result)['count'];
    
    // Get completed appointments today
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE DATE(appointment_date) = CURDATE() 
              AND status = 'Completed'";
    $result = mysqli_query($conn, $query);
    $stats['completed_appointments'] = mysqli_fetch_assoc($result)['count'];
    
    return $stats;
}

// Get upcoming appointments
function getUpcomingAppointments($limit = 2) {
    global $conn;
    
    $limit = (int)$limit;
    $query = "SELECT a.*, p.full_name as patient_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE a.appointment_date >= NOW() 
              AND a.status != 'Cancelled'
              ORDER BY a.appointment_date ASC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    return $appointments;
}

// Get all upcoming appointments
function getAllUpcomingAppointments() {
    global $conn;
    
    $query = "SELECT a.*, p.full_name as patient_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE a.appointment_date >= NOW() 
              AND a.status != 'Cancelled'
              ORDER BY a.appointment_date ASC";
    $result = mysqli_query($conn, $query);
    
    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    return $appointments;
}

// Get recent activities
function getRecentActivities($limit = 5) {
    global $conn;
    
    $limit = (int)$limit;
    $query = "SELECT a.*, p.full_name as patient_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              ORDER BY a.created_at DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
    return $activities;
}

// Get patient queue
function getPatientQueue() {
    global $conn;
    
    $query = "SELECT a.*, p.full_name as patient_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE DATE(a.appointment_date) = CURDATE() 
              AND a.status = 'Scheduled'
              ORDER BY a.appointment_date ASC";
    $result = mysqli_query($conn, $query);
    
    $queue = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $queue[] = $row;
    }
    return $queue;
}

// Update appointment status
function updateAppointmentStatus($appointment_id, $status) {
    global $conn;
    
    $appointment_id = (int)$appointment_id;
    $status = mysqli_real_escape_string($conn, $status);
    
    $query = "UPDATE appointments SET status = '$status' WHERE id = $appointment_id";
    return mysqli_query($conn, $query);
}

// Get documents
function getDocuments() {
    global $conn;
    
    $query = "SELECT * FROM documents ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    $documents = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    return $documents;
}

// Get unread messages
function getUnreadMessages() {
    global $conn;
    
    $query = "SELECT * FROM messages WHERE status = 'unread' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    return $messages;
}

// Helper function to sanitize input
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Helper function to get patient information
function get_patient_info($patient_id) {
    global $conn;
    $patient_id = sanitize_input($patient_id);
    
    $query = "SELECT * FROM patients WHERE id = '$patient_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Helper function to get appointment information
function get_appointment_info($appointment_id) {
    global $conn;
    $appointment_id = sanitize_input($appointment_id);
    
    $query = "SELECT * FROM appointments WHERE id = '$appointment_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Helper function to format date
function format_date($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

// Helper function to check if database tables exist
function check_database_tables() {
    global $conn;
    
    // Create doctors table
    $sql = "CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        specialization VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);

    // Create patients table
    $sql = "CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        email VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $sql);

    // Create appointments table
    $sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_date DATETIME NOT NULL,
        type VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id),
        FOREIGN KEY (doctor_id) REFERENCES doctors(id)
    )";
    mysqli_query($conn, $sql);

    // Create activities table
    $sql = "CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id)
    )";
    mysqli_query($conn, $sql);

    // Create reminders_log table
    $sql = "CREATE TABLE IF NOT EXISTS reminders_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        appointment_id INT NOT NULL,
        patient_id INT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (appointment_id) REFERENCES appointments(id),
        FOREIGN KEY (patient_id) REFERENCES patients(id)
    )";
    mysqli_query($conn, $sql);
}

// Call this function when the application starts
check_database_tables();

// Get today's schedule
function getTodaySchedule() {
    global $conn;
    
    $query = "SELECT a.*, p.full_name as patient_name, d.name as doctor_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              LEFT JOIN doctors d ON a.doctor_id = d.id 
              WHERE DATE(a.appointment_date) = CURDATE() 
              ORDER BY a.appointment_date ASC";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Error fetching today's schedule: " . mysqli_error($conn));
    }
    
    $schedule = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $schedule[] = $row;
    }
    return $schedule;
}
?> 