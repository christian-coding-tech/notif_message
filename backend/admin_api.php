<?php
header('Content-Type: application/json');
include '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Check if user is admin
$check_admin = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $check_admin->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only admins can perform this action']);
    exit;
}

$action = $_GET['action'] ?? '';

// GET ALERTS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_alerts') {
    $status = $_GET['status'] ?? 'all';
    
    if ($status === 'all') {
        $sql = "SELECT a.*, u.username FROM alerts a JOIN users u ON a.admin_id = u.id ORDER BY a.created_at DESC";
    } else {
        $sql = "SELECT a.*, u.username FROM alerts a JOIN users u ON a.admin_id = u.id WHERE a.status = '$status' ORDER BY a.created_at DESC";
    }
    
    $result = $conn->query($sql);
    $alerts = [];
    
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }
    
    echo json_encode(['success' => true, 'alerts' => $alerts]);
    exit;
}

// GET ALERT READERS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_alert_readers') {
    $alert_id = intval($_GET['alert_id'] ?? 0);
    if ($alert_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid alert id']);
        exit;
    }

    $sql = "SELECT u.username, n.is_read, n.read_at, n.created_at FROM notifications n JOIN users u ON n.user_id = u.id WHERE n.alert_id=$alert_id ORDER BY n.read_at DESC, n.created_at DESC";
    $result = $conn->query($sql);
    $readers = [];
    while ($row = $result->fetch_assoc()) {
        $readers[] = $row;
    }

    echo json_encode(['success' => true, 'readers' => $readers]);
    exit;
}

// CREATE ALERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_alert') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $topic = $conn->real_escape_string($data['topic']);
    $message = $conn->real_escape_string($data['message']);
    $alert_type = $conn->real_escape_string($data['alert_type'] ?? 'announcement');
    
    if (empty($topic) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Topic and message are required']);
        exit;
    }
    
    $sql = "INSERT INTO alerts (admin_id, topic, message, alert_type) 
            VALUES ($user_id, '$topic', '$message', '$alert_type')";
    
    if ($conn->query($sql)) {
        $alert_id = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Alert created successfully', 'alert_id' => $alert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// DELETE ALERT
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'delete_alert') {
    $data = json_decode(file_get_contents("php://input"), true);
    $alert_id = intval($data['alert_id']);
    
    // Verify the alert belongs to the current admin
    $check = $conn->query("SELECT id FROM alerts WHERE id=$alert_id AND admin_id=$user_id");
    
    if ($check->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only delete your own alerts']);
        exit;
    }
    
    $sql = "DELETE FROM alerts WHERE id=$alert_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Alert deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// UPDATE ALERT STATUS
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $action === 'update_alert_status') {
    $data = json_decode(file_get_contents("php://input"), true);
    $alert_id = intval($data['alert_id']);
    $status = $conn->real_escape_string($data['status']);
    
    $check = $conn->query("SELECT id FROM alerts WHERE id=$alert_id AND admin_id=$user_id");
    
    if ($check->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only update your own alerts']);
        exit;
    }
    
    $sql = "UPDATE alerts SET status='$status' WHERE id=$alert_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Alert status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// GET USER INFO
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_user_info') {
    $sql = "SELECT id, username, role FROM users WHERE id=$user_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
