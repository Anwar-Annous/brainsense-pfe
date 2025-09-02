<?php
require_once '../config/db_connect.php';

// First, backup existing messages
$backup_query = "CREATE TABLE IF NOT EXISTS messages_backup LIKE messages";
mysqli_query($conn, $backup_query);
$backup_data = "INSERT INTO messages_backup SELECT * FROM messages";
mysqli_query($conn, $backup_data);

// Drop existing foreign key constraints
$drop_constraints = "ALTER TABLE messages 
    DROP FOREIGN KEY IF EXISTS messages_ibfk_1,
    DROP FOREIGN KEY IF EXISTS messages_ibfk_2";
mysqli_query($conn, $drop_constraints);

// Modify the messages table
$alter_messages = "ALTER TABLE messages 
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    MODIFY `subject` varchar(255) NOT NULL,
    MODIFY `message` text NOT NULL,
    MODIFY `sender_id` int(11) NOT NULL,
    MODIFY `sender_type` enum('patient','doctor','secretary') NOT NULL,
    MODIFY `recipient_id` int(11) NOT NULL,
    MODIFY `recipient_type` enum('patient','doctor','secretary') NOT NULL,
    MODIFY `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
    MODIFY `status` enum('unread','read') NOT NULL DEFAULT 'unread',
    MODIFY `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    MODIFY `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    DROP INDEX IF EXISTS sender_id,
    DROP INDEX IF EXISTS recipient_id,
    ADD INDEX `idx_sender` (`sender_id`, `sender_type`),
    ADD INDEX `idx_recipient` (`recipient_id`, `recipient_type`)";

if (mysqli_query($conn, $alter_messages)) {
    echo "Messages table updated successfully\n";
} else {
    echo "Error updating messages table: " . mysqli_error($conn) . "\n";
}

// Create a view for easier message access
$create_view = "CREATE OR REPLACE VIEW message_details AS
    SELECT 
        m.*,
        CASE 
            WHEN m.sender_type = 'patient' THEN p.full_name
            WHEN m.sender_type = 'doctor' THEN d.name
            WHEN m.sender_type = 'secretary' THEN 'Secretary'
        END as sender_name,
        CASE 
            WHEN m.recipient_type = 'patient' THEN p2.full_name
            WHEN m.recipient_type = 'doctor' THEN d2.name
            WHEN m.recipient_type = 'secretary' THEN 'Secretary'
        END as recipient_name
    FROM messages m
    LEFT JOIN patients p ON m.sender_type = 'patient' AND m.sender_id = p.id
    LEFT JOIN doctors d ON m.sender_type = 'doctor' AND m.sender_id = d.id
    LEFT JOIN patients p2 ON m.recipient_type = 'patient' AND m.recipient_id = p2.id
    LEFT JOIN doctors d2 ON m.recipient_type = 'doctor' AND m.recipient_id = d2.id";

if (mysqli_query($conn, $create_view)) {
    echo "Message details view created successfully\n";
} else {
    echo "Error creating message details view: " . mysqli_error($conn) . "\n";
} 