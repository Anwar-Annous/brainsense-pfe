<?php
include '../connexion.php';

// Fetch pending notifications with appointment details
$sql = "SELECT n.*, a.*, d.name as doctor_name, d.specialization 
        FROM notifications n 
        JOIN appointments a ON n.appointment_id = a.id 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE n.status = 'pending' 
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notification-card {
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .notification-card:hover {
            transform: translateX(5px);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4">Appointment Notifications</h2>
        
        <div class="toast-container"></div>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card notification-card">
                            <div class="card-body">
                                <h5 class="card-title">New Appointment Request</h5>
                                <p class="card-text">
                                    <strong>Doctor:</strong> <?php echo htmlspecialchars($row['doctor_name']); ?><br>
                                    <strong>Specialty:</strong> <?php echo htmlspecialchars($row['specialization']); ?><br>
                                    <strong>Date:</strong> <?php echo htmlspecialchars($row['appointment_date']); ?><br>
                                    <strong>Time:</strong> <?php echo htmlspecialchars($row['appointment_time']); ?><br>
                                    <strong>Type:</strong> <?php echo htmlspecialchars($row['appointment_type']); ?><br>
                                    <strong>Purpose:</strong> <?php echo htmlspecialchars($row['purpose']); ?>
                                </p>
                                <div class="action-buttons">
                                    <button class="btn btn-success btn-sm" onclick="handleAppointment(<?php echo $row['id']; ?>, <?php echo $row['appointment_id']; ?>, 'approved')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="handleAppointment(<?php echo $row['id']; ?>, <?php echo $row['appointment_id']; ?>, 'declined')">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No pending notifications at this time.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        function handleAppointment(notificationId, appointmentId, action) {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            formData.append('appointment_id', appointmentId);
            formData.append('action', action);

            fetch('handle_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    showToast(`Appointment ${action === 'approved' ? 'approved' : 'declined'} successfully`);
                    // Remove the notification card from the UI
                    const card = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (card) {
                        card.remove();
                    }
                    // Reload the page after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error processing request', 'danger');
                }
            })
            .catch(error => {
                showToast('Error processing request', 'danger');
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html> 