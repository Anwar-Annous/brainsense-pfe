<?php
require_once __DIR__ . '/utils.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize user ID if not set
if (!isset($id_user)) {
    error_log('Warning: $id_user not set in navbar.php, using default value');
    $id_user = $_SESSION['user_id'] ?? null; // Get user ID from session
}

// Get doctor's profile information
try {
    if (!isset($conn)) {
        throw new Exception('Database connection not available in navbar.php');
    }

    $doctorQuery = "SELECT profile FROM users WHERE id = ? AND role = 'doctor'";
    $stmt = $conn->prepare($doctorQuery);
    if (!$stmt) {
        throw new Exception('Failed to prepare doctor query: ' . $conn->errorInfo()[2]);
    }
    
    $stmt->execute([$id_user]);
    $doctorProfile = $stmt->fetch(PDO::FETCH_ASSOC);
    $profileImage = $doctorProfile['profile'] ?? 'assets/images/default-profile.jpg';
} catch (Exception $e) {
    error_log('Error in navbar.php - Doctor profile: ' . $e->getMessage());
    $profileImage = 'assets/images/default-profile.jpg';
}

// Get unread messages count
try {
    $unreadMessagesQuery = "SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND status = 'unread'";
    $stmt = $conn->prepare($unreadMessagesQuery);
    if (!$stmt) {
        throw new Exception('Failed to prepare messages query: ' . $conn->errorInfo()[2]);
    }
    
    $stmt->execute([$id_user]);
    $unreadMessages = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (Exception $e) {
    error_log('Error in navbar.php - Unread messages: ' . $e->getMessage());
    $unreadMessages = 0;
}

// Debug information
if (isset($_GET['debug'])) {
    echo '<div style="background: #e3f2fd; color: #1565c0; padding: 1rem; margin: 1rem; border-radius: 4px; border: 1px solid #90caf9;">';
    echo '<strong>Debug Information:</strong><br>';
    echo 'User ID: ' . htmlspecialchars($id_user) . '<br>';
    echo 'Profile Image: ' . htmlspecialchars($profileImage) . '<br>';
    echo 'Unread Messages: ' . htmlspecialchars($unreadMessages) . '<br>';
    echo 'Database Connection: ' . (isset($conn) ? 'Available' : 'Not Available') . '<br>';
    echo '</div>';
}
?>


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
    <div class="notification-container">
        <a href="#" class="notification" id="notificationBtn">
            <i class='bx bxs-bell'></i>
            <span class="num"><?php echo $unreadMessages; ?></span>
        </a>
        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
                <h4>Recent Messages</h4>
                <a href="messages.php" class="view-all">View All</a>
            </div>
            <div class="notification-list">
                <?php
                try {
                    // Get the last two messages
                    $stmt = $conn->prepare("
                        SELECT m.*, p.full_name as patient_name 
                        FROM messages m 
                        LEFT JOIN patients p ON m.sender_id = p.id 
                        WHERE m.recipient_id = ? AND m.recipient_type = 'doctor'
                        ORDER BY m.created_at DESC 
                        LIMIT 2
                    ");
                    $stmt->execute([$id_user]);
                    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($recentMessages) > 0) {
                        foreach ($recentMessages as $msg) {
                            $timeAgo = time_elapsed_string($msg['created_at']);
                            $priorityClass = $msg['priority'] ?? 'medium';
                            ?>
                            <div class="notification-item <?php echo ($msg['status'] ?? '') === 'unread' ? 'unread' : ''; ?>">
                                <div class="notification-content">
                                    <div class="notification-title">
                                        <span class="patient-name"><?php echo htmlspecialchars($msg['patient_name'] ?? 'Unknown Patient'); ?></span>
                                        <span class="time"><?php echo $timeAgo; ?></span>
                                    </div>
                                    <p class="message-preview"><?php echo htmlspecialchars(substr($msg['subject'] ?? 'No subject', 0, 50)) . '...'; ?></p>
                                    <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo ucfirst($priorityClass); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-notifications">No recent messages</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="no-notifications">Unable to load messages</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <a href="#" class="profile">
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Picture">
    </a>
</nav>
<!-- NAVBAR -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (notificationBtn && notificationDropdown) {
        // Toggle notification dropdown
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
            }
        });

        // Prevent dropdown from closing when clicking inside it
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && notificationDropdown.classList.contains('active')) {
                notificationDropdown.classList.remove('active');
            }
        });
    }
});
</script>

<style>
/* Navbar Styles */
nav {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 2rem;
    background: var(--light);
    border-bottom: 1px solid var(--grey);
}

nav i {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--dark);
}

.nav-link {
    color: var(--dark);
    text-decoration: none;
    font-weight: 500;
}

.form-input {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--light);
    border: 1px solid var(--grey);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    margin-left: auto;
}

.form-input input {
    border: none;
    outline: none;
    background: none;
    width: 200px;
    color: var(--dark);
}

.search-btn {
    background: none;
    border: none;
    color: var(--grey);
    cursor: pointer;
}

/* Notification Styles */
.notification-container {
    position: relative;
}

.notification {
    position: relative;
    color: var(--dark);
    text-decoration: none;
    font-size: 1.5rem;
}

.notification .num {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--red);
    color: var(--light);
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 50%;
    min-width: 18px;
    text-align: center;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 300px;
    background: var(--light);
    border: 1px solid var(--grey);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
    margin-top: 0.5rem;
}

.notification-dropdown.active {
    display: block;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--grey);
}

.notification-header h4 {
    margin: 0;
    color: var(--dark);
}

.view-all {
    color: var(--blue);
    text-decoration: none;
    font-size: 0.875rem;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid var(--grey);
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: var(--grey);
}

.notification-item.unread {
    background-color: rgba(25, 118, 210, 0.05);
}

.notification-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.notification-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.patient-name {
    font-weight: 500;
    color: var(--dark);
}

.time {
    font-size: 0.75rem;
    color: var(--grey);
}

.message-preview {
    margin: 0;
    font-size: 0.875rem;
    color: var(--dark);
}

.priority-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-badge.high {
    background-color: #ffebee;
    color: #c62828;
}

.priority-badge.medium {
    background-color: #fff3e0;
    color: #ef6c00;
}

.priority-badge.low {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.no-notifications {
    padding: 1rem;
    text-align: center;
    color: var(--grey);
}

/* Profile Styles */
.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Dark Mode Support */
body.dark nav {
    background: var(--dark);
    border-color: var(--grey);
}

body.dark nav i,
body.dark .nav-link {
    color: var(--light);
}

body.dark .form-input {
    background: var(--dark);
    border-color: var(--grey);
}

body.dark .form-input input {
    color: var(--light);
    background: var(--dark);
}

body.dark .notification-dropdown {
    background: var(--light);
}

body.dark .notification-header {
    border-color: var(--grey);
}

body.dark .notification-header h4 {
    color: white;
}

body.dark .notification-item {
    border-color: var(--grey);
}

body.dark .notification-item:hover {
    background: rgba(255,255,255,0.05);
}

body.dark .notification-item.unread {
    background: rgba(25, 118, 210, 0.1);
}

body.dark .patient-name,
body.dark .message-preview {
    color: white;
}

body.dark .time {
    color: var(--grey);
}

/* Responsive Adjustments */
@media screen and (max-width: 768px) {
    nav {
        padding: 1rem;
    }

    .form-input {
        display: none;
    }

    .notification-dropdown {
        position: fixed;
        top: 60px;
        right: 0;
        left: 0;
        width: 100%;
        border-radius: 0;
        margin-top: 0;
    }
}
</style> 