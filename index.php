<?php
/**
 * Main entry point for the PCDS2030 Dashboard
 * 
 * This file serves as the landing page for the application.
 * It checks authentication and redirects to appropriate dashboard based on user role.
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

// Check if user is logged in, if not redirect to login
if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Get user role and redirect to appropriate dashboard
$role = get_user_role();

if ($role === 'admin') {
    header("Location: views/admin/dashboard.php");
    exit;
} else if ($role === 'agency') {
    header("Location: views/agency/dashboard.php");
    exit;
} else {
    // Invalid role or session corruption, destroy session and redirect to login
    session_destroy();
    header("Location: login.php?error=invalid_session");
    exit;
}

// This part should never execute due to the redirects above
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="jumbotron">
            <h1>Welcome to <?php echo APP_NAME; ?></h1>
            <p>Loading your dashboard...</p>
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3">If you are not redirected automatically, please click the appropriate link below:</p>
            <a href="views/admin/dashboard.php" class="btn btn-primary">Admin Dashboard</a>
            <a href="views/agency/dashboard.php" class="btn btn-secondary">Agency Dashboard</a>
        </div>
    </div>
</body>
</html>
