<?php
/**
 * View Outcome Details - Refactored
 * 
 * Agency page to view outcome details (view-only mode)
 * Supports flexible table structures (dynamic rows and columns)
 * Now uses base.php layout pattern with modular structure.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/outcomes.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

// Fetch outcome from outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: submit_outcomes.php');
    exit;
}

// Success message handling
$success_message = '';
if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $success_message = 'Outcome updated successfully!';
}

// Parse the data structure (compatible with edit_outcome.php format)
$data_array = $outcome['data'] ?? ['columns' => [], 'rows' => []];

// Ensure we have the correct structure
if (!isset($data_array['columns']) || !isset($data_array['rows'])) {
    $data_array = ['columns' => [], 'rows' => []];
}

$columns = $data_array['columns'] ?? [];
$rows = $data_array['rows'] ?? [];

// If no data exists, show empty state
$has_data = !empty($columns) && !empty($rows);

// Set up variables for base.php layout
$pageTitle = 'View Outcome Details';
$cssBundle = 'outcomes';
$jsBundle = 'outcomes';
$bodyClass = 'outcomes-page';
$contentFile = __DIR__ . '/partials/view_content.php';

// Configure page header
$header_config = [
    'title' => 'View Outcome Details',
    'subtitle' => htmlspecialchars($outcome['title']),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Prepare data for JavaScript
$tableData = [];
$tableColumns = [];
$tableRows = [];

if ($has_data) {
    $tableColumns = array_map(function($col) {
        return $col['label'] ?? $col;
    }, $columns);
    
    $tableRows = array_map(function($row) use ($columns) {
        $rowData = [];
        foreach ($columns as $col) {
            $colId = $col['id'] ?? $col;
            $colLabel = $col['label'] ?? $col;
            $rowData[$colLabel] = $row['data'][$colId] ?? '';
        }
        return [
            'label' => $row['label'] ?? $row['month'] ?? '',
            'data' => $rowData
        ];
    }, $rows);
    
    $tableData = $data_array;
}

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
