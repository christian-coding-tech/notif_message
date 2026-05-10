<?php
header('Content-Type: application/json');
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if user is admin (you might want to add session check here)
    // For now, assuming admin access

    $sql = "SELECT id, username, email, birth_date, age, role, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    echo json_encode(['success' => true, 'users' => $users]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>