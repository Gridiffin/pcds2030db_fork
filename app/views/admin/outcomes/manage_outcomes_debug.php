<?php
/**
* Manage Outcomes - Debug Version (No Auth)
* 
* Admin page to manage outcomes.
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug: Starting manage_outcomes.php<br>";

// Include necessary files
echo "Debug: Including files...<br>";
require_once '../../../config/config.php';
echo "Debug: Config included<br>";
require_once ROOT_PATH . 'app/lib/db_connect.php';
echo "Debug: DB connection included<br>";
require_once ROOT_PATH . 'app/lib/session.php';
echo "Debug: Session included<br>";
require_once ROOT_PATH . 'app/lib/functions.php';
echo "Debug: Functions included<br>";
require_once ROOT_PATH . 'app/lib/admins/index.php';
echo "Debug: Admin functions included<br>";

// TEMPORARILY COMMENT OUT AUTHENTICATION
/*
// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}
*/

echo "Debug: Authentication bypassed<br>";

// Set page title
$pageTitle = 'Manage Outcomes';

echo "Debug: Getting outcomes data...<br>";

// Get all outcomes using the JSON-based storage function
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
$outcomes = get_all_outcomes_data($period_id);

echo "Debug: Outcomes retrieved, type: " . gettype($outcomes) . "<br>";

// Ensure $outcomes is always an array to prevent null reference errors
if (!is_array($outcomes)) {
    $outcomes = [];
    echo "Debug: Converted to empty array<br>";
}

echo "Debug: About to include layouts...<br>";

// Include header
require_once '../../layouts/header.php';

echo "Debug: Header included<br>";

// Include admin navigation
require_once '../../layouts/admin_nav.php';

echo "Debug: Admin nav included<br>";

?>

<div class="container-fluid px-4 py-4">
    <div class="alert alert-warning">
        <strong>DEBUG MODE:</strong> Authentication is temporarily disabled for testing.
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Outcomes (Debug)</h1>
            <p class="text-muted">Admin interface to manage outcomes</p>
        </div>
    </div>
    
    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Debug Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Outcomes count:</strong> <?= count($outcomes) ?></p>
            <p><strong>Period ID:</strong> <?= $period_id ?></p>
            <p><strong>Page loaded successfully!</strong></p>
        </div>
    </div>
</div>

<?php
echo "Debug: Page content rendered<br>";
?>
