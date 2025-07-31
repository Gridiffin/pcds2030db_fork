<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Add Reporting Period AJAX Handler
 */

header('Content-Type: application/json');

// Define the project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['period_type', 'period_number', 'year', 'start_date', 'end_date', 'status'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo json_encode(['success' => false, 'message' => "Field {$field} is required"]);
        exit;
    }
}

$period_type = trim($_POST['period_type']);
$period_number = intval($_POST['period_number']);
$year = intval($_POST['year']);
$start_date = trim($_POST['start_date']);
$end_date = trim($_POST['end_date']);
$status = trim($_POST['status']);

// Validate period type and number
$valid_types = ['quarter', 'half', 'yearly'];
if (!in_array($period_type, $valid_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid period type']);
    exit;
}

// Validate period number based on type
if ($period_type === 'quarter' && ($period_number < 1 || $period_number > 4)) {
    echo json_encode(['success' => false, 'message' => 'Quarter period number must be between 1 and 4']);
    exit;
}
if ($period_type === 'half' && ($period_number < 1 || $period_number > 2)) {
    echo json_encode(['success' => false, 'message' => 'Half yearly period number must be between 1 and 2']);
    exit;
}
if ($period_type === 'yearly' && $period_number !== 1) {
    echo json_encode(['success' => false, 'message' => 'Yearly period number must be 1']);
    exit;
}

// Validate year
if ($year < 2020 || $year > 2030) {
    echo json_encode(['success' => false, 'message' => 'Year must be between 2020 and 2030']);
    exit;
}

// Validate status
if (!in_array($status, ['open', 'closed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

if (strtotime($start_date) >= strtotime($end_date)) {
    echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
    exit;
}

try {
    // Check if this period already exists
    $check_stmt = $pdo->prepare("SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = ? AND period_number = ?");
    $check_stmt->execute([$year, $period_type, $period_number]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A period with this type, number, and year already exists']);
        exit;
    }
    
    // Insert the new period
    $stmt = $pdo->prepare("INSERT INTO reporting_periods (year, period_type, period_number, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$year, $period_type, $period_number, $start_date, $end_date, $status]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Period added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add period']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in add_period.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>