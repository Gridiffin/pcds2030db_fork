<?php
/**
 * Create Sector Metrics
 * 
 * Interface for agency users to create sectir-specific metrics
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency_user()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Create Sector Metrics';

// Get metrics for the agency's sector
$metrics = get_agency_sector_metrics($_SESSION['sector_id']);
if (!is_array($metrics)) {
    $metrics = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = 
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Create New Sector Metrics</h1>
        <p class="text-muted">Create your sector-specific metrics</p>
    </div>
</div>



