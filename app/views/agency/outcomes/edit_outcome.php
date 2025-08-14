<?php
/**
 * Edit Outcome Details - Agency Version
 * 
 * Agency interface to edit outcome details with support for flexible table structures
 * Based on working admin implementation
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/outcomes.php';

// Verify user is an agency
if (!is_agency()) {
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
    header('Location: submit_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: submit_outcomes.php');
    exit;
}

// Ensure this is not a KPI outcome (KPI outcomes use edit_kpi.php)
if ($outcome['type'] === 'kpi') {
    $_SESSION['error_message'] = 'KPI outcomes should be edited using the KPI edit page.';
    header('Location: edit_kpi.php?id=' . $outcome_id);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCode = isset($_POST['code']) ? trim($_POST['code']) : '';
    $postedType = isset($_POST['type']) ? trim($_POST['type']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $post_data = [];
    if (isset($_POST['data'])) {
        $decoded = json_decode($_POST['data'], true);
        if (is_array($decoded)) {
            $post_data = $decoded;
        }
    }

    // Fallback to existing values if not provided
    $code = $postedCode !== '' ? $postedCode : (isset($outcome['code']) ? $outcome['code'] : '');
    $type = $postedType !== '' ? $postedType : (isset($outcome['type']) ? $outcome['type'] : '');

    // Sanitize type against enum
    $allowedTypes = ['graph', 'kpi'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = isset($outcome['type']) ? $outcome['type'] : 'graph';
    }

    $update_result = update_outcome_full($outcome_id, $code, $type, $title, $description, $post_data);
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
$cssBundle = 'outcomes'; // CSS bundle for outcomes module
$jsBundle = 'agency-edit-outcomes';
$bodyClass = 'outcomes-page';

// Configure modern page header
$is_draft = isset($outcome['is_draft']) ? $outcome['is_draft'] : 0;
$header_config = [
    'title' => 'Edit Outcome',
    'subtitle' => 'Edit existing outcome with dynamic table structure' . ($is_draft ? ' (Draft)' : ' (Submitted)'),
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/index.php?page=agency_dashboard'
        ],
        [
            'text' => 'Outcomes',
            'url' => 'submit_outcomes.php'
        ],
        [
            'text' => 'Edit Outcome',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => [
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-light'
        ],
        [
            'url' => 'view_outcome.php?id=' . $outcome_id,
            'text' => 'View Outcome',
            'icon' => 'fas fa-eye',
            'class' => 'btn-light'
        ],
        [
            'html' => '<span class="badge ' . ($is_draft ? 'bg-warning text-dark' : 'bg-success') . '"><i class="fas ' . ($is_draft ? 'fa-edit' : 'fa-check') . ' me-1"></i>' . ($is_draft ? 'Draft' : 'Submitted') . '</span>'
        ]
    ]
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/edit_outcome_content.php';

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
