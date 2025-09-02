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

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$blood_type = isset($_GET['blood_type']) ? $_GET['blood_type'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';

// Build the query with search and filter conditions
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(full_name LIKE ? OR national_id LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($blood_type)) {
    $where_conditions[] = "blood_type = ?";
    $params[] = $blood_type;
}

// Combine where conditions
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Handle sorting
$order_by = match($sort_by) {
    'name' => 'full_name ASC',
    'date' => 'created_at DESC',
    'id' => 'id ASC',
    default => 'created_at DESC'
};

// Fetch patients with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 6;
$offset = ($page - 1) * $records_per_page;

// Get total number of patients with filters
$total_query = "SELECT COUNT(*) as total FROM patients $where_clause";
$stmt = mysqli_prepare($conn, $total_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$total_result = mysqli_stmt_get_result($stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_patients = $total_row['total'];
$total_pages = ceil($total_patients / $records_per_page);

// Fetch patients with pagination, search, and filters
$query = "SELECT * FROM patients $where_clause ORDER BY $order_by LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $query);

// Add pagination parameters
$params[] = $offset;
$params[] = $records_per_page;

// Bind all parameters
if (!empty($params)) {
    $types = str_repeat('s', count($params) - 2) . 'ii'; // All strings except last two which are integers
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - BrainSense</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            position: relative;
            background: var(--light);
            margin: 50px auto;
            padding: 2rem;
            width: 90%;
            max-width: 800px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--grey);
        }

        .modal-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--blue);
        }

        .patient-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .info-group {
            margin-bottom: 1rem;
        }

        .info-group h3 {
            color: var(--dark);
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .info-group p {
            color: var(--dark-grey);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .profile-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid var(--blue);
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: var(--dark-grey);
            font-size: 0.95rem;
        }

        /* Dark mode support */
        body.dark .modal-content {
            background: var(--grey);
        }

        body.dark .modal-header h2,
        body.dark .info-group h3,
        body.dark .profile-name {
            color: var(--light);
        }

        body.dark .info-group p,
        body.dark .profile-email {
            color: var(--light-grey);
        }

        body.dark .close-modal {
            color: var(--light);
        }

        body.dark .close-modal:hover {
            color: var(--blue);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="#" class="brand">
            <img src="Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
            <span class="Logo">BrainSense</span>
        </a>
        <ul class="side-menu top">
            <li><a href="http://localhost/brainsense/Secretry%20part/"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li class="active"><a href="#"><i class='bx bxs-user'></i><span class="text">Patient Records</span></a></li>
            <li><a href="document_center.php"><i class='bx bxs-file'></i><span class="text">Document Center</span></a></li>
            <li><a href="communication_hub.php"><i class='bx bxs-chat'></i><span class="text">Communication Hub</span></a></li>
            <li><a href="resources.php"><i class='bx bxs-clinic'></i><span class="text">Resources</span></a></li>
        </ul>
        <ul class="side-menu">
            <li><a href="../logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <!-- Main Content -->
    <section id="content">
        <!-- Navbar -->
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

        <!-- Patient Records Section -->
        <div class="patient-records">
            <!-- Header Section -->
            <div class="records-header">
                <div class="header-left">
                <h2><i class='bx bxs-user-detail'></i> Patient Records</h2>
                    <p class="subtitle">Manage and view all patient information</p>
                </div>
                <div class="header-actions">
                    <a href="export_patients.php" class="btn-secondary">
                        <i class='bx bx-export'></i> Export
                    </a>
                <button class="btn-primary" onclick="openNewPatientModal()">
                    <i class='bx bx-plus'></i> Add New Patient
                </button>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="search-filters">
                <form action="" method="GET" class="search-form" id="searchForm">
                <div class="search-box">
                    <i class='bx bx-search'></i>
                        <input type="text" name="search" id="searchInput" 
                               placeholder="Search patients by name, ID, or contact..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               autocomplete="off">
                </div>
                    <div class="filter-group">
                        <select class="filter-select" name="blood_type" id="bloodTypeFilter">
                    <option value="">All Blood Types</option>
                            <option value="A+" <?php echo $blood_type === 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo $blood_type === 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo $blood_type === 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo $blood_type === 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo $blood_type === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo $blood_type === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo $blood_type === 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo $blood_type === 'O-' ? 'selected' : ''; ?>>O-</option>
                </select>
                        <select class="filter-select" name="sort_by" id="sortFilter">
                            <option value="date" <?php echo $sort_by === 'date' ? 'selected' : ''; ?>>Sort by Registration Date</option>
                            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="id" <?php echo $sort_by === 'id' ? 'selected' : ''; ?>>Sort by ID</option>
                </select>
                    </div>
                </form>
            </div>

            <!-- Patient Records Grid -->
            <div class="records-grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($patient = mysqli_fetch_assoc($result)): 
                    $dob = new DateTime($patient['date_of_birth']);
                    $now = new DateTime();
                    $age = $dob->diff($now)->y;
                    $created_date = date('M d, Y', strtotime($patient['created_at']));
                ?>
                <div class="record-card">
                        <div class="card-header">
                        <div class="patient-avatar">
                            <?php if($patient['profile_photo']): ?>
                                <img src="<?php echo htmlspecialchars($patient['profile_photo']); ?>" alt="Patient Photo">
                            <?php else: ?>
                                <i class='bx bxs-user'></i>
                            <?php endif; ?>
                        </div>
                            <div class="patient-info">
                            <h3><?php echo htmlspecialchars($patient['full_name']); ?></h3>
                                <div class="patient-meta">
                                    <span class="patient-id">ID: #<?php echo htmlspecialchars($patient['id']); ?></span>
                                    <span class="patient-age"><?php echo $age; ?> years</span>
                            <span class="blood-type"><?php echo htmlspecialchars($patient['blood_type']); ?></span>
                        </div>
                    </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class='bx bx-id-card'></i>
                                    <div class="info-content">
                                        <label>National ID</label>
                                        <p><?php echo htmlspecialchars($patient['national_id']); ?></p>
                        </div>
                    </div>
                                <div class="info-item">
                                    <i class='bx bx-calendar'></i>
                                    <div class="info-content">
                                        <label>Registered</label>
                                        <p><?php echo $created_date; ?></p>
                                    </div>
                        </div>
                    </div>
                            
                            <div class="contact-info">
                                <a href="mailto:<?php echo htmlspecialchars($patient['email']); ?>" class="contact-link">
                            <i class='bx bx-envelope'></i>
                            <?php echo htmlspecialchars($patient['email']); ?>
                        </a>
                                <a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>" class="contact-link">
                            <i class='bx bx-phone'></i>
                            <?php echo htmlspecialchars($patient['phone']); ?>
                        </a>
                    </div>
                        </div>

                        <div class="card-actions">
                        <button class="action-btn view-btn" onclick="viewPatientRecord(<?php echo $patient['id']; ?>)">
                                <i class='bx bx-show'></i> View Details
                        </button>
                        <button class="action-btn edit-btn" onclick="editPatientRecord(<?php echo $patient['id']; ?>)">
                            <i class='bx bx-edit'></i> Edit
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class='bx bx-search-alt'></i>
                        <h3>No patients found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&blood_type=<?php echo urlencode($blood_type); ?>&sort_by=<?php echo urlencode($sort_by); ?>" class="page-btn">
                        <i class='bx bx-chevron-left'></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&blood_type=<?php echo urlencode($blood_type); ?>&sort_by=<?php echo urlencode($sort_by); ?>" 
                       class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                </div>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&blood_type=<?php echo urlencode($blood_type); ?>&sort_by=<?php echo urlencode($sort_by); ?>" class="page-btn">
                        Next <i class='bx bx-chevron-right'></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- View Patient Modal -->
    <div id="viewPatientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Patient Information</h2>
                <button class="close-modal" onclick="closeViewModal()">&times;</button>
            </div>
            <div class="profile-section">
                <img id="viewProfileImage" src="assets/images/default-avatar.png" alt="Profile Picture" class="profile-image">
                <h3 class="profile-name" id="viewFullName"></h3>
                <p class="profile-email" id="viewEmail"></p>
            </div>
            <div class="patient-info">
                <div class="info-group">
                    <h3>Contact Information</h3>
                    <p><strong>Phone:</strong> <span id="viewPhone"></span></p>
                    <p><strong>Address:</strong> <span id="viewAddress"></span></p>
                </div>
                <div class="info-group">
                    <h3>Personal Information</h3>
                    <p><strong>Date of Birth:</strong> <span id="viewDOB"></span></p>
                    <p><strong>Gender:</strong> <span id="viewGender"></span></p>
                    <p><strong>Blood Type:</strong> <span id="viewBloodType"></span></p>
                </div>
                <div class="info-group">
                    <h3>Emergency Contact</h3>
                    <p><strong>Name:</strong> <span id="viewEmergencyContact"></span></p>
                    <p><strong>Phone:</strong> <span id="viewEmergencyPhone"></span></p>
                </div>
                <div class="info-group">
                    <h3>Medical Information</h3>
                    <p><strong>Allergies:</strong> <span id="viewAllergies"></span></p>
                    <p><strong>Medical History:</strong> <span id="viewMedicalHistory"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class='bx bxs-edit'></i> Edit Patient Record</h2>
            <form id="editPatientForm" class="edit-form" method="post" enctype="multipart/form-data" onsubmit="return false;">
                <input type="hidden" id="editPatientId" name="id">
                
                <div class="form-group photo-upload-section">
                    <label>Profile Photo</label>
                    <div class="profile-photo-upload" id="profilePhotoUpload">
                        <div class="photo-preview">
                        <img id="currentProfilePhoto" src="assets/images/default-avatar.png" alt="Profile Photo">
                            <div class="photo-overlay">
                                <i class='bx bx-camera'></i>
                                <span>Click to upload photo</span>
                            </div>
                        </div>
                        <input type="file" id="editProfilePhoto" name="profile_photo" accept="image/*" style="display: none;">
                        <div class="upload-info">
                            <p class="upload-text">Drag and drop an image or <span>click to browse</span></p>
                            <p class="upload-hint">Recommended: Square image, max 2MB</p>
                        </div>
                        <div class="upload-progress">
                            <div class="progress-bar"></div>
                        </div>
                        <div class="upload-status">
                            <i class='bx bx-check-circle'></i>
                            <span>Upload successful!</span>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                <div class="form-group">
                    <label for="editFullName">Full Name</label>
                    <input type="text" id="editFullName" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>

                <div class="form-group">
                    <label for="editPhone">Phone</label>
                    <input type="tel" id="editPhone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="editAddress">Address</label>
                    <textarea id="editAddress" name="address" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="editNationalId">National ID</label>
                    <input type="text" id="editNationalId" name="national_id" required>
                </div>

                <div class="form-group">
                    <label for="editDateOfBirth">Date of Birth</label>
                    <input type="date" id="editDateOfBirth" name="date_of_birth" required>
                </div>

                <div class="form-group">
                    <label for="editGender">Gender</label>
                    <select id="editGender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editBloodType">Blood Type</label>
                    <select id="editBloodType" name="blood_type" required>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editAllergies">Allergies</label>
                    <textarea id="editAllergies" name="allergies" rows="2" placeholder="Enter any allergies or 'None'"></textarea>
                </div>

                <div class="form-group">
                    <label for="editMedicalConditions">Medical Conditions</label>
                    <textarea id="editMedicalConditions" name="medical_conditions" rows="2" placeholder="Enter any medical conditions or 'None'"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-save'></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add New Patient Modal -->
    <div id="newPatientModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class='bx bx-user-plus'></i> Add New Patient</h2>
            <form id="newPatientForm" class="edit-form" method="post" enctype="multipart/form-data" onsubmit="return false;">
                <div class="form-group">
                    <label for="profilePhoto">Profile Photo</label>
                    <div class="profile-photo-upload" id="newProfilePhotoUpload">
                        <div class="photo-preview">
                            <img id="newProfilePhoto" src="assets/images/default-avatar.png" alt="Profile Photo">
                            <div class="photo-overlay">
                                <i class='bx bx-camera'></i>
                                <span>Click to upload photo</span>
                            </div>
                        </div>
                        <input type="file" id="profilePhoto" name="profile_photo" accept="image/*" style="display: none;">
                        <div class="upload-info">
                            <p class="upload-text">Drag and drop an image or <span>click to browse</span></p>
                            <p class="upload-hint">Recommended: Square image, max 2MB</p>
                        </div>
                        <div class="upload-progress">
                            <div class="progress-bar"></div>
                        </div>
                        <div class="upload-status">
                            <i class='bx bx-check-circle'></i>
                            <span>Upload successful!</span>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="nationalId">National ID</label>
                        <input type="text" id="nationalId" name="national_id" required>
                    </div>

                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth</label>
                        <input type="date" id="dateOfBirth" name="date_of_birth" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bloodType">Blood Type</label>
                        <select id="bloodType" name="blood_type" required>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies" rows="2" placeholder="Enter any allergies or 'None'"></textarea>
                </div>

                <div class="form-group">
                    <label for="medicalConditions">Medical Conditions</label>
                    <textarea id="medicalConditions" name="medical_conditions" rows="2" placeholder="Enter any medical conditions or 'None'"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeNewPatientModal()">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-save'></i> Add Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

<script src="script.js"></script>
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize search functionality
            const searchInput = document.getElementById('searchInput');
            const bloodTypeFilter = document.getElementById('bloodTypeFilter');
            const sortFilter = document.getElementById('sortFilter');

            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(updateResults, 500);
                });
            }

            if (bloodTypeFilter) {
                bloodTypeFilter.addEventListener('change', updateResults);
            }

            if (sortFilter) {
                sortFilter.addEventListener('change', updateResults);
            }

            // Clear search when clicking the search icon
            const searchIcon = document.querySelector('.search-box i');
            if (searchIcon && searchInput) {
                searchIcon.addEventListener('click', function() {
                    searchInput.value = '';
                    updateResults();
                });
            }

            // Initialize modals
            const modals = {
                patientDetails: document.getElementById('viewPatientModal'),
                editPatient: document.getElementById('editPatientModal'),
                newPatient: document.getElementById('newPatientModal')
            };

            // Close buttons for all modals
            document.querySelectorAll('.modal .close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                }
            });
        });

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                Object.values(modals).forEach(modal => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Close modals when pressing Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    Object.values(modals).forEach(modal => {
                        if (modal && modal.style.display === 'block') {
                            modal.style.display = 'none';
                        }
                    });
                }
            });

            // Initialize form submissions
            const editForm = document.getElementById('editPatientForm');
            if (editForm) {
                editForm.addEventListener('submit', handleEditSubmit);
            }

            const newForm = document.getElementById('newPatientForm');
            if (newForm) {
                newForm.addEventListener('submit', handleNewSubmit);
            }

            // Initialize profile photo upload functionality
            initializeProfilePhotoUpload();
        });

        // Function to initialize profile photo upload functionality
        function initializeProfilePhotoUpload() {
            const uploadAreas = ['profilePhotoUpload', 'newProfilePhotoUpload'];
            
            uploadAreas.forEach(areaId => {
                const uploadArea = document.getElementById(areaId);
                if (!uploadArea) return;

                const fileInput = uploadArea.querySelector('input[type="file"]');
                const previewImage = uploadArea.querySelector('img');
                const progressBar = uploadArea.querySelector('.upload-progress');
                const progressBarInner = uploadArea.querySelector('.progress-bar');
                const status = uploadArea.querySelector('.upload-status');
                const photoOverlay = uploadArea.querySelector('.photo-overlay');

                if (fileInput) {
                    // Make the entire upload area clickable
                    uploadArea.addEventListener('click', function(e) {
                        if (e.target !== fileInput) {
                            e.preventDefault();
                            fileInput.click();
                        }
                    });

                    fileInput.addEventListener('change', function(e) {
                        handleFileSelect(e, previewImage, progressBar, progressBarInner, status);
                    });
                }

                // Drag and drop functionality
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, preventDefaults, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
                });

                uploadArea.addEventListener('drop', function(e) {
                    const dt = e.dataTransfer;
                    const file = dt.files[0];
                    
                    if (file && file.type.startsWith('image/')) {
                        fileInput.files = dt.files;
                        const event = new Event('change');
                        fileInput.dispatchEvent(event);
                    } else {
                        showUploadStatus(status, 'error');
                    }
                }, false);
            });
        }

        function handleFileSelect(e, previewImage, progressBar, progressBarInner, status) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showUploadStatus(status, 'error');
                return;
            }

            // Show progress bar
            progressBar.classList.add('active');
            
            // Simulate upload progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                progressBarInner.style.width = `${progress}%`;
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        progressBar.classList.remove('active');
                        showUploadStatus(status, 'success');
                    }, 500);
                }
            }, 200);

            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function showUploadStatus(statusElement, type) {
            if (!statusElement) return;
            
            statusElement.className = 'upload-status ' + type;
            
            if (type === 'success') {
                statusElement.innerHTML = '<i class="bx bx-check-circle"></i><span>Upload successful!</span>';
            } else {
                statusElement.innerHTML = '<i class="bx bx-error-circle"></i><span>Please upload an image file</span>';
            }
            
            setTimeout(() => {
                statusElement.style.display = 'none';
            }, 3000);
        }

        // Function to open new patient modal
        function openNewPatientModal() {
            const modal = document.getElementById('newPatientModal');
            if (!modal) return;

            // Close any other open modals
            document.querySelectorAll('.modal').forEach(m => {
                if (m !== modal) m.style.display = 'none';
            });

            // Reset form and photo
            const form = document.getElementById('newPatientForm');
            const photo = document.getElementById('newProfilePhoto');
            
            if (form) form.reset();
            if (photo) photo.src = 'assets/images/default-avatar.png';

            // Show modal
            modal.style.display = 'block';
        }

        // Function to update URL and reload page
        function updateResults() {
            const searchInput = document.getElementById('searchInput');
            const bloodTypeFilter = document.getElementById('bloodTypeFilter');
            const sortFilter = document.getElementById('sortFilter');

            if (!searchInput || !bloodTypeFilter || !sortFilter) return;

            const searchValue = searchInput.value;
            const bloodTypeValue = bloodTypeFilter.value;
            const sortValue = sortFilter.value;
            
            const params = new URLSearchParams(window.location.search);
            params.set('search', searchValue);
            params.set('blood_type', bloodTypeValue);
            params.set('sort_by', sortValue);
            params.set('page', '1');
            
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        }

        // View Patient Record
        async function viewPatientRecord(patientId) {
            try {
                const response = await fetch(`api/get_patient_details.php?id=${patientId}`);
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response:', errorText);
                    throw new Error(`Failed to load patient details: ${response.status} ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load patient details');
                }
                
                const patient = data.patient;
                const appointments = data.appointments || [];
                const documents = data.documents || [];
                
                // Update modal content
                document.getElementById('viewFullName').textContent = patient.full_name;
                document.getElementById('viewEmail').textContent = patient.email;
                document.getElementById('viewPhone').textContent = patient.phone || 'Not provided';
                document.getElementById('viewAddress').textContent = patient.address || 'Not provided';
                document.getElementById('viewDOB').textContent = patient.date_of_birth || 'Not provided';
                document.getElementById('viewGender').textContent = patient.gender || 'Not provided';
                document.getElementById('viewBloodType').textContent = patient.blood_type || 'Not provided';
                document.getElementById('viewEmergencyContact').textContent = patient.emergency_contact || 'Not provided';
                document.getElementById('viewEmergencyPhone').textContent = patient.emergency_phone || 'Not provided';
                document.getElementById('viewAllergies').textContent = patient.allergies || 'None';
                document.getElementById('viewMedicalHistory').textContent = patient.medical_history || 'None';
                
                // Update profile image
                const profileImage = document.getElementById('viewProfileImage');
                if (patient.profile_photo) {
                    profileImage.src = `../uploads/profiles/${patient.profile_photo}`;
                } else {
                    profileImage.src = 'assets/images/default-avatar.png';
                }
                
                // Show modal
                document.getElementById('viewPatientModal').style.display = 'block';
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            }
        }

        // Edit Patient Record
        function editPatientRecord(id) {
            const modal = document.getElementById('editPatientModal');
            if (!modal) return;

            // Close any other open modals
            document.querySelectorAll('.modal').forEach(m => {
                if (m !== modal) m.style.display = 'none';
            });

            modal.style.display = 'block';
            
            fetch(`api/get_patient_details.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error parsing JSON:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load patient details');
                    }

                    // Set form values
                    document.getElementById('editPatientId').value = data.patient.id || '';
                    document.getElementById('editFullName').value = data.patient.full_name || '';
                    document.getElementById('editEmail').value = data.patient.email || '';
                    document.getElementById('editPhone').value = data.patient.phone || '';
                    document.getElementById('editAddress').value = data.patient.address || '';
                    document.getElementById('editNationalId').value = data.patient.national_id || '';
                    document.getElementById('editDateOfBirth').value = data.patient.date_of_birth || '';
                    document.getElementById('editGender').value = data.patient.gender || 'Male';
                    document.getElementById('editBloodType').value = data.patient.blood_type || 'A+';
                    document.getElementById('editAllergies').value = data.patient.allergies || '';
                    document.getElementById('editMedicalConditions').value = data.patient.medical_conditions || '';

                    // Handle profile photo
                    const currentProfilePhoto = document.getElementById('currentProfilePhoto');
                    if (currentProfilePhoto) {
                        currentProfilePhoto.src = data.patient.profile_photo || 'assets/images/default-avatar.png';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification(error.message, 'error');
                    closeEditModal();
                });
        }

        // Close modal functions
        function closeViewModal() {
            const modal = document.getElementById('viewPatientModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editPatientModal');
            if (modal) modal.style.display = 'none';
        }

        function closeNewPatientModal() {
            const modal = document.getElementById('newPatientModal');
            if (modal) {
                modal.style.display = 'none';
                const form = document.getElementById('newPatientForm');
                if (form) form.reset();
                const photo = document.getElementById('newProfilePhoto');
                if (photo) photo.src = 'assets/images/default-avatar.png';
            }
        }

        // Form submission handlers
        function handleEditSubmit(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
            submitButton.disabled = true;
            
            const formData = new FormData(this);
            
            fetch('update_patient.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Patient record updated successfully!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Failed to update patient record');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            })
            .finally(() => {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        }

        function handleNewSubmit(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Adding...';
            submitButton.disabled = true;
            
            const formData = new FormData(this);
            
            fetch('add_patient.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Patient added successfully!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Failed to add patient');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            })
            .finally(() => {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class='bx ${type === 'success' ? 'bx-check-circle' : 'bx-error-circle'}'></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Delete Patient Record
        function deletePatientRecord(id) {
            if (confirm('Are you sure you want to delete this patient record? This action cannot be undone.')) {
                fetch(`delete_patient.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Patient record deleted successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        throw new Error(data.message || 'Failed to delete patient record');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification(error.message, 'error');
                });
            }
        }

        const callPatientBtn = document.querySelector('.quick-action.call-patient');
    </script>

    <style>
        .notification.show {
            transform: translateX(0);
        }
        .notification.success {
            border-left: 4px solid var(--success);
        }
        .notification.error {
            border-left: 4px solid var(--danger);
        }
        .notification i {
            font-size: 24px;
        }
        .notification.success i {
            color: var(--success);
        }
        .notification.error i {
            color: var(--danger);
        }
        body.dark .notification {
            background: var(--dark);
            color: var(--light);
        }
        /* Sidebar and Navbar dark mode */
        body.dark #sidebar {
            background: #1e293b;
            color: #f1f5f9;
        }
        body.dark #sidebar .side-menu li a {
            background: #1e293b;
            color: #cbd5e1;
        }
        body.dark #sidebar .side-menu li.active,
        body.dark #sidebar .side-menu li.active a {
            background: #334155;
            color: #60a5fa;
        }
        body.dark #sidebar .side-menu li a.logout {
            color: #f87171;
        }
        body.dark #content nav {
            background: #1e293b;
            color: #f1f5f9;
        }
        body.dark #content nav .nav-link,
        body.dark #content nav a {
            background: #1e293b;
        }
        body.dark #content nav .notification .num {
            background: #f87171;
            color: #fff;
        }
        /* Main content dark mode */
        body.dark .patient-records {
            background: #111827;
            color: #f1f5f9;
        }
        body.dark .records-header,
        body.dark .search-filters {
            background: #1e293b;
            border-bottom: 1px solid #334155;
        }
        body.dark .header-left h2,
        body.dark .header-left .subtitle {
            color: #f1f5f9;
        }
        body.dark .search-box input,
        body.dark .filter-select {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }
        body.dark .search-box input:focus,
        body.dark .filter-select:focus {
            border-color: #60a5fa;
        }
        body.dark .record-card {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }
        body.dark .record-card:hover {
            background: #334155;
            border-color: #60a5fa;
        }
        body.dark .patient-info h3,
        body.dark .info-content p {
            color: #f1f5f9;
        }
        body.dark .info-item {
            background: #334155;
        }
        body.dark .info-item:hover {
            background: #2563eb22;
        }
        body.dark .contact-link {
            color: #cbd5e1;
        }
        body.dark .contact-link:hover {
            color: #60a5fa;
            background: #2563eb22;
        }
        body.dark .card-actions .action-btn {
            background: #334155;
            color: #f1f5f9;
        }
        body.dark .card-actions .action-btn.view-btn {
            background: #2563eb;
            color: #fff;
        }
        body.dark .card-actions .action-btn.edit-btn {
            background: #334155;
            color: #f1f5f9;
        }
        body.dark .card-actions .action-btn:hover {
            background: #2563eb;
            color: #fff;
        }
        body.dark .no-results {
            background: #1e293b;
            color: #cbd5e1;
        }
        /* Modal dark mode */
        body.dark .modal-content {
            background: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
        }
        body.dark .modal-content h2 {
            color: #60a5fa;
            border-bottom: 1px solid #334155;
        }
        body.dark .form-group label {
            color: #cbd5e1;
        }
        body.dark .form-group input,
        body.dark .form-group select,
        body.dark .form-group textarea {
            background: #111827;
            color: #f1f5f9;
            border-color: #334155;
        }
        body.dark .form-group input:focus,
        body.dark .form-group select:focus,
        body.dark .form-group textarea:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 2px #2563eb44;
        }
        body.dark .form-actions .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        body.dark .form-actions .btn-primary:hover {
            background: #60a5fa;
        }
        body.dark .form-actions .btn-secondary {
            background: #334155;
            color: #f1f5f9;
        }
        body.dark .form-actions .btn-secondary:hover {
            background: #2563eb;
            color: #fff;
        }
        body.dark .form-actions .btn-danger {
            background: #f87171;
            color: #fff;
        }
        body.dark .form-actions .btn-danger:hover {
            background: #dc2626;
        }
        /* Profile photo upload dark mode */
        body.dark .profile-photo-upload {
            background: #1e293b;
            border-color: #334155;
        }
        body.dark .profile-photo-upload:hover {
            background: #2563eb22;
            border-color: #60a5fa;
        }
        body.dark .upload-text {
            color: #cbd5e1;
        }
        body.dark .upload-hint {
            color: #334155;
        }
        body.dark .upload-progress {
            background: #334155;
        }
        body.dark .upload-status.success {
            background: #166534;
            color: #bbf7d0;
        }
        body.dark .upload-status.error {
            background: #7f1d1d;
            color: #fecaca;
        }
        /* Pagination dark mode */
        body.dark .pagination {
            border-top: 1px solid #334155;
        }
        body.dark .page-btn {
            background: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
        }
        body.dark .page-btn.active {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }
        body.dark .page-btn:hover:not(.active) {
            background: #334155;
            border-color: #60a5fa;
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