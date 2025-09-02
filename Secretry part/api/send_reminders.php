<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    // First, ensure the reminder_sent column exists
    $alter_query = "ALTER TABLE `appointments` 
                    ADD COLUMN IF NOT EXISTS `reminder_sent` tinyint(1) NOT NULL DEFAULT 0";
    mysqli_query($conn, $alter_query);

    // Get the next upcoming appointment that needs a reminder
    $query = "SELECT a.*, p.email, p.full_name, p.phone
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE a.appointment_date > NOW() 
              AND a.status = 'Scheduled'
              AND (a.reminder_sent = 0 OR a.reminder_sent IS NULL)
              ORDER BY a.appointment_date ASC
              LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Error fetching appointment: " . mysqli_error($conn));
    }

    $appointment = mysqli_fetch_assoc($result);
    
    if (!$appointment) {
        echo json_encode([
            'success' => true,
            'message' => "No upcoming appointments need reminders at this time"
        ]);
        exit;
    }

    // Prepare reminder message
    $appointment_date = date('F j, Y', strtotime($appointment['appointment_date']));
    $appointment_time = date('h:i A', strtotime($appointment['appointment_date']));
    
    $subject = "Appointment Reminder: " . $appointment_date;
    $message = "Dear " . $appointment['full_name'] . ",\n\n";
    $message .= "This is a reminder for your upcoming appointment:\n\n";
    $message .= "Date: " . $appointment_date . "\n";
    $message .= "Time: " . $appointment_time . "\n";
    $message .= "Type: " . $appointment['type'] . "\n";
    $message .= "Location: " . $appointment['location'] . "\n\n";
    $message .= "Please arrive 10 minutes before your scheduled time.\n\n";
    $message .= "Best regards,\nBrainSense Medical Center";

    // Set up email headers
    $headers = "From: BrainSense <noreply@brainsense.com>\r\n";
    $headers .= "Reply-To: appointments@brainsense.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email reminder
    if (mail($appointment['email'], $subject, $message, $headers)) {
        // Update reminder_sent status
        $update_query = "UPDATE appointments SET reminder_sent = 1 WHERE id = " . $appointment['id'];
        mysqli_query($conn, $update_query);

        echo json_encode([
            'success' => true,
            'message' => "Reminder sent successfully to " . $appointment['full_name'] . " for appointment on " . $appointment_date
        ]);
    } else {
        throw new Exception("Failed to send reminder to " . $appointment['email']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 