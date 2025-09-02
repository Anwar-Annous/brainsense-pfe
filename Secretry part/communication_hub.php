<?php

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'brainsense';

// Create connection with database name
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("Error setting charset: " . mysqli_error($conn));
}

// Set timezone
date_default_timezone_set('UTC');


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


// Fetch upcoming appointments
$appointments_query = "SELECT a.*, p.full_name as patient_name 
                      FROM appointments a 
                      LEFT JOIN patients p ON a.patient_id = p.id 
                      WHERE a.appointment_date >= NOW() 
                      ORDER BY a.appointment_date ASC 
                      LIMIT 5";
$appointments_result = mysqli_query($conn, $appointments_query);

// Fetch quick notes
$notes_query = "SELECT * FROM quick_notes ORDER BY created_at DESC LIMIT 5";
$notes_result = mysqli_query($conn, $notes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication Hub - BrainSense</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <style>
        .communication-hub {
            padding: 2rem;
            height: calc(100vh - 70px);
            overflow-y: auto;
            background: var(--grey);
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--grey);
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: var(--blue);
        }

        .action-card i {
            font-size: 2rem;
            color: var(--blue);
            background: var(--light-blue);
            padding: 0.75rem;
            border-radius: 10px;
        }

        .action-card .action-info h3 {
            font-size: 1.1rem;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .action-card .action-info p {
            font-size: 0.875rem;
            color: var(--dark-grey);
        }

        .messages-section {
            background: var(--light);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--grey);
        }

        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--grey);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light);
            border-radius: 12px 12px 0 0;
        }

        .section-header h2 {
            font-size: 1.25rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-header h2 i {
            color: var(--blue);
            background: var(--light-blue);
            padding: 0.5rem;
            border-radius: 8px;
        }

        .messages-list {
            padding: 1rem;
            max-height: 600px;
            overflow-y: auto;
        }

        .message-item {
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--light);
            border: 1px solid var(--grey);
            transition: all 0.3s ease;
        }

        .message-item:hover {
            background: var(--light-blue);
            border-color: var(--blue);
        }

        .message-item.unread {
            background: var(--light-blue);
            border-left: 4px solid var(--blue);
        }

        .message-item.unread:hover {
            background: #fffbe6; /* Soft yellow for unread hover */
            border-color: var(--yellow);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .message-title {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .message-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
        }

        .priority {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .priority.high {
            background: #ffebee;
            color: #d32f2f;
        }

        .priority.medium {
            background: #fff3e0;
            color: #f57c00;
        }

        .priority.low {
            background: #e8f5e9;
            color: #388e3c;
        }

        .message-preview {
            color: var(--dark-grey);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .message-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-primary {
            background: var(--blue);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--dark);
            border: 1px solid var(--grey);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--light-blue);
            border-color: var(--blue);
            color: var(--blue);
        }

        .side-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .appointments-card, .notes-card {
            background: var(--light);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--grey);
        }

        .appointments-list {
            padding: 1rem;
        }

        .appointment-item {
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--light);
            border: 1px solid var(--grey);
            transition: all 0.3s ease;
        }

        .appointment-item:hover {
            background: var(--light-blue);
            border-color: var(--blue);
        }

        .appointment-time {
            font-size: 0.875rem;
            color: var(--blue);
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .appointment-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .appointment-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--dark-grey);
        }

        .appointment-details span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .appointment-details i {
            color: var(--blue);
        }

        .notes-list {
            padding: 1rem;
        }

        .note-item {
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--light);
            border: 1px solid var(--grey);
        }

        .note-content textarea {
            width: 100%;
            min-height: 100px;
            padding: 1rem;
            border: 1px solid var(--grey);
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
            font-size: 0.875rem;
            color: var(--dark);
            background: var(--light);
            transition: all 0.3s ease;
        }

        .note-content textarea:focus {
            border-color: var(--blue);
            outline: none;
            box-shadow: 0 0 0 2px var(--light-blue);
        }

        .note-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: var(--dark-grey);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: var(--light-blue);
            color: var(--blue);
        }

        @media screen and (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .communication-hub {
                padding: 1rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .message-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .message-meta {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            position: relative;
            background: var(--light);
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 99999;

        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: var(--dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-grey);
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--grey);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--dark);
            background: var(--light);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Add these styles to your existing CSS */
        .message-info {
            color: var(--dark-grey);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .message-content {
            background: var(--light);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--grey);
            margin-top: 1rem;
            white-space: pre-wrap;
            min-height: 100px;
        }

        .message-details {
            padding: 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: var(--light);
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--grey);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-grey);
            cursor: pointer;
        }

        .message-item {
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--light);
            border: 1px solid var(--grey);
            transition: all 0.3s ease;
        }

        .message-item:hover {
            background: var(--light-blue);
            border-color: var(--blue);
        }

        .message-item.unread {
            background: var(--light-blue);
            border-left: 4px solid var(--blue);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .message-title {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .message-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
        }

        .message-preview {
            color: var(--dark-grey);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .message-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .audience-toggle-group {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
            justify-content: center;
        }
        .audience-toggle {
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            border: 1.5px solid var(--blue);
            background: var(--light);
            color: var(--blue);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            outline: none;
            box-shadow: none;
        }
        .audience-toggle.selected, .audience-toggle:active {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
            box-shadow: 0 2px 8px rgba(74,144,226,0.08);
        }
        .audience-toggle:focus {
            outline: 2px solid var(--blue);
        }

        /* Dark Mode Styles */
        body.dark .communication-hub {
            background: var(--light);
            color: var(--dark);
        }

        body.dark .action-card,
        body.dark .messages-section,
        body.dark .appointments-card,
        body.dark .notes-card,
        body.dark .modal-content {
            background: var(--grey);
            color: var(--dark);
        }

        body.dark .action-card:hover,
        body.dark .message-item:hover,
        body.dark .appointment-item:hover {
            background: var(--light-blue);
            border-color: var(--dark);
        }

        body.dark .action-card i,
        body.dark .section-header h2 i {
            color: var(--blue);
            background: var(--light-blue);
        }

        body.dark .action-card .action-info h3,
        body.dark .message-title,
        body.dark .appointment-title {
            color: var(--dark);
        }

        body.dark .action-card .action-info p,
        body.dark .message-preview,
        body.dark .appointment-details {
            color: var(--dark-grey);
        }

        body.dark .btn-primary {
            background: var(--blue);
            color: var(--dark);
        }

        body.dark .btn-secondary {
            background: var(--grey);
            color: var(--dark);
            border-color: var(--dark-grey);
        }

        body.dark .btn-secondary:hover {
            background: var(--light-blue);
            border-color: var(--blue);
            color: var(--blue);
        }

        body.dark .form-group input,
        body.dark .form-group select,
        body.dark .form-group textarea {
            background: var(--grey);
            color: var(--dark);
            border-color: var(--dark-grey);
        }

        body.dark .form-group input:focus,
        body.dark .form-group select:focus,
        body.dark .form-group textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 2px var(--light-blue);
        }

        body.dark .priority.high {
            background: #462020;
            color: #f8d7da;
        }

        body.dark .priority.medium {
            background: #463020;
            color: #fff3e0;
        }

        body.dark .priority.low {
            background: #204620;
            color: #d4edda;
        }

        body.dark .audience-toggle {
            background: var(--grey);
            color: var(--dark);
            border-color: var(--blue);
        }

        body.dark .audience-toggle.selected {
            background: var(--blue);
            color: var(--dark);
        }

        body.dark .message-item.unread {
            background: #1e3a8a !important;
            border-left: 4px solid #60a5fa !important;
            color: #f1f5f9 !important;
        }
        body.dark .message-item.unread:hover {
            background: #2563eb !important;
            border-color: #3b82f6 !important;
            color: #f1f5f9 !important;
        }

        body.dark .note-content {
            background: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        body.dark .note-content:focus {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 2px #2563eb44 !important;
        }

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
            background: var(--dark);
            color: var(--light);
        }

        body.dark .profile:hover {
            background: var(--grey);
        }

        body.dark .profile-name {
            color: white;
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
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar" style="z-index: 1;">
        <a href="http://localhost/brainsense/Secretry%20part" class="brand">
            <img src="assets/images/Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
            <span class="Logo">BrainSense</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="http://localhost/brainsense/Secretry%20part">
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
            <li class="active">
                <a href="communication_hub.php">
                    <i class='bx bxs-chat'></i>
                    <span class="text">Communication Hub</span>
                </a>
            </li>
            <li>
                <a href="resources.php">
                    <i class='bx bxs-clinic'></i>
                    <span class="text">Resources</span>
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


        <!-- MAIN CONTENT -->
        <main class="communication-hub">
            <div class="head-title">
                <div class="left">
                    <h1>Communication Hub</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">BrainSense</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Communication Hub</a></li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="openNewMessageModal()">
                    <i class='bx bx-message-square-add'></i>
                    <div class="action-info">
                        <h3>New Message</h3>
                        <p>Send a message to a patient or doctor</p>
                    </div>
                </div>
                <div class="action-card" onclick="openNewAppointmentModal()">
                    <i class='bx bx-calendar-plus'></i>
                    <div class="action-info">
                        <h3>New Appointment</h3>
                        <p>Schedule a new appointment</p>
                    </div>
                </div>
                <div class="action-card" onclick="openNewBroadcastModal()">
                    <i class='bx bx-broadcast'></i>
                    <div class="action-info">
                        <h3>New Broadcast</h3>
                        <p>Send a message to multiple recipients</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="main-grid">
                <!-- Messages Section -->
                <div class="messages-section">
                    <div class="section-header">
                        <h2><i class='bx bx-message-square-dots'></i> Recent Messages</h2>
                        <button class="btn-primary" onclick="openNewMessageModal()">
                            <i class='bx bx-plus'></i> New Message
                        </button>
                    </div>
                    <div class="messages-list" id="messagesList">
                        <!-- Messages will be loaded dynamically -->
                    </div>
                </div>

                <!-- Side Section -->
                <div class="side-section">
                    <!-- Appointments Card -->
                    <div class="appointments-card">
                        <div class="section-header">
                            <h2><i class='bx bx-calendar'></i> Upcoming Appointments</h2>
                        </div>
                        <div class="appointments-list" id="appointmentsList">
                            <!-- Appointments will be loaded here by JS -->
                        </div>
                    </div>

                    <!-- Quick Notes Card -->
                    <div class="notes-card">
                        <div class="section-header">
                            <h2><i class='bx bx-note'></i> Quick Notes</h2>
                        </div>
                        <div class="notes-list">
                            <div class="note-item">
                                <textarea class="note-content" placeholder="Add a quick note..."></textarea>
                                <div class="note-actions">
                                    <button class="btn-secondary" onclick="saveNote()">
                                        <i class='bx bx-save'></i> Save
                                    </button>
                                </div>
                            </div>
                            <?php while ($note = mysqli_fetch_assoc($notes_result)): ?>
                                <div class="note-item">
                                    <p><?php echo htmlspecialchars($note['content']); ?></p>
                                    <div class="note-actions">
                                        <button class="btn-icon" onclick="editNote(<?php echo $note['id']; ?>)">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <button class="btn-icon" onclick="deleteNote(<?php echo $note['id']; ?>)">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <!-- New Message Modal -->
    <div id="newMessageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Message</h2>
                <button class="close-modal" onclick="closeModal('newMessageModal')">&times;</button>
            </div>
            <form id="newMessageForm" onsubmit="sendMessage(event)">
                <div class="form-group">
                    <label for="recipientType">Recipient Type</label>
                    <select id="recipientType" name="recipientType" required>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="recipientId">Recipient</label>
                    <select id="recipientId" name="recipientId" required>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('newMessageModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add New Appointment Modal -->
    <div id="newAppointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Appointment</h2>
                <button class="close-modal" onclick="closeModal('newAppointmentModal')">&times;</button>
            </div>
            <form id="newAppointmentForm" onsubmit="saveAppointment(event)">
                <div class="form-group">
                    <label for="appointmentPatient">Patient</label>
                    <select id="appointmentPatient" name="patient_id" required>
                        <option value="">Select a patient</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointmentDoctor">Doctor</label>
                    <select id="appointmentDoctor" name="doctor_id" required>
                        <option value="">Select a doctor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="appointmentDate">Date</label>
                    <input type="datetime-local" id="appointmentDate" name="appointment_date" required>
                </div>
                <div class="form-group">
                    <label for="appointmentLocation">Location</label>
                    <input type="text" id="appointmentLocation" name="location" required>
                </div>
                <div class="form-group">
                    <label for="appointmentNotes">Notes</label>
                    <textarea id="appointmentNotes" name="notes"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('newAppointmentModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Schedule Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Message Modal -->
    <div id="viewMessageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="viewSubject"></h2>
                <button class="close-modal" onclick="closeModal('viewMessageModal')">&times;</button>
            </div>
            <div class="message-details">
                <div class="form-group">
                    <p id="viewSender" class="message-info"></p>
                    <p id="viewRecipient" class="message-info"></p>
                    <p id="viewDate" class="message-info"></p>
                    <p id="viewPriority" class="message-info"></p>
                </div>
                <div class="form-group">
                    <div id="viewMessage" class="message-content"></div>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn-secondary" onclick="closeModal('viewMessageModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Broadcast Modal -->
    <div id="broadcastModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>New Broadcast</h2>
                <button class="close-modal" onclick="closeModal('broadcastModal')">&times;</button>
            </div>
            <form id="broadcastForm" onsubmit="sendBroadcast(event)">
                <div class="form-group">
                    <label>Audience</label>
                    <div class="audience-toggle-group">
                        <button type="button" class="audience-toggle" data-value="doctors">Doctors</button>
                        <button type="button" class="audience-toggle" data-value="patients">Patients</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="broadcastSubject">Subject</label>
                    <input type="text" id="broadcastSubject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="broadcastMessage">Message</label>
                    <textarea id="broadcastMessage" name="message" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('broadcastModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Send Broadcast</button>
                </div>
            </form>
        </div>
    </div>
<script src="script.js"></script>
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize event listeners
            const recipientTypeSelect = document.getElementById('recipientType');
            if (recipientTypeSelect) {
                recipientTypeSelect.addEventListener('change', function() {
                    loadRecipients();
                });
            }

            // Load initial data
            loadMessages();
            loadAppointments();
        });

        // Load messages from API
        function loadMessages() {
            const messagesList = document.getElementById('messagesList');
            if (!messagesList) return;

            // Show loading state
            messagesList.innerHTML = '<div class="loading">Loading messages...</div>';

            fetch('api/get_messages.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.messages.length === 0) {
                            messagesList.innerHTML = '<div class="no-messages">No messages found</div>';
                            return;
                        }

                        messagesList.innerHTML = '';
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.className = `message-item ${message.status === 'unread' ? 'unread' : ''}`;
                            messageElement.setAttribute('data-id', message.id);
                            
                            messageElement.innerHTML = `
                                <div class="message-header">
                                    <h3 class="message-title">${message.subject}</h3>
                                    <div class="message-meta">
                                        <span class="priority ${message.priority}">
                                            ${message.priority.charAt(0).toUpperCase() + message.priority.slice(1)}
                                        </span>
                                        <span class="time">
                                            ${new Date(message.created_at).toLocaleString()}
                                        </span>
                                    </div>
                                </div>
                                <div class="message-preview">
                                    ${message.message.substring(0, 150)}...
                                </div>
                                <div class="message-actions">
                                    <button class="btn-secondary" onclick="viewMessage(${message.id})">
                                        <i class='bx bx-show'></i> View
                                    </button>
                                    ${message.sender_type !== 'secretary' ? `
                                        <button class="btn-secondary" onclick="replyToMessage(${message.id})">
                                            <i class='bx bx-reply'></i> Reply
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                            
                            messagesList.appendChild(messageElement);
                        });
                    } else {
                        throw new Error(data.message || 'Failed to load messages');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messagesList.innerHTML = `<div class="error-message">Failed to load messages: ${error.message}</div>`;
                });
        }

        // Load recipients based on type
        function loadRecipients() {
            const type = document.getElementById('recipientType')?.value;
            const recipientSelect = document.getElementById('recipientId');
            
            if (!type || !recipientSelect) return;
            
            // Show loading state
            recipientSelect.innerHTML = '<option value="">Loading recipients...</option>';
            recipientSelect.disabled = true;
            
            // Fetch recipients based on type
            fetch(`api/get_recipients.php?type=${type}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.recipients.length === 0) {
                            recipientSelect.innerHTML = `<option value="">No ${type}s found</option>`;
                        } else {
                            recipientSelect.innerHTML = '<option value="">Select a recipient</option>' + 
                                data.recipients.map(recipient => 
                                    `<option value="${recipient.id}">${recipient.name}</option>`
                                ).join('');
                        }
                    } else {
                        throw new Error(data.message || 'Failed to load recipients');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recipientSelect.innerHTML = `<option value="">Error: ${error.message}</option>`;
                })
                .finally(() => {
                    recipientSelect.disabled = false;
                });
        }

        // Modal Functions
        function openNewMessageModal() {
            const modal = document.getElementById('newMessageModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                loadRecipients();
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                // If closing the view message modal, refresh messages
                if (modalId === 'viewMessageModal' && currentViewedMessageId) {
                    loadMessages();
                    currentViewedMessageId = null;
                }
            }
        }

        // Send message
        function sendMessage(event) {
            event.preventDefault();
            
            const formData = {
                subject: document.getElementById('subject')?.value,
                message: document.getElementById('message')?.value,
                recipient_id: document.getElementById('recipientId')?.value,
                recipient_type: document.getElementById('recipientType')?.value,
                priority: document.getElementById('priority')?.value
            };

            if (!formData.subject || !formData.message || !formData.recipient_id || !formData.recipient_type) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Show loading state
            const submitButton = event.target.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Sending...';
            submitButton.disabled = true;
            
            // Log the request data
            console.log('Sending message with data:', formData);
            
            fetch('api/save_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(async response => {
                // Log the raw response
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                
                // Get the raw text first
                const rawText = await response.text();
                console.log('Raw response:', rawText);
                
                // Try to parse as JSON
                try {
                    const data = JSON.parse(rawText);
                    if (!response.ok) {
                        throw new Error(data.message || 'Network response was not ok');
                    }
                    return data;
                } catch (e) {
                    console.error('Failed to parse response as JSON:', e);
                    console.error('Raw response was:', rawText);
                    throw new Error('Server returned invalid JSON response. Check console for details.');
                }
            })
            .then(data => {
                if (data.success) {
                    closeModal('newMessageModal');
                    loadMessages();
                    // Clear form
                    const form = document.getElementById('newMessageForm');
                    if (form) form.reset();
                    
                    // Show success message
                    alert('Message sent successfully!');
                } else {
                    throw new Error(data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                alert('Failed to send message: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        }

        // Note Functions
        function saveNote() {
            const noteContent = document.querySelector('.note-content');
            if (!noteContent) return;

            const content = noteContent.value;
            if (!content.trim()) return;

            fetch('api/save_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to save note: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save note. Please try again later.');
            });
        }

        function editNote(id) {
            // Implement edit note
            console.log('Edit note:', id);
        }

        function deleteNote(id) {
            if (!confirm('Are you sure you want to delete this note?')) return;

            fetch('api/delete_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete note: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete note. Please try again later.');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            // Check if the clicked element is a modal backdrop
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        // Add these new functions
        function openNewAppointmentModal() {
            const modal = document.getElementById('newAppointmentModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                loadAppointmentFormData();
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
                        const patientSelect = document.getElementById('appointmentPatient');
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
                        const doctorSelect = document.getElementById('appointmentDoctor');
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

        function saveAppointment(event) {
            event.preventDefault();
            
            const formData = {
                patient_id: document.getElementById('appointmentPatient')?.value,
                doctor_id: document.getElementById('appointmentDoctor')?.value,
                appointment_date: document.getElementById('appointmentDate')?.value,
                location: document.getElementById('appointmentLocation')?.value,
                notes: document.getElementById('appointmentNotes')?.value
            };

            if (!formData.patient_id || !formData.doctor_id || !formData.appointment_date || !formData.location) {
                alert('Please fill in all required fields');
                return;
            }

            fetch('api/save_appointment.php', {
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
                    closeModal('newAppointmentModal');
                    loadAppointments(); // Refresh the list
                } else {
                    alert('Failed to schedule appointment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to schedule appointment. Please try again later.');
            });
        }

        let currentViewedMessageId = null;

        function viewMessage(messageId) {
            currentViewedMessageId = messageId;
            fetch(`api/get_message.php?id=${messageId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        document.getElementById('viewSubject').textContent = message.subject;
                        document.getElementById('viewSender').textContent = `From: ${message.sender_name} (${message.sender_type})`;
                        document.getElementById('viewRecipient').textContent = `To: ${message.recipient_name} (${message.recipient_type})`;
                        document.getElementById('viewDate').textContent = `Date: ${new Date(message.created_at).toLocaleString()}`;
                        document.getElementById('viewPriority').textContent = `Priority: ${message.priority.charAt(0).toUpperCase() + message.priority.slice(1)}`;
                        document.getElementById('viewMessage').textContent = message.message;
                        
                        // Show the view modal
                        const viewModal = document.getElementById('viewMessageModal');
                        if (viewModal) {
                           viewModal.style.display = 'block';
                           document.body.style.overflow = 'hidden';
                        }
                    } else {
                        throw new Error(data.message || 'Failed to load message');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load message: ' + error.message);
                });
        }

        function replyToMessage(messageId) {
            fetch(`api/get_message.php?id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        
                        // Set the recipient information
                        document.getElementById('recipientId').value = message.sender_id;
                        document.getElementById('recipientType').value = message.sender_type;
                        
                        // Set the subject with "Re:" prefix
                        const originalSubject = message.subject;
                        const replySubject = originalSubject.startsWith('Re:') ? originalSubject : `Re: ${originalSubject}`;
                        document.getElementById('subject').value = replySubject;
                        
                        // Clear the message field
                        document.getElementById('message').value = '';
                        
                        // Close view modal if open
                        const viewModal = document.getElementById('viewMessageModal');
                         if (viewModal) {
                             viewModal.style.display = 'none';
                         }
                        
                        // Show the new message modal
                        const newMessageModal = document.getElementById('newMessageModal');
                         if (newMessageModal) {
                             newMessageModal.style.display = 'block';
                             document.body.style.overflow = 'hidden';
                         }
                    } else {
                        alert('Failed to load message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load message');
                });
        }

        function openNewBroadcastModal() {
            const modal = document.getElementById('broadcastModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                // Reset audience selection
                document.querySelectorAll('.audience-toggle').forEach(btn => btn.classList.remove('selected'));
            }
        }

        // Toggle audience selection (can select one or both)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.audience-toggle').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.classList.toggle('selected');
                });
            });
        });

        function sendBroadcast(event) {
            event.preventDefault();
            // Collect selected audiences
            const selected = Array.from(document.querySelectorAll('.audience-toggle.selected')).map(btn => btn.getAttribute('data-value'));
            const subject = document.getElementById('broadcastSubject')?.value;
            const message = document.getElementById('broadcastMessage')?.value;
            if (selected.length === 0) {
                alert('Please select at least one audience.');
                return;
            }
            if (!subject || !message) {
                alert('Please fill in all fields');
                return;
            }
            const formData = {
                audience: selected, // array
                subject,
                message
            };
            const submitButton = event.target.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Sending...';
            submitButton.disabled = true;
            fetch('api/send_broadcast.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('broadcastModal');
                    alert('Broadcast sent successfully!');
                } else {
                    throw new Error(data.message || 'Failed to send broadcast');
                }
            })
            .catch(error => {
                alert('Failed to send broadcast: ' + error.message);
            })
            .finally(() => {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        }

        function loadAppointments() {
            fetch('api/get_all_appointments.php')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('appointmentsList');
                    list.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(appointment => {
                            const item = document.createElement('div');
                            item.className = 'appointment-item';
                            item.innerHTML = `
                                <div class="appointment-time">
                                    ${new Date(appointment.appointment_date).toLocaleString()}
                                </div>
                                <h4 class="appointment-title">
                                    ${appointment.patient_name || ''}
                                </h4>
                                <div class="appointment-details">
                                    <span><i class='bx bx-user'></i> ${appointment.doctor_name || ''}</span>
                                    <span><i class='bx bx-map'></i> ${appointment.location || ''}</span>
                                </div>
                            `;
                            list.appendChild(item);
                        });
                    } else {
                        list.innerHTML = '<div>No upcoming appointments.</div>';
                    }
                })
                .catch(() => {
                    document.getElementById('appointmentsList').innerHTML = '<div>Error loading appointments.</div>';
                });
        }
    </script>
</body>
</html> 