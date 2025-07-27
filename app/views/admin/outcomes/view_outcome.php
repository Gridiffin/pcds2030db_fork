<?php
/**
 * View Submitted Outcome Details for Admin
 * 
 * Allows admin users to view the details of submitted outcomes from any sector.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php'; // Contains legacy functions
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php'; // Contains updated outcome functions
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php'; // Contains is_admin
require_once PROJECT_ROOT_PATH . 'app/lib/admins/users.php'; // Contains user information functions
require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php'; // For display_submission_status_badge
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php'; // For display_overall_rating_badge

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}
// Set $title to fallback only if truly empty
$title = (!empty($outcome['title'])) ? $outcome['title'] : 'Untitled Outcome';
$code = $outcome['code'] ?? '';
$type = $outcome['type'] ?? '';
$description = $outcome['description'] ?? '';
$data_array = $outcome['data'] ?? ['columns' => [], 'rows' => []];
$updated_at = new DateTime($outcome['updated_at'] ?? 'now');

// Ensure we have the correct structure
if (!isset($data_array['columns']) || !isset($data_array['rows'])) {
    $data_array = ['columns' => [], 'rows' => []];
}

$columns = $data_array['columns'] ?? [];
$rows = $data_array['rows'] ?? [];

// Ensure $columns and $data_array['rows'] are always arrays
if (!isset($columns) || !is_array($columns)) {
    $columns = [];
}
if (!isset($data_array['rows']) || !is_array($data_array['rows'])) {
    $data_array['rows'] = [];
}

// Get row labels from the rows array
$row_labels = [];
foreach ($rows as $row) {
    $row_labels[] = $row['month'] ?? $row['label'] ?? '';
}

// If no data exists, show empty state
$has_data = !empty($columns) && !empty($rows);

// Set up base layout variables
$pageTitle = 'View Outcome - ' . $title;
$cssBundle = 'admin-outcomes'; // Vite bundle for admin outcomes
$jsBundle = 'admin-outcomes';

// Configure modern page header
$header_config = [
    'title' => $title,
    'subtitle' => 'Review outcome data and metrics',
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
            'text' => 'View Outcome',
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
        // Replace Edit Outcome button with Edit KPI if type is kpi
        (
            $type === 'kpi'
            ? [
                'url' => 'edit_kpi.php?id=' . $outcome_id,
                'text' => 'Edit KPI',
                'icon' => 'fas fa-edit',
                'class' => 'btn-primary'
            ]
            : [
                'url' => 'edit_outcome.php?id=' . $outcome_id,
                'text' => 'Edit Outcome',
                'icon' => 'fas fa-edit',
                'class' => 'btn-primary'
            ]
        )
    ]
];
// Set content file for base layout
$contentFile = __DIR__ . '/partials/view_outcome_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
