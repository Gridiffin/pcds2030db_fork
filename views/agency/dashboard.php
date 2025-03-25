<?php
/**
 * Agency Dashboard
 * 
 * Main interface for agency users.
 * Shows overview of their programs and reporting requirements.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../../login.php');
    exit;
}

// Get agency's programs
// ...

// Get current reporting period
// ...

// Include header
require_once '../layouts/header.php';
?>

<div class="container-fluid">
    <h1>Agency Dashboard</h1>
    
    <!-- Dashboard content would go here -->
    <!-- Programs, submission status, and metrics -->
    
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
