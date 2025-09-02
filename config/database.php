<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    // First, connect without database name to create it if it doesn't exist
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS brainsense");
    error_log("Database check/creation completed");
    
    // Now connect to the specific database
    $dbname = 'brainsense';
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable foreign key checks temporarily
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Check if users table exists and modify it instead of dropping
    $checkTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->rowCount() > 0) {
        // Modify existing table if needed
        $conn->exec("ALTER TABLE users 
                    MODIFY COLUMN username VARCHAR(50) NOT NULL,
                    MODIFY COLUMN password VARCHAR(255) NOT NULL,
                    MODIFY COLUMN full_name VARCHAR(100) NOT NULL,
                    MODIFY COLUMN Profile VARCHAR(255) NOT NULL,
                    MODIFY COLUMN email VARCHAR(100) NOT NULL,
                    MODIFY COLUMN role ENUM('admin', 'secretary', 'doctor', 'patient') NOT NULL,
                    MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        error_log("Modified existing users table");
    } else {
        // Create new table
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            Profile VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role ENUM('admin', 'secretary', 'doctor', 'patient') NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )";
        $conn->exec($sql);
        error_log("Created new users table");
    }

    // Check if patients table exists
    $checkPatientsTable = $conn->query("SHOW TABLES LIKE 'patients'");
    if ($checkPatientsTable->rowCount() == 0) {
        // Create patients table
        $sql = "CREATE TABLE patients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            date_of_birth DATE NOT NULL,
            gender ENUM('male', 'female', 'other') NOT NULL,
            national_id VARCHAR(50) DEFAULT NULL,
            blood_type VARCHAR(5) DEFAULT NULL,
            medical_conditions TEXT DEFAULT NULL,
            allergies TEXT DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT DEFAULT NULL,
            emergency_contact VARCHAR(255) DEFAULT NULL,
            emergency_phone VARCHAR(20) DEFAULT NULL,
            created_by INT DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            profile_photo VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_password_change DATE DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        $conn->exec($sql);
        error_log("Created new patients table");
    }
    
    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
} catch(PDOException $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    error_log("Connection details - Host: $host, Database: $dbname, Username: $username");
    
    // Display a user-friendly error message
    die("Connection failed: Please check your database configuration. Error: " . $e->getMessage());
}
?> 