<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/UserModel.php';
require_once __DIR__ . '/../lib/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userModel = new UserModel($conn);
    $user = $userModel->findByUsername($data['username']);
    if ($user && $userModel->verifyPassword($user, $data['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['agency_id'] = $user['agency_id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']); 