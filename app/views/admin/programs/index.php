<?php
/**
 * Admin Programs Entry Point
 * 
 * This file serves as the entry point for the admin programs page.
 * It includes the controller which handles the business logic and then renders the view.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Load the controller to prepare the data
require_once PROJECT_ROOT_PATH . 'app/controllers/AdminProgramsController.php';

// Load the view to render the page
require_once PROJECT_ROOT_PATH . 'app/views/admin/programs/programs.php'; 