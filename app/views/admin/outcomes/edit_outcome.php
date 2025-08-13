<?php
/**
 * Edit Outcome Details - Admin Version
 * 
 * Admin interface to edit outcome details with support for flexible table structures
 * Based on working agency implementation
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: log the full POST data
    file_put_contents(__DIR__ . '/debug_post_data.log', "\n==== " . date('Y-m-d H:i:s') . " FULL POST ====\n" . print_r($_POST, true), FILE_APPEND);
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $post_data = [];
    if (isset($_POST['data'])) {
        $decoded = json_decode($_POST['data'], true);
        if (is_array($decoded)) {
            $post_data = $decoded;
        }
    }
    // Debug: log the decoded data
    file_put_contents(__DIR__ . '/debug_post_data.log', "Decoded: " . print_r($post_data, true), FILE_APPEND);
    require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';
    $update_result = update_outcome_full($outcome_id, $code, $type, $title, $description, $post_data);
    // Debug: log the result of the update
    file_put_contents(__DIR__ . '/debug_post_data.log', "Update result: " . print_r($update_result, true), FILE_APPEND);
    if ($update_result) {
        header('Location: view_outcome.php?id=' . $outcome_id . '&saved=1');
        exit;
    } else {
        $message = 'Error updating outcome.';
        $message_type = 'danger';
    }
}

// Set up base layout variables
$pageTitle = 'Edit Outcome';
$cssBundle = 'admin-outcomes'; // Vite bundle for admin outcomes
$jsBundle = 'admin-outcomes';

// Configure modern page header
$is_draft = isset($outcome['is_draft']) ? $outcome['is_draft'] : 0;
$header_config = [
    'title' => 'Edit Outcome',
    'subtitle' => 'Edit existing outcome with dynamic table structure' . ($is_draft ? ' (Draft)' : ' (Submitted)'),
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
            'text' => 'Edit Outcome',
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
            'text' => 'View Outcome',
            'icon' => 'fas fa-eye',
            'class' => 'btn-outline-info'
        ],
        [
            'html' => '<span class="badge ' . ($is_draft ? 'bg-warning text-dark' : 'bg-success') . '"><i class="fas ' . ($is_draft ? 'fa-edit' : 'fa-check') . ' me-1"></i>' . ($is_draft ? 'Draft' : 'Submitted') . '</span>'
        ]
    ]
];
// Set content file for base layout
$contentFile = __DIR__ . '/partials/edit_outcome_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';