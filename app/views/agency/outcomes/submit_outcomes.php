<?php
/**
 * Submit Outcomes - Refactored
 * 
 * Interface for agency users to submit sector outcomes.
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

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get all outcomes using the new outcomes table
$outcomes = get_all_outcomes();

// Set up variables for base.php layout
$pageTitle = 'View Outcomes';
$cssBundle = 'outcomes';
$jsBundle = 'outcomes';
$bodyClass = 'outcomes-page';
$contentFile = __DIR__ . '/partials/submit_content.php';

// Configure page header
$header_config = [
    'title' => 'View Outcomes',
    'subtitle' => 'View and explore all outcomes data',
    'variant' => 'green',
    'actions' => [
        [
            'url' => '#',
            'id' => 'refresh-outcomes',
            'text' => 'Refresh',
            'icon' => 'fas fa-sync-alt',
            'class' => 'btn-light'
        ]
    ]
];

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
