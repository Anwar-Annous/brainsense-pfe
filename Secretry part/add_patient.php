<?php
// Prevent any output before JSON response
ob_start();

// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Set JSON header
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/includes/db_connect.php';
    require_once __DIR__ . '/includes/functions.php';

    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = array('success' => false, 'message' => '');
        
        try {
            // Log the received POST data
            error_log("Received POST data: " . print_r($_POST, true));

            // Validate required fields
            $required_fields = ['full_name', 'email', 'phone', 'date_of_birth', 'gender'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields");
                }
            }

            // Sanitize and validate email
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email already exists
            $check_email = "SELECT id FROM patients WHERE email = ?";
            $stmt = mysqli_prepare($conn, $check_email);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Database error: " . mysqli_stmt_error($stmt));
            }
            
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                throw new Exception("Email already exists");
            }

            // Handle profile picture upload
            $profile_picture = '';
            if (isset($_FILES['profile_picture'])) {
                try {
                    // Check for upload errors
                    if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                        $error_message = match($_FILES['profile_picture']['error']) {
                            UPLOAD_ERR_INI_SIZE => "File is too large (exceeds php.ini limit)",
                            UPLOAD_ERR_FORM_SIZE => "File is too large (exceeds form limit)",
                            UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
                            UPLOAD_ERR_NO_FILE => "No file was uploaded",
                            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
                            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload",
                            default => "Unknown upload error"
                        };
                        throw new Exception("Upload error: " . $error_message);
                    }

                    $upload_dir = __DIR__ . '/../uploads/profiles/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            throw new Exception("Failed to create upload directory");
                        }
                    }

                    // Check if directory is writable
                    if (!is_writable($upload_dir)) {
                        throw new Exception("Upload directory is not writable");
                    }

                    // Get file extension
                    $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                    
                    // Basic file type check
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($file_extension, $allowed_types)) {
                        throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed_types));
                    }

                    // Generate unique filename
                    $new_filename = uniqid('profile_') . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        throw new Exception("Failed to move uploaded file");
                    }

                    $profile_picture = $new_filename;
                    
                } catch (Exception $e) {
                    error_log("Profile picture upload error: " . $e->getMessage());
                    throw new Exception("Profile picture upload failed: " . $e->getMessage());
                }
            }

            // Prepare the insert query
            $query = "INSERT INTO patients (
                full_name, email, phone, date_of_birth, gender, 
                address, emergency_contact, emergency_phone, 
                blood_type, allergies, profile_photo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($conn));
            }
            
            // Log the query and parameters
            error_log("Query: " . $query);
            error_log("Parameters: " . print_r([
                'full_name' => $_POST['full_name'],
                'email' => $email,
                'phone' => $_POST['phone'],
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'address' => $_POST['address'] ?? '',
                'emergency_contact' => $_POST['emergency_contact'] ?? '',
                'emergency_phone' => $_POST['emergency_phone'] ?? '',
                'blood_type' => $_POST['blood_type'] ?? '',
                'allergies' => $_POST['allergies'] ?? '',
                'profile_pic' => $profile_picture
            ], true));
            
            // Prepare variables for binding
            $full_name = $_POST['full_name'];
            $phone = $_POST['phone'];
            $date_of_birth = $_POST['date_of_birth'];
            $gender = $_POST['gender'];
            $address = $_POST['address'] ?? '';
            $emergency_contact = $_POST['emergency_contact'] ?? '';
            $emergency_phone = $_POST['emergency_phone'] ?? '';
            $blood_type = $_POST['blood_type'] ?? '';
            $allergies = $_POST['allergies'] ?? '';
            
            // Bind parameters
            if (!mysqli_stmt_bind_param($stmt, "sssssssssss",
                $full_name,
                $email,
                $phone,
                $date_of_birth,
                $gender,
                $address,
                $emergency_contact,
                $emergency_phone,
                $blood_type,
                $allergies,
                $profile_picture
            )) {
                throw new Exception("Error binding parameters: " . mysqli_stmt_error($stmt));
            }

            // Execute the query
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
            }

            $patient_id = mysqli_insert_id($conn);

            // Generate username from patient ID and name
            $username_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $full_name));
            $username = $username_base . $patient_id;

            // Generate a random password
            $password = bin2hex(random_bytes(4)); // 8 characters
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Create user account
            $user_query = "INSERT INTO users (
                username, password, email, full_name, role, 
                Profile, status, created_at
            ) VALUES (?, ?, ?, ?, 'patient', ?, 'active', NOW())";

            $user_stmt = mysqli_prepare($conn, $user_query);
            if (!$user_stmt) {
                throw new Exception("Error creating user account: " . mysqli_error($conn));
            }

            if (!mysqli_stmt_bind_param($user_stmt, "sssss",
                $username,
                $hashed_password,
                $email,
                $full_name,
                $profile_picture
            )) {
                throw new Exception("Error binding user parameters: " . mysqli_stmt_error($user_stmt));
            }

            if (!mysqli_stmt_execute($user_stmt)) {
                throw new Exception("Error creating user account: " . mysqli_stmt_error($user_stmt));
            }

            $response['success'] = true;
            $response['message'] = "Patient added successfully";
            $response['patient_id'] = $patient_id;
            $response['username'] = $username;
            $response['password'] = $password; // Send the generated password to display to the user

        } catch (Exception $e) {
            error_log("Error in add_patient.php: " . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Send JSON response
        echo json_encode($response);
        exit();
    }
} catch (Exception $e) {
    error_log("Critical error in add_patient.php: " . $e->getMessage());
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
    exit();
}

