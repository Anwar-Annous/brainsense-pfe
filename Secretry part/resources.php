<?php
// resources.php - BrainSense Resources Page
require_once 'includes/db_connect.php';

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - BrainSense</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e3f2fd 0%, #f8fafc 100%);
        }
        body.dark {
            background: linear-gradient(135deg, #1e293b 0%, #111827 100%);
        }
        #content main {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 0;
        }
        .hero-resource {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            text-align: center;
            padding: 3rem 1rem 2rem 1rem;
            background: none;
            min-height: 70vh;
        }
        .hero-resource h1 {
            font-size: 2.8rem;
            font-family: var(--font-heading, 'Poppins', sans-serif);
            color: var(--blue);
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 1rem;
            text-shadow: 0 4px 32px #4a90e230;
        }
        body.dark .hero-resource h1 {
            color: #60a5fa;
            text-shadow: 0 4px 32px #2563eb44;
        }
        .hero-resource p {
            font-size: 1.25rem;
            color: var(--dark-grey);
            margin-bottom: 2.5rem;
            max-width: 600px;
        }
        body.dark .hero-resource p {
            color: #cbd5e1;
        }
        .glow-card {
            background: rgba(255,255,255,0.95);
            border-radius: 18px;
            box-shadow: 0 0 32px 0 #4a90e250, 0 2px 8px #0001;
            padding: 2.5rem 2rem;
            margin: 0 auto 2.5rem auto;
            max-width: 420px;
            position: relative;
            overflow: hidden;
            border: 2px solid #e3f2fd;
            transition: box-shadow 0.3s, background 0.3s, border 0.3s;
        }
        body.dark .glow-card {
            background: rgba(30,41,59,0.98);
            border: 2px solid #334155;
            box-shadow: 0 0 32px 0 #2563eb60, 0 2px 8px #0003;
        }
        .glow-card::before {
            content: '';
            position: absolute;
            top: -40px; left: -40px; right: -40px; bottom: -40px;
            background: radial-gradient(circle, #4a90e220 0%, transparent 70%);
            z-index: 0;
        }
        body.dark .glow-card::before {
            background: radial-gradient(circle, #2563eb33 0%, transparent 70%);
        }
        .glow-card .bx {
            font-size: 3.5rem;
            color: var(--blue);
            margin-bottom: 1rem;
            z-index: 1;
            position: relative;
            filter: drop-shadow(0 0 12px #4a90e2aa);
        }
        body.dark .glow-card .bx {
            color: #60a5fa;
            filter: drop-shadow(0 0 16px #2563ebcc);
        }
        .glow-card h2 {
            font-size: 1.5rem;
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.75rem;
            z-index: 1;
            position: relative;
        }
        body.dark .glow-card h2 {
            color: #f1f5f9;
        }
        .glow-card p {
            color: var(--dark-grey);
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
            z-index: 1;
            position: relative;
        }
        body.dark .glow-card p {
            color: #cbd5e1;
        }
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: linear-gradient(90deg, #4a90e2 60%, #6ee7b7 100%);
            color: #fff;
            font-weight: 600;
            font-size: 0.98rem;
            padding: 0.55rem 1.3rem;
            border-radius: 999px;
            box-shadow: 0 4px 24px #4a90e250;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            z-index: 1;
            position: relative;
        }
        .cta-btn:hover {
            background: linear-gradient(90deg, #2563eb 60%, #22d3ee 100%);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 8px 32px #4a90e280;
        }
        body.dark .cta-btn {
            background: linear-gradient(90deg, #2563eb 60%, #22d3ee 100%);
            color: #fff;
        }
        .cta-btn .bx {
            font-size: 1.1rem;
        }
        .epic-wave {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            width: 100%;
            height: 110px;
            pointer-events: none;
            z-index: 0;
        }
        @media (max-width: 600px) {
            .hero-resource h1 { font-size: 2rem; }
            .glow-card { padding: 1.5rem 0.5rem; }
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
            background: var(--light);
            color: var(--dark);
        }

        body.dark .profile:hover {
            background: var(--grey);
        }

        body.dark .profile-name {
            color: var(--dark);
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
    <section id="sidebar">
        <a href="index.php" class="brand">
            <img src="Logo(BrainSense )png.png" width="70px" alt="BrainSense Logo">
            <span class="Logo">BrainSense</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="index.php">
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
            <li class="active">
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
        <main style="min-height: 100vh; position:relative;" class="communication-hub">
            <div class="hero-resource">
                <h1><i class='bx bx-rocket'></i> BrainSense Resources</h1>
                <p>Unlock the full power of BrainSense. <br>Access our most essential resource in one click.</p>
                <div class="glow-card">
                    <i class='bx bx-bulb'></i>
                    <h2>Ultimate Quickstart</h2>
                    <p>Download our beautifully designed, illustrated PDF to master BrainSense in minutes.<br><span style="color:#4a90e2;font-weight:600;">No registration. No hassle.</span></p>
                    <a href="#" class="cta-btn"><i class='bx bx-download'></i> Download Now</a>
                </div>
            </div>
            <svg class="epic-wave" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#4a90e2" fill-opacity="0.08" d="M0,80 C360,160 1080,0 1440,80 L1440,120 L0,120 Z"></path></svg>
        </main>
    </section>
    <script src="script.js"></script>
</body>
</html> 