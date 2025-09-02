<?php
require_once '../config/db_connect.php';

// Create patients table
$create_patients = "CREATE TABLE IF NOT EXISTS `patients` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `full_name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `date_of_birth` date NOT NULL,
    `gender` enum('male','female','other') NOT NULL,
    `address` text,
    `medical_history` text,
    `emergency_contact` varchar(255),
    `emergency_phone` varchar(20),
    `blood_type` varchar(5),
    `allergies` text,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `full_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_patients)) {
    echo "Patients table created successfully\n";
} else {
    echo "Error creating patients table: " . mysqli_error($conn) . "\n";
}

// Create doctors table
$create_doctors = "CREATE TABLE IF NOT EXISTS `doctors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `specialization` varchar(255) NOT NULL,
    `qualification` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_doctors)) {
    echo "Doctors table created successfully\n";
} else {
    echo "Error creating doctors table: " . mysqli_error($conn) . "\n";
}

// Create appointments table
$create_appointments = "CREATE TABLE IF NOT EXISTS `appointments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `patient_id` int(11) NOT NULL,
    `doctor_id` int(11) NOT NULL,
    `appointment_date` datetime NOT NULL,
    `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    `type` varchar(50) NOT NULL,
    `notes` text,
    `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `patient_id` (`patient_id`),
    KEY `doctor_id` (`doctor_id`),
    CONSTRAINT `fk_appointment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appointment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_appointments)) {
    echo "Appointments table created successfully\n";
    
    // Add reminder_sent column if it doesn't exist
    $alter_appointments = "ALTER TABLE `appointments` 
                          ADD COLUMN IF NOT EXISTS `reminder_sent` tinyint(1) NOT NULL DEFAULT 0";
    
    if (mysqli_query($conn, $alter_appointments)) {
        echo "Added reminder_sent column to appointments table\n";
    } else {
        echo "Error adding reminder_sent column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Error creating appointments table: " . mysqli_error($conn) . "\n";
}

// Create messages table
$create_messages = "CREATE TABLE IF NOT EXISTS `messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `sender_id` int(11) NOT NULL,
    `sender_type` enum('patient','doctor','secretary') NOT NULL,
    `recipient_id` int(11) NOT NULL,
    `recipient_type` enum('patient','doctor','secretary') NOT NULL,
    `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
    `status` enum('unread','read') NOT NULL DEFAULT 'unread',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `sender_id` (`sender_id`),
    KEY `recipient_id` (`recipient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_messages)) {
    echo "Messages table created successfully\n";
} else {
    echo "Error creating messages table: " . mysqli_error($conn) . "\n";
}

// Create quick_notes table
$create_quick_notes = "CREATE TABLE IF NOT EXISTS `quick_notes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content` text NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_quick_notes)) {
    echo "Quick notes table created successfully\n";
} else {
    echo "Error creating quick notes table: " . mysqli_error($conn) . "\n";
} 