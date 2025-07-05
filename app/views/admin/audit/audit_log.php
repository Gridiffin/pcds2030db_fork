<?php
/**
 * Admin Audit Log
 * 
 * Displays system audit logs for administrators.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page configuration
$pageTitle = 'System Audit Log';
$currentPage = 'audit';

// Additional CSS/JS for this page
$additionalStyles = [
    // audit.css removed - using standard Bootstrap classes
];

$additionalScripts = [
    asset_url('js', 'admin/audit-log.js')
];

// Include header
require_once '../../layouts/header.php';

// Include admin navigation
// Set up the dashboard header variables
$title = "System Audit Log";
// Configure modern page header
$header_config = [
    'title' => 'Audit Log',
    'subtitle' => 'View system activity and security logs',
    'variant' => 'white',
    'actions' => []
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="row mb-4">
    <div class="col-lg-12">
        <!-- Error alert container -->
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

<!-- Error alert for displaying errors -->
<div class="row mb-4 d-none" id="errorAlert">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="errorMessage">Error loading audit logs</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Audit Log Entries</h5>
            </div>
            <div class="card-body">
                <!-- Audit log table will be loaded here -->
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

<script>
// Initialize APP_URL for JavaScript
const APP_URL = '<?php echo APP_URL; ?>';

// Helper function to show error alerts
function showErrorAlert(message) {
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    
    if (errorAlert && errorMessage) {
        errorMessage.textContent = message;
        errorAlert.classList.remove('d-none');
    }
}

// Function to close error alerts
function closeErrorAlert() {
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        errorAlert.classList.add('d-none');
    }
}

// Add event listener to refresh button
document.getElementById('refreshLogs')?.addEventListener('click', function() {
    // This will be picked up by the audit-log.js loadAuditLogs function
    if (typeof loadAuditLogs === 'function') {
        loadAuditLogs();
    } else {
        location.reload();
    }
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>