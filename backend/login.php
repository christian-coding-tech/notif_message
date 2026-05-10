<?php
header('Content-Type: application/json');
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = $conn->real_escape_string($data['username']);
    $password = $data['password'];
    
    // Check user exists
    $sql = "SELECT id, password, role FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    // Set session
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];
    
    echo json_encode(['success' => true, 'message' => 'Login successful', 'user_id' => $user['id'], 'username' => $username, 'role' => $user['role']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
