<?php
/**
 * Test Data Insertion Script
 * This script inserts sample notifications into the database for testing purposes
 * Run this once to populate the database with test data
 * 
 * Access via: http://localhost/notif/backend/test_data.php
 */

header('Content-Type: application/json');
include '../config/db.php';
session_start();

// Check if we should seed data (via GET parameter)
$action = $_GET['action'] ?? '';

if ($action === 'seed') {
    echo json_encode(['status' => 'starting', 'message' => 'Seeding test data...']);
    
    // Get or create test user
    $test_username = 'testuser';
    $test_email = 'test@example.com';
    $test_password = password_hash('testuser123', PASSWORD_DEFAULT);
    
    // Check if user exists
    $check_user = $conn->query("SELECT id FROM users WHERE username='$test_username'");
    
    if ($check_user && $check_user->num_rows > 0) {
        $user_row = $check_user->fetch_assoc();
        $user_id = $user_row['id'];
        echo json_encode(['status' => 'info', 'message' => 'Test user already exists (ID: ' . $user_id . ')']);
    } else {
        // Create test user
        $insert_user = "INSERT INTO users (username, email, password, role) 
                       VALUES ('$test_username', '$test_email', '$test_password', 'user')";
        if ($conn->query($insert_user)) {
            $user_id = $conn->insert_id;
            echo json_encode(['status' => 'success', 'message' => 'Test user created (ID: ' . $user_id . ')']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user: ' . $conn->error]);
            exit;
        }
    }
    
    // Insert test alerts
    $alerts = [
        ['topic' => 'school/announcements', 'message' => 'School assembly tomorrow at 10:00 AM', 'type' => 'announcement'],
        ['topic' => 'school/announcements', 'message' => 'Upcoming exams schedule released', 'type' => 'announcement'],
        ['topic' => 'system/alerts', 'message' => 'Server maintenance scheduled for this weekend', 'type' => 'warning'],
        ['topic' => 'system/alerts', 'message' => 'Database backup completed successfully', 'type' => 'announcement'],
        ['topic' => 'weather/alerts', 'message' => 'Heavy rain expected tomorrow afternoon', 'type' => 'warning'],
        ['topic' => 'weather/alerts', 'message' => 'Temperature will drop below freezing tonight', 'type' => 'warning'],
    ];
    
    $admin_id = 1; // Assuming admin user has ID 1
    $inserted_count = 0;
    
    foreach ($alerts as $alert) {
        $stmt = $conn->prepare("INSERT INTO alerts (admin_id, topic, message, alert_type, status) 
                               VALUES (?, ?, ?, ?, 'active')");
        if ($stmt) {
            $stmt->bind_param('isss', $admin_id, $alert['topic'], $alert['message'], $alert['type']);
            if ($stmt->execute()) {
                $alert_id = $stmt->insert_id;
                
                // Create notification for test user
                $stmt2 = $conn->prepare("INSERT INTO notifications (user_id, alert_id, topic, message) 
                                        VALUES (?, ?, ?, ?)");
                if ($stmt2) {
                    $stmt2->bind_param('iiss', $user_id, $alert_id, $alert['topic'], $alert['message']);
                    $stmt2->execute();
                    $stmt2->close();
                    $inserted_count++;
                }
            }
            $stmt->close();
        }
    }
    
    // Insert direct user notifications (not linked to alerts)
    $direct_notifs = [
        ['topic' => 'messages', 'message' => 'You have a new message from John Doe'],
        ['topic' => 'reminders', 'message' => 'Reminder: Complete your profile setup'],
    ];
    
    foreach ($direct_notifs as $notif) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, topic, message) 
                               VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('iss', $user_id, $notif['topic'], $notif['message']);
            if ($stmt->execute()) {
                $inserted_count++;
            }
            $stmt->close();
        }
    }
    
    echo json_encode([
        'status' => 'complete',
        'message' => 'Test data inserted successfully',
        'details' => [
            'user_id' => $user_id,
            'username' => $test_username,
            'password' => 'testuser123',
            'notifications_created' => $inserted_count
        ]
    ]);
    
} else if ($action === 'clear') {
    // Clear test data
    $test_username = 'testuser';
    $delete_result = $conn->query("DELETE FROM users WHERE username='$test_username'");
    
    if ($delete_result) {
        echo json_encode(['status' => 'success', 'message' => 'Test data cleared']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to clear test data']);
    }
    
} else if ($action === 'status') {
    // Show status
    $test_username = 'testuser';
    $check = $conn->query("SELECT id FROM users WHERE username='$test_username'");
    
    if ($check && $check->num_rows > 0) {
        $user = $check->fetch_assoc();
        $user_id = $user['id'];
        
        $notif_check = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id=$user_id");
        $notif_count = $notif_check->fetch_assoc()['count'];
        
        echo json_encode([
            'status' => 'exists',
            'user_id' => $user_id,
            'username' => $test_username,
            'notifications_count' => $notif_count
        ]);
    } else {
        echo json_encode(['status' => 'not_found', 'message' => 'Test user does not exist']);
    }
    
} else {
    // Show instructions
    echo json_encode([
        'instructions' => [
            '1. Seed test data: ' . $_SERVER['REQUEST_URI'] . '?action=seed',
            '2. Check status: ' . $_SERVER['REQUEST_URI'] . '?action=status',
            '3. Clear test data: ' . $_SERVER['REQUEST_URI'] . '?action=clear',
            'Login with: username=testuser, password=testuser123'
        ]
    ]);
}
?>
