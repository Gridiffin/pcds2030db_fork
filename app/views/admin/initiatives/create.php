<?php
/**
 * Create Initiative Page
 * 
 * Admin interface for creating new initiatives.
 */

// Define project root path for consistent file references (align with manage_initiatives.php)
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Create Initiative';

// Process form submission
$message = '';
$message_type = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = $_POST;
    $result = create_initiative($_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = 'Initiative created successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: manage_initiatives.php');
        exit;
    } else {
        $message = $result['error'] ?? 'Failed to create initiative.';
        $message_type = 'danger';
    }
}

// Configure header for base layout
$header_config = [
    'title' => 'Create Initiative',
    'subtitle' => 'Create a new strategic initiative',
    'breadcrumb' => [
        ['text' => 'Home', 'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'],
        ['text' => 'Initiatives', 'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php'],
        ['text' => 'Create', 'url' => null]
    ],
    'actions' => [
        [
            'text' => 'Back to Initiatives',
            'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php',
            'class' => 'btn-outline-secondary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Check for session messages
$message = '';
$message_type = '';

if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Layout variables
$cssBundle = 'admin-create-initiative';
$jsBundle = 'admin-create-initiative';
$contentFile = __DIR__ . '/partials/create_content.php';

// Inline scripts for form validation
$inlineScripts = "
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.getElementById('initiativeForm');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        
        // Date validation
        function validateDates() {
            if (startDate.value && endDate.value) {
                if (new Date(startDate.value) >= new Date(endDate.value)) {
                    endDate.setCustomValidity('End date must be after start date');
                } else {
                    endDate.setCustomValidity('');
                }
            } else {
                endDate.setCustomValidity('');
            }
        }
        
        startDate.addEventListener('change', validateDates);
        endDate.addEventListener('change', validateDates);
        
        // Form submission
        form.addEventListener('submit', function(e) {
            validateDates();
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
";

require_once dirname(__DIR__, 2) . '/layouts/base_admin.php';
?>
