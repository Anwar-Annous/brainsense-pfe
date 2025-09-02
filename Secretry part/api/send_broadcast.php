<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'brainsense');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$audience = $data['audience'] ?? [];
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

if (!$audience || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$recipients = [];

if (in_array('doctors', $audience)) {
    $res = $conn->query("SELECT id FROM doctors");
    while ($row = $res->fetch_assoc()) {
        $recipients[] = ['id' => $row['id'], 'type' => 'doctor'];
    }
}
if (in_array('patients', $audience)) {
    $res = $conn->query("SELECT id FROM patients");
    while ($row = $res->fetch_assoc()) {
        $recipients[] = ['id' => $row['id'], 'type' => 'patient'];
    }
}

if (empty($recipients)) {
    echo json_encode(['success' => false, 'message' => 'No recipients found']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (subject, message, sender_id, sender_type, recipient_id, recipient_type, priority, status) VALUES (?, ?, 0, 'secretary', ?, ?, 'medium', 'unread')");
foreach ($recipients as $r) {
    $stmt->bind_param('ssis', $subject, $message, $r['id'], $r['type']);
    $stmt->execute();
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true]); 