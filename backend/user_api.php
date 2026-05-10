<?php
header('Content-Type: application/json');
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_profile') {
    $sql = "SELECT id, username, email, avatar, role FROM users WHERE id=$user_id";
    $result = $conn->query($sql);
    $user = $result ? $result->fetch_assoc() : null;
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_profile') {
    $username = trim($conn->real_escape_string($_POST['username'] ?? ''));
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username and email are required']);
        exit;
    }

    $check = $conn->query("SELECT id FROM users WHERE (username='$username' OR email='$email') AND id <> $user_id");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already in use']);
        exit;
    }

    $avatarPath = null;
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        // Check file size
        if ($_FILES['avatar']['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 2MB limit']);
            exit;
        }

        $uploadDir = realpath(__DIR__ . '/../uploads/avatars');
        if (!$uploadDir) {
            mkdir(__DIR__ . '/../uploads/avatars', 0755, true);
            $uploadDir = realpath(__DIR__ . '/../uploads/avatars');
        }

        $fileName = basename($_FILES['avatar']['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and GIF images are allowed']);
            exit;
        }

        $newFileName = uniqid('avatar_') . '.' . $extension;
        $targetFile = $uploadDir . '/' . $newFileName;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload avatar image']);
            exit;
        }

        $avatarPath = 'uploads/avatars/' . $newFileName;

        $oldUser = $conn->query("SELECT avatar FROM users WHERE id=$user_id");
        if ($oldUser && $oldUser->num_rows > 0) {
            $oldRow = $oldUser->fetch_assoc();
            if (!empty($oldRow['avatar']) && file_exists(__DIR__ . '/../' . $oldRow['avatar'])) {
                @unlink(__DIR__ . '/../' . $oldRow['avatar']);
            }
        }
    }

    $fields = [
        "username='$username'",
        "email='$email'"
    ];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $fields[] = "password='$hashedPassword'";
    }

    if ($avatarPath) {
        $fields[] = "avatar='$avatarPath'";
    }

    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id=$user_id";

    if ($conn->query($sql)) {
        $_SESSION['username'] = $username;
        if ($avatarPath) {
            $_SESSION['avatar'] = $avatarPath;
        }
        $resultUser = $conn->query("SELECT id, username, email, avatar, role FROM users WHERE id=$user_id");
        $updatedUser = $resultUser ? $resultUser->fetch_assoc() : null;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'user' => $updatedUser]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
