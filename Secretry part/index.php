<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];


// Query to get secretary ID based on user ID
$secretary_query = "SELECT id FROM secretaries WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $secretary_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$secretary_row = mysqli_fetch_assoc($result);

// Check if secretary exists
if (!$secretary_row) {
    // Handle case where secretary is not found
    $secretary_name = 'Unknown Secretary';
    $secretary_photo = 'assets/images/default-avatar.png';
} else {
    $secretary_id = $secretary_row['id'];
    // Query to get secretary name and profile picture
    $secretary_details_query = "SELECT full_name, profile_photo FROM secretaries WHERE id = ?";
    $stmt = mysqli_prepare($conn, $secretary_details_query);
    mysqli_stmt_bind_param($stmt, "i", $secretary_id);
    mysqli_stmt_execute($stmt);
    $secretary_details = mysqli_stmt_get_result($stmt);
    $secretary = mysqli_fetch_assoc($secretary_details);

    // Store secretary details in variables with error handling
    $secretary_name = $secretary ? $secretary['full_name'] : 'Unknown Secretary';
    $secretary_photo = ($secretary && $secretary['profile_photo']) ? $secretary['profile_photo'] : 'assets/images/default-avatar.png';
}


// Get dashboard statistics
$stats = getDashboardStats();

// Get upcoming appointments
$upcomingAppointments = getUpcomingAppointments();

// Get recent activities
$recentActivities = getRecentActivities();

// Get patient queue
$patientQueue = getPatientQueue();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">

	<title>Dashboard</title>
</head>
<body>


<!-- SIDEBAR -->
<section id="sidebar">
    <a href="#" class="brand">
        <img src="assets/images/Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
        <span class="Logo">BrainSense</span>
    </a>
    <ul class="side-menu top">
        <li class="active">
            <a href="#">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>

        <li>
            <a href="patient_records.php">
                <i class='bx bxs-user'></i>
                <span class="text">Patient Records</span>
            </a>
        </li>
        <li>
            <a href="document_center.php">
                <i class='bx bxs-file'></i>
                <span class="text">Document Center</span>
            </a>
        </li>
        <li>
            <a href="communication_hub.php">
                <i class='bx bxs-chat'></i>
                <span class="text">Communication Hub</span>
            </a>
        </li>
        <li>
            <a href="resources.php">
                <i class='bx bxs-clinic'></i>
                <span class="text"> Resources</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">

        <li>
            <a href="../logout.php" class="logout">
                <i class='bx bxs-log-out-circle'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>
<!-- SIDEBAR -->



	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
			<a href="communication_hub.php" class="notification">
                <i class='bx bxs-bell'></i>
            </a>
            <a href="#" class="profile">
                <img src="<?php echo isset($secretary_photo) ? htmlspecialchars($secretary_photo) : 'assets/images/default-avatar.png'; ?>" alt="Profile Picture">
                <span class="profile-name" title="<?php echo isset($secretary_name) ? htmlspecialchars($secretary_name) : 'Unknown Secretary'; ?>">
                    <?php echo isset($secretary_name) ? htmlspecialchars($secretary_name) : 'Unknown Secretary'; ?>
                </span>
            </a>
        </nav>
		<!-- NAVBAR -->

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Secrétaire Dashboard</h1>
            <ul class="breadcrumb">
                <li><a href="#">BrainSense</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Appointment Management</a></li>
            </ul>
        </div>
        <div class="secretary-actions">
            <button class="btn-primary" onclick="openNewAppointmentModal()">
                <i class='bx bx-plus'></i> New Appointment
            </button>
            <button class="btn-secondary" onclick="sendReminders()">
                <i class='bx bx-bell'></i> Send Reminders
            </button>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class='bx bx-user-plus'></i>
            </div>
            <div class="stat-info">
                <h4>New Patients</h4>
                <h3><?php echo $stats['new_patients']; ?></h3>
                <p>This Week</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class='bx bx-calendar-check'></i>
            </div>
            <div class="stat-info">
                <h4>Appointments</h4>
                <h3><?php echo $stats['today_appointments']; ?></h3>
                <p>Today</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class='bx bx-time'></i>
            </div>
            <div class="stat-info">
                <h4>Waiting Time</h4>
                <h3>15 min</h3>
                <p>Average</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class='bx bx-check-circle'></i>
            </div>
            <div class="stat-info">
                <h4>Completed</h4>
                <h3><?php echo $stats['completed_appointments']; ?></h3>
                <p>Today</p>
            </div>
        </div>
    </div>
