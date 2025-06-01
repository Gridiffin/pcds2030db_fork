<?php


if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/controllers/DashboardController.php';

if (!is_agency()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$program_id = $_POST['program_id'] ?? null;

if (!$program_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid program ID']);
    exit;
}

try {
    $query = "UPDATE program_submissions SET is_draft = 0 WHERE program_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Program submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
