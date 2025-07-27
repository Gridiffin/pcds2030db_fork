<?php
/**
 * Edit KPI Outcome - Admin Version
 *
 * Dedicated page for editing KPI-type outcomes only.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';

if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

if ($outcome['type'] !== 'kpi') {
    $_SESSION['error_message'] = 'This page is only for editing KPI outcomes.';
    header('Location: manage_outcomes.php');
    exit;
}

// --- POST handling for saving KPI outcome ---
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $data = $_POST['data'] ?? [];
    // Basic validation
    if ($title === '') {
        $error_message = 'Title is required.';
    } else {
        // Use existing code and type
        $code = $outcome['code'];
        $type = $outcome['type'];
        // Sanitize data array
        $clean_data = [];
        foreach ($data as $item) {
            $clean_data[] = [
                'description' => trim($item['description'] ?? ''),
                'value' => trim($item['value'] ?? ''),
                'unit' => trim($item['unit'] ?? ''),
                'extra' => trim($item['extra'] ?? ''),
            ];
        }
        $result = update_outcome_full($outcome_id, $code, $type, $title, $description, $clean_data);
        if ($result) {
            $success_message = 'KPI outcome updated successfully.';
            // Refresh outcome data
            $outcome = get_outcome_by_id($outcome_id);
        } else {
            $error_message = 'Failed to update KPI outcome. Please try again.';
        }
    }
}

// Set up base layout variables
$pageTitle = 'Edit KPI Outcome';
$cssBundle = 'admin-outcomes'; // Vite bundle for admin outcomes
$jsBundle = 'admin-outcomes';

// Configure modern page header
$header_config = [
    'title' => 'Edit KPI Outcome',
    'subtitle' => 'Update KPI details and data',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/index.php?page=admin_dashboard'
        ],
        [
            'text' => 'Manage Outcomes',
            'url' => 'manage_outcomes.php'
        ],
        [
            'text' => 'Edit KPI',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'view_outcome.php?id=' . $outcome_id,
            'text' => 'View KPI',
            'icon' => 'fas fa-eye',
            'class' => 'btn-secondary'
        ]
    ]
];
// Set content file for base layout
$contentFile = __DIR__ . '/partials/edit_kpi_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
 