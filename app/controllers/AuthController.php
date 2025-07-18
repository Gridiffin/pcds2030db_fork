<?php
require_once __DIR__ . '/../lib/UserModel.php';
require_once __DIR__ . '/../lib/db_connect.php';

class AuthController {
    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new UserModel($conn);
            $user = $userModel->findByUsername($_POST['username']);
            if ($user && $userModel->verifyPassword($user, $_POST['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['agency_id'] = $user['agency_id'];
                header('Location: /app/views/' . ($user['role'] === 'admin' ? 'admin' : 'agency') . '/dashboard/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
        $pageTitle = 'Login';
        $contentFile = __DIR__ . '/../views/admin/partials/login_form.php';
        include __DIR__ . '/../views/layouts/base.php';
    }
} 