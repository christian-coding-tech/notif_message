<?php
header('Content-Type: application/json');
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_user_notifications') {
    $sql = "SELECT n.id AS notification_id, a.id AS alert_id, a.topic, a.message, a.alert_type, a.status, COALESCE(n.is_read, 0) AS is_read, n.read_at, a.created_at " .
           "FROM alerts a LEFT JOIN notifications n ON a.id = n.alert_id AND n.user_id = $user_id " .
           "WHERE a.status = 'active' " .
           "UNION ALL " .
           "SELECT n.id AS notification_id, n.alert_id, n.topic, n.message, '' AS alert_type, 'active' AS status, n.is_read, n.read_at, n.created_at " .
           "FROM notifications n WHERE n.user_id = $user_id AND (n.alert_id IS NULL OR n.alert_id = 0) " .
           "ORDER BY created_at DESC";

    $result = $conn->query($sql);
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_notification') {
    $data = json_decode(file_get_contents("php://input"), true);
    $alert_id = intval($data['alert_id'] ?? 0);
    $topic = $conn->real_escape_string($data['topic'] ?? '');
    $message = $conn->real_escape_string($data['message'] ?? '');

    if (empty($topic) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Topic and message are required']);
        exit;
    }

    $sql_check = $alert_id > 0
        ? "SELECT id FROM notifications WHERE user_id=$user_id AND alert_id=$alert_id"
        : "SELECT id FROM notifications WHERE user_id=$user_id AND alert_id IS NULL";
    $result = $conn->query($sql_check);

    if ($result && $result->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification already saved']);
        exit;
    }

    if ($alert_id > 0) {
        $sql = "INSERT INTO notifications (user_id, alert_id, topic, message) VALUES ($user_id, $alert_id, '$topic', '$message')";
    } else {
        $sql = "INSERT INTO notifications (user_id, alert_id, topic, message) VALUES ($user_id, NULL, '$topic', '$message')";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Notification saved', 'notification_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_read') {
    $data = json_decode(file_get_contents("php://input"), true);
    $notification_id = intval($data['notification_id'] ?? 0);
    $alert_id = intval($data['alert_id'] ?? 0);
    $is_read = isset($data['is_read']) ? intval($data['is_read']) : 1;

    if ($notification_id > 0) {
        $read_at = $is_read ? 'NOW()' : 'NULL';
        $sql = "UPDATE notifications SET is_read=$is_read, read_at=$read_at WHERE id=$notification_id AND user_id=$user_id";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Read status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        $read_at = $is_read ? 'NOW()' : 'NULL';
        if ($alert_id > 0) {
            $sql = "INSERT INTO notifications (user_id, alert_id, topic, message, is_read, read_at) VALUES ($user_id, $alert_id, '', '', $is_read, $read_at)";
        } else {
            $sql = "INSERT INTO notifications (user_id, alert_id, topic, message, is_read, read_at) VALUES ($user_id, NULL, '', '', $is_read, $read_at)";
        }
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Notification created and read status set']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>