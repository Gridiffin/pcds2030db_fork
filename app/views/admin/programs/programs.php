<?php
/**
 * Admin View: Programs
 *
 * This file is the main view for the admin programs page.
 * It ensures the controller has run to provide data, then includes the necessary partials.
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Ensure the controller has run and prepared the data. If not, load it.
if (!isset($programs_with_drafts)) {
    require_once PROJECT_ROOT_PATH . 'app/controllers/AdminProgramsController.php';
}

// Set up variables for base_admin layout
$pageTitle = 'Admin Programs';
$cssBundle = 'admin-view-programs'; // Specific bundle for view programs page
$jsBundle = 'admin-view-programs';

// Inline script to handle delete functionality  
$inlineScripts = '
console.log("DEBUG: CSS Bundle set to: admin-view-programs");
console.log("DEBUG: Expected CSS file: /dist/css/admin-view-programs.bundle.css");

// Define triggerDeleteFromModal function globally
window.triggerDeleteFromModal = function(programId, programName) {
    console.log("triggerDeleteFromModal called with:", { programId, programName });
    
    // First, close the "More Actions" modal if it\'s open
    const moreActionsModal = document.getElementById("moreActionsModal");
    if (moreActionsModal) {
        const moreActionsModalInstance = bootstrap.Modal.getInstance(moreActionsModal);
        if (moreActionsModalInstance) {
            moreActionsModalInstance.hide();
        }
    }
    
    // Wait a bit for the first modal to close, then show delete modal
    setTimeout(function() {
        const deleteModal = document.getElementById("deleteModal");
        if (!deleteModal) {
            console.error("Delete modal not found");
            return;
        }

        const programNameDisplay = deleteModal.querySelector("#program-name-display");
        const programIdInput = deleteModal.querySelector("#program-id-input");

        if (programNameDisplay) {
            programNameDisplay.textContent = programName;
            console.log("Set program name display to:", programName);
        } else {
            console.error("Program name display element not found");
        }
        
        if (programIdInput) {
            programIdInput.value = programId;
            console.log("Set program ID input to:", programId);
        } else {
            console.error("Program ID input element not found");
        }

        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
    }, 300); // Wait 300ms for the first modal to fully close
};

// Handle form submission to close modal and show loading state
document.addEventListener("DOMContentLoaded", function() {
    const deleteForm = document.getElementById("delete-program-form");
    if (deleteForm) {
        deleteForm.addEventListener("submit", function(e) {
            console.log("Form submitted with program_id:", document.getElementById("program-id-input").value);
            
            // Get the modal instance
            const deleteModal = document.getElementById("deleteModal");
            const modalInstance = bootstrap.Modal.getInstance(deleteModal);
            
            // Show loading state
            const submitBtn = deleteForm.querySelector("button[type=submit]");
            if (submitBtn) {
                submitBtn.innerHTML = "<i class=\"fas fa-spinner fa-spin me-2\"></i>Deleting...";
                submitBtn.disabled = true;
            }
            
            // Close the modal after a brief delay to show the loading state
            setTimeout(function() {
                if (modalInstance) {
                    modalInstance.hide();
                }
            }, 500);
        });
    }
});

console.log("Delete function loaded successfully");
';

// Configure modern page header
$header_config = [
    'title' => $pageTitle,
    'subtitle' => 'View and manage programs across all agencies',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
        ],
        [
            'text' => 'Programs',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => []
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/programs_content.php';

require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';




