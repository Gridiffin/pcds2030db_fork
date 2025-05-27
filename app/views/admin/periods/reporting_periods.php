<?php
/**
 * Admin Reporting Periods Management
 * 
 * Allows administrators to manage reporting periods.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page configuration
$pageTitle = 'Reporting Periods Management';
$currentPage = 'periods';

// Additional CSS/JS for this page
$additionalStyles = [
    asset_url('css', 'admin/periods.css')
];

$additionalScripts = [
    asset_url('js', 'admin/periods-management.js')
];

// Include header
require_once '../../layouts/header.php';

// Include admin navigation
require_once '../../layouts/admin_nav.php';

// Set up the dashboard header variables
$title = "Reporting Periods Management";
$subtitle = "Manage reporting periods for the PCDS 2030 dashboard";
$headerStyle = 'light';
$actions = [
    [
        'url' => '#',
        'text' => 'Add Period',
        'icon' => 'fas fa-plus-circle',
        'class' => 'btn-primary',
        'id' => 'addPeriodBtn'
    ]
];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Reporting Periods Management</h5>
                <?php /* Remove redundant button, dashboard_header.php provides this
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                    <i class="fas fa-plus me-1"></i> Add New Period
                </button>
                */ ?>
            </div>
            <div class="card-body">
                <!-- Periods table will be loaded here -->
                <div id="periodsTable">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Loading periods...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Period Modal -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="addPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPeriodModalLabel">Add New Reporting Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPeriodForm">
                    <!-- Hidden field for period ID (used when editing) -->
                    <input type="hidden" id="periodId" name="period_id" value="">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="quarter" class="form-label">Period Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="quarter" name="quarter" required>
                                <option value="" disabled selected>Select Period Type</option>
                                <option value="1">Q1</option>
                                <option value="2">Q2</option>
                                <option value="3">Q3</option>
                                <option value="4">Q4</option>
                                <option value="5">Half Yearly 1 (Jan-Jun)</option>
                                <option value="6">Half Yearly 2 (Jul-Dec)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="year" name="year" required 
                                   placeholder="YYYY" min="2000" max="2099">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required readonly>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="open">Open</option>
                            <option value="closed" selected>Closed</option> 
                        </select>
                        <div class="form-text">Set the initial status for this period. Defaults to Closed.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="savePeriod">
                    <i class="fas fa-save me-1"></i> Save Period
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize APP_URL for JavaScript
const APP_URL = '<?php echo APP_URL; ?>';
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>