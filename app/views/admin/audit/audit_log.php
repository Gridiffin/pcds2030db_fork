<?php
/**
 * Admin Audit Log
 * Displays system audit logs for administrators.
 */

// --- Dependencies & Access Control ---
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Only allow admin users
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// --- Page Configuration ---
$pageTitle = 'System Audit Log';
$currentPage = 'audit';

// --- Asset Loading ---
$additionalStyles = [
    asset_url('css/custom', 'audit_log.css'), // Custom audit log styles
];
$additionalScripts = [
    asset_url('js/admin', 'audit-log.js'), // Modular JS for audit log
];

// --- Layout Includes ---
require_once '../../layouts/header.php';

// Modern page header config
$header_config = [
    'title' => 'Audit Log',
    'subtitle' => 'View system activity and security logs',
    'variant' => 'white',
    'actions' => []
];
require_once '../../layouts/page_header.php';
?>

<div class="row mb-4">
    <div class="col-lg-12">
        <!-- Error alert container for JS-injected alerts -->
        <div id="auditLogAlertContainer"></div>
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title m-0">
                    <i class="fas fa-filter me-2"></i>Filter Audit Logs
                </h6>
            </div>
            <div class="card-body">
                <form id="auditFilter" class="row g-3">
                    <div class="col-md-3">
                        <label for="filterDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="filterDate" name="date_from">
                    </div>
                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="filterDateTo" name="date_to">
                    </div>
                    <div class="col-md-3">
                        <label for="filterAction" class="form-label">Action Type</label>
                        <select class="form-select" id="filterAction" name="action_type">
                            <option value="">All Actions</option>
                            <option value="login_success">Login Success</option>
                            <option value="login_failure">Login Failed</option>
                            <option value="logout">Logout</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                            <option value="export">Export</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterUser" class="form-label">User</label>
                        <input type="text" class="form-control" id="filterUser" name="user" placeholder="Username">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// --- Error Alert Partial (hidden by default, shown via JS) ---
$errorMessage = 'Error loading audit logs';
$errorVisible = false;
$errorId = 'errorAlert';
include __DIR__ . '/../partials/error_alert.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Audit Log Entries</h5>
            </div>
            <div class="card-body">
                <!-- Audit log table will be loaded here via JS -->
                <div id="auditLogTable">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Loading audit logs...</p>
                    </div>
                </div>
                <!-- Pagination area will be dynamically added here -->
                <div id="paginationContainer"></div>
            </div>
        </div>
    </div>
</div>

<?php
// --- Footer Include ---
require_once '../../layouts/footer.php';
?>