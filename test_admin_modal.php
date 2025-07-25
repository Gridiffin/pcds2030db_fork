<?php
/**
 * Simple test to verify admin modal functionality
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once 'app/config/config.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';

// Set up for testing
$pageTitle = 'Modal Test';
$jsBundle = 'admin-programs';
$cssBundle = 'main';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Modal Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Modal Test</h1>
        
        <!-- Test button to trigger modal -->
        <button type="button" class="btn btn-danger delete-program-btn" 
                data-bs-toggle="modal" 
                data-bs-target="#deleteModal"
                data-id="123" 
                data-name="Test Program">
            Test Delete Modal
        </button>
        
        <!-- Delete Modal (same as in _modals.php) -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the program: <strong id="program-name-display"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="#" method="post" id="delete-program-form">
                            <input type="hidden" name="program_id" id="program-id-input">
                            <input type="hidden" name="confirm_delete" value="1">
                            <button type="submit" class="btn btn-danger">Delete Program</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h3>Debug Info</h3>
            <div id="debug-info"></div>
        </div>
    </div>

    <!-- Core scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <!-- Admin programs bundle -->
    <script type="module" src="<?php echo APP_URL; ?>/dist/js/admin-programs.bundle.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const debugInfo = document.getElementById('debug-info');
            debugInfo.innerHTML = `
                <p><strong>Bootstrap loaded:</strong> ${typeof bootstrap !== 'undefined' ? 'Yes' : 'No'}</p>
                <p><strong>jQuery loaded:</strong> ${typeof $ !== 'undefined' ? 'Yes' : 'No'}</p>
                <p><strong>Modal element found:</strong> ${document.getElementById('deleteModal') ? 'Yes' : 'No'}</p>
                <p><strong>showToast function available:</strong> ${typeof showToast !== 'undefined' ? 'Yes' : 'No'}</p>
            `;
            
            // Test the delete button click
            const deleteBtn = document.querySelector('.delete-program-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    console.log('Delete button clicked');
                    const programId = this.getAttribute('data-id');
                    const programName = this.getAttribute('data-name');
                    console.log('Program ID:', programId, 'Program Name:', programName);
                    
                    // Check if modal elements exist
                    const modal = document.getElementById('deleteModal');
                    const nameDisplay = document.getElementById('program-name-display');
                    const idInput = document.getElementById('program-id-input');
                    
                    console.log('Modal found:', !!modal);
                    console.log('Name display found:', !!nameDisplay);
                    console.log('ID input found:', !!idInput);
                });
            }
        });
    </script>
</body>
</html>
