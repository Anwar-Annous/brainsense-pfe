<?php
require_once '../config/db_connect.php';

// First, backup existing data
$backup_query = "CREATE TABLE IF NOT EXISTS patients_backup LIKE patients";
mysqli_query($conn, $backup_query);
$backup_data = "INSERT INTO patients_backup SELECT * FROM patients";
mysqli_query($conn, $backup_data);

// Modify the patients table
$alter_patients = "ALTER TABLE `patients` 
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    MODIFY `full_name` varchar(255) NOT NULL,
    MODIFY `email` varchar(255) NOT NULL,
    MODIFY `phone` varchar(20) NOT NULL,
    MODIFY `date_of_birth` date NOT NULL,
    MODIFY `gender` enum('male','female','other') NOT NULL,
    MODIFY `national_id` varchar(50) DEFAULT NULL,
    MODIFY `blood_type` varchar(5) DEFAULT NULL,
    MODIFY `medical_conditions` text DEFAULT NULL,
    MODIFY `allergies` text DEFAULT NULL,
    MODIFY `address` text DEFAULT NULL,
    MODIFY `password` varchar(255) NOT NULL,
    MODIFY `profile_photo` varchar(255) DEFAULT NULL,
    ADD COLUMN `emergency_contact` varchar(255) DEFAULT NULL AFTER `address`,
    ADD COLUMN `emergency_phone` varchar(20) DEFAULT NULL AFTER `emergency_contact`,
    ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`,
    ADD UNIQUE KEY `email` (`email`),
    ADD KEY `full_name` (`full_name`)";

if (mysqli_query($conn, $alter_patients)) {
    echo "Patients table updated successfully\n";
} else {
    echo "Error updating patients table: " . mysqli_error($conn) . "\n";
}

// Create messages table if it doesn't exist
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

// Create doctors table if it doesn't exist
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

// Create quick_notes table if it doesn't exist
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