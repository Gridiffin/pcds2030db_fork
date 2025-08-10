<?php
/**
 * Admin Edit Initiative Page
 * 
 * Provides interface for editing existing initiatives.
 * Follows the same pattern as admin program pages.
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
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID from URL
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get initiative details
$initiative = get_initiative_by_id($initiative_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_description_col = get_column_name('initiatives', 'description');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_status_col = get_column_name('initiatives', 'status');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$created_at_col = get_column_name('initiatives', 'created_at');
$updated_at_col = get_column_name('initiatives', 'updated_at');

// Program column names for displaying associated programs
$programNameCol = get_column_name('programs', 'name');
$programNumberCol = get_column_name('programs', 'number');
$agencyNameCol = get_column_name('agency', 'name');

// Process form submission
$message = '';
$message_type = '';
$form_data = $initiative; // Pre-populate with existing data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $result = delete_initiative($initiative_id);
        
        if (isset($result['success'])) {
            $_SESSION['message'] = 'Initiative deleted successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: manage_initiatives.php');
            exit;
        } else {
            $message = $result['error'] ?? 'Failed to delete initiative.';
            $message_type = 'danger';
        }
    } else {
        // Regular update action
        $form_data = $_POST;
        $result = update_initiative($initiative_id, $_POST);
        
        if (isset($result['success'])) {
            $_SESSION['message'] = 'Initiative updated successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: manage_initiatives.php');
            exit;
        } else {
            $message = $result['error'] ?? 'Failed to update initiative.';
            $message_type = 'danger';
        }
    }
}

// Get associated programs for display
$associated_programs = get_initiative_programs($initiative_id);

// Check for session messages
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Set page title and header configuration
$pageTitle = 'Edit Initiative';
$header_config = [
    'title' => 'Edit Initiative',
    'subtitle' => htmlspecialchars($initiative[$initiative_name_col] ?? 'Unknown Initiative'),
    'breadcrumb' => [
        ['text' => 'Home', 'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'],
        ['text' => 'Initiatives', 'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php'],
        ['text' => 'Edit', 'url' => null]
    ],
    'actions' => [
        [
            'url' => 'manage_initiatives.php',
            'text' => 'Back to Initiatives',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ],
        [
            'url' => 'view_initiative.php?id=' . $initiative_id,
            'text' => 'View Initiative',
            'icon' => 'fas fa-eye',
            'class' => 'btn-info'
        ]
    ]
];

// Inline scripts for form validation
$inlineScripts = "
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.getElementById('initiativeForm');
        if (form) {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            // Date validation
            function validateDates() {
                if (startDate && endDate && startDate.value && endDate.value) {
                    if (new Date(startDate.value) >= new Date(endDate.value)) {
                        endDate.setCustomValidity('End date must be after start date');
                    } else {
                        endDate.setCustomValidity('');
                    }
                } else if (endDate) {
                    endDate.setCustomValidity('');
                }
            }
            
            if (startDate) startDate.addEventListener('change', validateDates);
            if (endDate) endDate.addEventListener('change', validateDates);
            
            // Form submission
            form.addEventListener('submit', function(e) {
                validateDates();
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        }
    });
";

// Layout variables
$cssBundle = 'admin-edit-initiative';
$jsBundle = 'admin-edit-initiative';
$contentFile = __DIR__ . '/partials/edit_content.php';

// Include base layout
require_once dirname(__DIR__, 2) . '/layouts/base_admin.php';
?>