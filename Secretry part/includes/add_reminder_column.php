<?php
require_once '../config/db_connect.php';

// Add reminder_sent column if it doesn't exist
$alter_appointments = "ALTER TABLE `appointments` 
                      ADD COLUMN IF NOT EXISTS `reminder_sent` tinyint(1) NOT NULL DEFAULT 0";

if (mysqli_query($conn, $alter_appointments)) {
    echo "Successfully added reminder_sent column to appointments table\n";
} else {
    echo "Error adding reminder_sent column: " . mysqli_error($conn) . "\n";
}
?> 