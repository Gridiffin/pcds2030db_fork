<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes - Enhanced to follow agency side structure.
*/

// Define the project root path correctly
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php'; // Added for get_all_outcomes()

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome creation setting
require_once PROJECT_ROOT_PATH . 'app/lib/admins/settings.php';
$allow_outcome_creation = get_outcome_creation_setting();

// Set page title
$pageTitle = 'Manage Outcomes';

// Get all outcomes using the new outcomes table
$outcomes = get_all_outcomes();

// No more important/regular separation for fixed outcomes

// Get current reporting period for display purposes
$current_period = get_current_reporting_period();

// Set up variables for base layout
$cssBundle = 'admin-outcomes'; // Use modular admin-outcomes CSS bundle (~85kB vs 352kB)
$jsBundle = 'admin-common';
$additionalStyles = [
    APP_URL . '/dist/js/manage_outcomes.bundle.css'
];
$additionalScripts = [
    APP_URL . '/dist/js/manage_outcomes.bundle.js'
];

// Configure the modern page header
$header_config = [
    'title' => 'Manage Outcomes',
    'subtitle' => 'Admin interface to manage outcomes across all sectors',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
        ],
        [
            'text' => 'Outcomes',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => []
];

// Add create button if outcome creation is allowed
if ($allow_outcome_creation) {
    $header_config['actions'][] = [
        'text' => 'Create New Outcome',
        'url' => APP_URL . '/app/views/admin/outcomes/create_outcome_flexible.php',
        'class' => 'btn-primary',
        'icon' => 'fas fa-plus-circle',
        'id' => 'createMetricBtn'
    ];
}

// Add period badges to actions
if ($current_period) {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success"><i class="fas fa-calendar-alt me-1"></i> Q' . $current_period['quarter'] . '-' . $current_period['year'] . '</span>'
    ];
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success ms-2"><i class="fas fa-clock me-1"></i> Ends: ' . date('M j, Y', strtotime($current_period['end_date'])) . '</span>'
    ];
} else {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i> No Active Reporting Period</span>'
    ];
}

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/manage_outcomes_content.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