// ... rest of the file ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Patient - BrainSense</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <style>
        .add-patient {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--light);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
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

        .error-message {
            color: #dc3545;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .success-message {
            color: #28a745;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        /* Dark mode support */
        body.dark .form-container {
            background: var(--grey);
        }

        body.dark .form-group input,
        body.dark .form-group select,
        body.dark .form-group textarea {
            background: var(--dark);
            color: var(--light);
            border-color: var(--dark-grey);
        }

        body.dark .form-group label {
            color: var(--light);
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="index.php" class="brand">
            <img src="assets/images/Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
            <span class="Logo">BrainSense</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="active">
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
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
            </a>
            <a href="#" class="profile">
                <img src="assets/images/default-avatar.png" alt="Profile Picture">
                <span class="profile-name">Secretary</span>
            </a>
        </nav>

        <!-- MAIN CONTENT -->
        <main class="add-patient">
            <div class="head-title">
                <div class="left">
                    <h1>Add New Patient</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">BrainSense</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a href="patient_records.php">Patient Records</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Add New Patient</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-container">
                <form id="addPatientForm" onsubmit="submitForm(event)" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="medical_history">Medical History</label>
                        <textarea id="medical_history" name="medical_history"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact Name</label>
                        <input type="text" id="emergency_contact" name="emergency_contact">
                    </div>

                    <div class="form-group">
                        <label for="emergency_phone">Emergency Contact Phone</label>
                        <input type="tel" id="emergency_phone" name="emergency_phone">
                    </div>

                    <div class="form-group">
                        <label for="blood_type">Blood Type</label>
                        <select id="blood_type" name="blood_type">
                            <option value="">Select Blood Type</option>
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

                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea id="allergies" name="allergies"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="window.location.href='patient_records.php'">
                            <i class='bx bx-x'></i> Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class='bx bx-save'></i> Save Patient
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </section>

    <script>
        function submitForm(event) {
            event.preventDefault();
            
            const form = event.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
            
            // Get form data
            const formData = new FormData(form);
            
            // Send POST request
            fetch('add_patient.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Create message with credentials
                    const message = `Patient added successfully!\n\n` +
                        `Please save these credentials:\n` +
                        `Username: ${data.username}\n` +
                        `Password: ${data.password}\n\n` +
                        `The patient can use these credentials to log in to their account.`;
                    
                    // Show success message with credentials
                    alert(message);
                    
                    // Redirect to patient records page
                    window.location.href = 'patient_records.php';
                } else {
                    // Show error message
                    alert(data.message || 'Failed to add patient');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the patient');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        }
    </script>
</body>
</html> 