<!-- 
    <div class="quick-actions-panel">
        <h3><i class='bx bx-bolt'></i> Quick Actions</h3>
        <div class="actions-grid">
            <button class="action-btn">
                <i class='bx bx-phone'></i>
                <span>Call Patient</span>
            </button>
            <button class="action-btn">
                <i class='bx bx-message-square-dots'></i>
                <span>Send SMS</span>
            </button>
            <button class="action-btn">
                <i class='bx bx-envelope'></i>
                <span>Email Patient</span>
            </button>
            <button class="action-btn">
                <i class='bx bx-file'></i>
                <span>Print Forms</span>
            </button>
        </div>
    </div>
 -->
    <!-- Upcoming Appointments -->
    <div class="upcoming-appointments">
        <div class="section-header">
            <h3><i class='bx bx-calendar-event'></i> Upcoming Appointments</h3>
            <button class="btn-secondary" onclick="openAllAppointmentsModal()">View All</button>
        </div>
        <div class="appointments-list">
            <?php foreach ($upcomingAppointments as $appointment): ?>
            <div class="appointment-item">
                <div class="appointment-time">
                    <span class="time"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></span>
                    <span class="date"><?php echo date('M d', strtotime($appointment['appointment_date'])); ?></span>
                </div>
                <div class="appointment-details">
                    <h4><?php echo htmlspecialchars($appointment['patient_name']); ?></h4>
                    <p><?php echo htmlspecialchars($appointment['type']); ?></p>
                    <span class="doctor"><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                </div>
                <div class="appointment-status <?php echo strtolower($appointment['status']); ?>">
                    <i class='bx bx-check-circle'></i>
                    <span><?php echo $appointment['status']; ?></span>
                </div>
                <?php if ($appointment['status'] !== 'Completed'): ?>
                    <button class="btn-secondary mark-completed-btn" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'Completed')">
                        <i class='bx bx-check'></i> Mark as Completed
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- All Appointments Modal -->
    <div id="allAppointmentsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAllAppointmentsModal()">&times;</span>
            <h2>All Upcoming Appointments</h2>
            <div class="appointments-list" id="allAppointmentsList">
                <!-- Will be populated dynamically -->
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="recent-activities">
        <div class="section-header">
            <h3><i class='bx bx-history'></i> Recent Activities</h3>
            <button class="btn-secondary" onclick="openAllActivitiesModal()">View All</button>
        </div>
        <div class="activity-list">
            <?php 
            $count = 0;
            foreach ($recentActivities as $activity): 
                if ($count >= 2) break;
                $count++;
            ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class='bx bx-calendar-plus'></i>
                </div>
                <div class="activity-details">
                    <p>Appointment <?php echo $activity['status']; ?> for <strong><?php echo htmlspecialchars($activity['patient_name']); ?></strong></p>
                    <span class="activity-time"><?php echo date('M d, Y h:i A', strtotime($activity['created_at'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- All Activities Modal -->
    <div id="allActivitiesModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAllActivitiesModal()">&times;</span>
            <h2>All Recent Activities</h2>
            <div class="activity-list" id="allActivitiesList">
                <!-- Will be populated dynamically -->
            </div>
        </div>
    </div>

    <!-- Management Cards -->
    <div class="management-cards">
        <!-- Patient Queue Card -->
        <div class="card queue-card">
            <div class="card-header">
                <i class='bx bx-list-ul'></i>
                <h3>Patient Queue</h3>
            </div>
            <div class="card-body">
                <?php foreach ($patientQueue as $patient): ?>
                <div class="queue-item">
                    <span class="patient-name"><?php echo htmlspecialchars($patient['patient_name']); ?></span>
                    <span class="appointment-time"><?php echo date('h:i A', strtotime($patient['appointment_date'])); ?></span>
                    <span class="status waiting">
                        <i class='bx bx-time-five'></i> Waiting
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-actions">
                <button class="btn-secondary" onclick="printQueue()">
                    <i class='bx bx-printer'></i> Print List
                </button>
                <button class="btn-primary" onclick="callNextPatient()">
                    <i class='bx bx-notification'></i> Call Next
                </button>
            </div>
        </div>

        <!-- Document Management -->
        <div class="card documents-card">
            <div class="card-header">
                <i class='bx bx-folder'></i>
                <h3>Document Center</h3>
            </div>
            <div class="card-body">
                <div class="document-item">
                    <i class='bx bx-file'></i>
                    <span class="doc-name">Consent Form - Leila Benali</span>
                    <div class="doc-actions">
                        <button class="btn-icon"><i class='bx bx-download'></i></button>
                        <button class="btn-icon"><i class='bx bx-envelope'></i></button>
                    </div>
                </div>
                <!-- More document items... -->
            </div>
            <div class="card-footer">
                <button class="btn-primary"><i class='bx bx-upload'></i> Upload New Document</button>
            </div>
        </div>

        <!-- Communication Hub -->
        <div class="card communication-card">
            <div class="card-header">
                <i class='bx bx-chat'></i>
                <h3>Patient Communications</h3>
            </div>
            <div class="card-body">
                <div class="message-item unread">
                    <div class="message-preview">
                        <h5>Amal Ziri - Insurance Verification</h5>
                        <p>Please confirm coverage for MRI scan...</p>
                    </div>
                    <span class="message-time">2h ago</span>
                </div>
                <!-- More messages... -->
            </div>
            <div class="card-actions">
                <button class="btn-primary"><i class='bx bx-plus'></i> New Broadcast</button>
            </div>
        </div>
    </div>
</main>
<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	
	<!-- New Appointment Modal -->
	<div id="newAppointmentModal" class="modal">
		<div class="modal-content">
			<span class="close" onclick="closeNewAppointmentModal()">&times;</span>
			<h2>New Appointment</h2>
			<form id="newAppointmentForm">
				<div class="form-group">
					<label for="patient">Patient</label>
					<select id="patient" name="patient" required>
						<option value="">Select Patient</option>
						<!-- Will be populated dynamically -->
					</select>
				</div>
				<div class="form-group">
					<label for="doctor">Doctor</label>
					<select id="doctor" name="doctor" required>
						<option value="">Select Doctor</option>
						<!-- Will be populated dynamically -->
					</select>
				</div>
				<div class="form-group">
					<label for="date">Date</label>
					<input type="date" id="date" name="date" required>
				</div>
				<div class="form-group">
					<label for="time">Time</label>
					<input type="time" id="time" name="time" required>
				</div>
				<div class="form-group">
					<label for="type">Type</label>
					<select id="type" name="type" required>
						<option value="Checkup">Checkup</option>
						<option value="Follow-up">Follow-up</option>
						<option value="Consultation">Consultation</option>
					</select>
				</div>
				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea id="notes" name="notes"></textarea>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn-primary">Schedule</button>
					<button type="button" class="btn-secondary" onclick="closeNewAppointmentModal()">Cancel</button>
				</div>
			</form>
		</div>
	</div>
    <!-- Document Upload Modal -->
<div id="documentUploadModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDocumentUploadModal()">&times;</span>
        <h2>Upload Document</h2>
        <form id="documentUploadForm">
            <div class="form-group">
                <label for="title">Document Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="file">File</label>
                <input type="file" id="file" name="file" required>
            </div>
            <div class="form-group">
                <label>Share With</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="shared_with" value="both" checked>
                        <span>Both Patient and Doctor</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="shared_with" value="doctor">
                        <span>Doctor Only</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="shared_with" value="patient">
                        <span>Patient Only</span>
                    </label>
                </div>
            </div>
            <div class="form-group doctor-select" style="display: none;">
                <label for="doctorSelect">Select Doctor</label>
                <select id="doctorSelect" name="doctor_id">
                    <option value="">Select Doctor</option>
                </select>
            </div>
            <div class="form-group patient-select" style="display: none;">
                <label for="patientSelect">Select Patient</label>
                <select id="patientSelect" name="patient_id">
                    <option value="">Select Patient</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Upload</button>
                <button type="button" class="btn-secondary" onclick="closeDocumentUploadModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

	<script src="script.js"></script>
	 <script>
		
	</script>

    <script>
    // Add these functions to your existing script.js or inline script
    function openAllAppointmentsModal() {
        const modal = document.getElementById('allAppointmentsModal');
        modal.style.display = 'block';
        loadAllAppointments();
    }

    function closeAllAppointmentsModal() {
        const modal = document.getElementById('allAppointmentsModal');
        modal.style.display = 'none';
    }

    async function loadAllAppointments() {
        try {
            const response = await fetch('api/get_all_appointments.php');
            const appointments = await response.json();
            const container = document.getElementById('allAppointmentsList');
            
            container.innerHTML = appointments.map(appointment => `
                <div class="appointment-item">
                    <div class="appointment-time">
                        <span class="time">${new Date(appointment.appointment_date).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}</span>
                        <span class="date">${new Date(appointment.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</span>
                    </div>
                    <div class="appointment-details">
                        <h4>${appointment.patient_name}</h4>
                        <p>${appointment.type}</p>
                        <span class="doctor">${appointment.doctor_name}</span>
                    </div>
                    <div class="appointment-status ${appointment.status.toLowerCase()}">
                        <i class='bx bx-check-circle'></i>
                        <span>${appointment.status}</span>
                    </div>
                    ${appointment.status !== 'Completed' ? `<button class=\"btn-secondary mark-completed-btn\" onclick=\"updateStatus(${appointment.id}, 'Completed')\"><i class='bx bx-check'></i> Mark as Completed</button>` : ''}
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading appointments:', error);
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('allAppointmentsModal');
        if (event.target == modal) {
            closeAllAppointmentsModal();
        }
    }

    function openAllActivitiesModal() {
        const modal = document.getElementById('allActivitiesModal');
        modal.style.display = 'block';
        loadAllActivities();
    }

    function closeAllActivitiesModal() {
        const modal = document.getElementById('allActivitiesModal');
        modal.style.display = 'none';
    }

    async function loadAllActivities() {
        try {
            const response = await fetch('api/get_all_activities.php');
            const activities = await response.json();
            const container = document.getElementById('allActivitiesList');
            
            container.innerHTML = activities.map(activity => `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class='bx bx-calendar-plus'></i>
                    </div>
                    <div class="activity-details">
                        <p>Appointment ${activity.status} for <strong>${activity.patient_name}</strong></p>
                        <span class="activity-time">${new Date(activity.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}</span>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading activities:', error);
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('allActivitiesModal');
        if (event.target == modal) {
            closeAllActivitiesModal();
        }
    }

    function openNewAppointmentModal() {
        const modal = document.getElementById('newAppointmentModal');
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            loadAppointmentFormData();
        }
    }

    function closeNewAppointmentModal() {
        const modal = document.getElementById('newAppointmentModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function loadAppointmentFormData() {
        // Load patients
        fetch('api/get_recipients.php?type=patient')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const patientSelect = document.getElementById('patient');
                    if (patientSelect) {
                        patientSelect.innerHTML = '<option value="">Select a patient</option>' + 
                            data.recipients.map(patient => 
                                `<option value="${patient.id}">${patient.name}</option>`
                            ).join('');
                    }
                }
            })
            .catch(error => console.error('Error loading patients:', error));

        // Load doctors
        fetch('api/get_recipients.php?type=doctor')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const doctorSelect = document.getElementById('doctor');
                    if (doctorSelect) {
                        doctorSelect.innerHTML = '<option value="">Select a doctor</option>' + 
                            data.recipients.map(doctor => 
                                `<option value="${doctor.id}">${doctor.name}</option>`
                            ).join('');
                    }
                }
            })
            .catch(error => console.error('Error loading doctors:', error));
    }

    // Handle form submission
    document.getElementById('newAppointmentForm')?.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = {
            patient: document.getElementById('patient')?.value,
            doctor: document.getElementById('doctor')?.value,
            date: document.getElementById('date')?.value,
            time: document.getElementById('time')?.value,
            type: document.getElementById('type')?.value,
            notes: document.getElementById('notes')?.value
        };

        if (!formData.patient || !formData.doctor || !formData.date || !formData.time || !formData.type) {
            alert('Please fill in all required fields');
            return;
        }

        fetch('api/create_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                closeNewAppointmentModal();
                location.reload(); // Refresh the page to show new appointment
            } else {
                alert('Failed to schedule appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to schedule appointment. Please try again later.');
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('newAppointmentModal');
        if (event.target == modal) {
            closeNewAppointmentModal();
        }
    }

    // Send appointment reminders
    async function sendReminders() {
        try {
            const response = await fetch('api/send_reminders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Reminders sent successfully!');
            } else {
                alert('Failed to send reminders: ' + data.message);
            }
        } catch (error) {
            console.error('Error sending reminders:', error);
            alert('Failed to send reminders. Please try again later.');
        }
    }
    </script>

<style>
    /* Profile section styling */
    .profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 16px;
        border-radius: 50px;
        background: var(--light);
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--dark);
    }

    .profile:hover {
        background: var(--grey);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--dark);
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.3s ease;
    }

    /* Dark mode support */
    body.dark .profile {
        background: var(--light);
        color: white    ;
    }

    body.dark .profile:hover {
        background: var(--grey);
    }

    body.dark .profile-name {
        color: var(--light);
    }

    /* Responsive adjustments */
    @media screen and (max-width: 768px) {
        .profile {
            padding: 6px 12px;
        }

        .profile img {
            width: 32px;
            height: 32px;
        }

        .profile-name {
            max-width: 120px;
            font-size: 0.9rem;
        }
    }
</style>
</body>
</html>