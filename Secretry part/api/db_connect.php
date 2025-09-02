<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'brainsense';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => "Connection failed: " . mysqli_connect_error()
    ]));
}

// Set charset to utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die(json_encode([
        'success' => false,
        'message' => "Error setting charset: " . mysqli_error($conn)
    ]));
}

// Set timezone
date_default_timezone_set('UTC');

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

mysqli_query($conn, $create_messages);

// Create doctors table if it doesn't exist
$create_doctors = "CREATE TABLE IF NOT EXISTS `doctors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `specialization` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

mysqli_query($conn, $create_doctors);

// Create patients table if it doesn't exist
$create_patients = "CREATE TABLE IF NOT EXISTS `patients` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `full_name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `date_of_birth` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

mysqli_query($conn, $create_patients);
?> 