<?php
include '../connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_id = $_POST['notification_id'];
    $action = $_POST['action']; // 'approve' or 'decline'
    $appointment_id = $_POST['appointment_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update notification status
        $notification_sql = "UPDATE notifications SET status = ? WHERE id = ?";
        $notification_stmt = $conn->prepare($notification_sql);
        $notification_stmt->bind_param("si", $action, $notification_id);
        $notification_stmt->execute();
        
        // Update appointment status
        $appointment_status = ($action === 'approved') ? 'Scheduled' : 'Declined';
        $appointment_sql = "UPDATE appointments SET status = ? WHERE id = ?";
        $appointment_stmt = $conn->prepare($appointment_sql);
        $appointment_stmt->bind_param("si", $appointment_status, $appointment_id);
        $appointment_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "error";
    }
    
    $notification_stmt->close();
    $appointment_stmt->close();
    $conn->close();
}
?> 