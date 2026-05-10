<?php
header('Content-Type: application/json');
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session. Session data: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
error_log("User ID from session: $user_id");
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_user_notifications') {
    error_log("Getting notifications for user_id: $user_id");

    // Get alerts that are shared with all users
    $sql_alerts = "SELECT a.id AS notification_id, a.id AS alert_id, a.topic, a.message, a.alert_type, 
                          a.status, 0 AS is_read, NULL AS read_at, a.created_at 
                   FROM alerts a 
                   WHERE a.status = 'active' 
                   ORDER BY a.created_at DESC 
                   LIMIT 100";

    // Get user-specific notifications
    $sql_user_notif = "SELECT n.id AS notification_id, n.alert_id, n.topic, n.message, 
                              '' AS alert_type, 'active' AS status, n.is_read, n.read_at, n.created_at 
                       FROM notifications n 
                       WHERE n.user_id = ? 
                       ORDER BY n.created_at DESC 
                       LIMIT 100";

    error_log("Fetching alerts and user notifications");

    $all_notifications = [];

    // Get alerts
    $result_alerts = $conn->query($sql_alerts);
    if ($result_alerts) {
        while ($row = $result_alerts->fetch_assoc()) {
            $all_notifications[] = $row;
        }
    } else {
        error_log("SQL Error fetching alerts: " . $conn->error);
    }

    // Get user notifications using prepared statement
    $stmt = $conn->prepare($sql_user_notif);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result_user = $stmt->get_result();
            while ($row = $result_user->fetch_assoc()) {
                $all_notifications[] = $row;
            }
            $stmt->close();
        } else {
            error_log("Prepared statement execution error: " . $stmt->error);
        }
    } else {
        error_log("Prepared statement creation error: " . $conn->error);
    }

    // Sort by created_at DESC and remove duplicates
    usort($all_notifications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    error_log("Found " . count($all_notifications) . " total notifications");
    echo json_encode(['success' => true, 'notifications' => array_slice($all_notifications, 0, 50)]);
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