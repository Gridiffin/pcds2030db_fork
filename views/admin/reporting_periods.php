<?php
/**
 * Reporting Periods Management
 * 
 * Interface for admin users to manage reporting periods.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle add/edit reporting period
    if (isset($_POST['save_period'])) {
        $period_id = intval($_POST['period_id'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        $quarter = intval($_POST['quarter'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'open';
        
        if ($period_id) {
            // Update existing period
            $result = update_reporting_period($period_id, $year, $quarter, $start_date, $end_date, $status);
        } else {
            // Add new period
            $result = add_reporting_period($year, $quarter, $start_date, $end_date, $status);
        }
        
        if (isset($result['success'])) {
            $message = $result['message'] ?? 'Reporting period saved successfully.';
            $messageType = 'success';
        } else {
            $message = $result['error'] ?? 'Failed to save reporting period.';
            $messageType = 'danger';
        }
    }
    
    // Handle delete reporting period
    if (isset($_POST['delete_period'])) {
        $period_id = intval($_POST['period_id'] ?? 0);
        
        if ($period_id) {
            $result = delete_reporting_period($period_id);
            
            if (isset($result['success'])) {
                $message = $result['message'] ?? 'Reporting period deleted successfully.';
                $messageType = 'success';
            } else {
                $message = $result['error'] ?? 'Failed to delete reporting period.';
                $messageType = 'danger';
            }
        }
    }
}

// Get all reporting periods
$reporting_periods = get_all_reporting_periods();

// Set page title
$pageTitle = 'Manage Periods';

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/admin.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/reporting_periods.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Manage Periods</h1>
        <p class="text-muted">Add, edit, and delete reporting periods</p>
    </div>
    <button type="button" class="btn btn-primary" id="addPeriodBtn">
        <i class="fas fa-plus-circle me-1"></i> Add Period
    </button>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Reporting Periods Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Periods</h5>
        <div>
            <input type="text" id="periodSearch" class="form-control form-control-sm d-inline-block me-2" placeholder="Search periods..." style="width: auto;">
        </div>
    </div>
    <div class="card-body">
        <?php 
        // Group reporting periods by year
        $periods_by_year = [];
        foreach ($reporting_periods as $period) {
            $periods_by_year[$period['year']][] = $period;
        }
        
        // Sort years in descending order (newest first)
        krsort($periods_by_year);
        ?>
        
        <div class="accordion" id="accordionPeriods">
            <?php foreach ($periods_by_year as $year => $year_periods): ?>
                <div class="accordion-item year-group mb-3">
                    <h2 class="accordion-header position-relative" id="heading<?php echo $year; ?>">
                        <button class="accordion-button <?php echo ($year === array_key_first($periods_by_year)) ? '' : 'collapsed'; ?>" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?php echo $year; ?>" 
                                aria-expanded="<?php echo ($year === array_key_first($periods_by_year)) ? 'true' : 'false'; ?>" 
                                aria-controls="collapse<?php echo $year; ?>">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div>
                                    <strong class="fs-5 me-2"><?php echo $year; ?></strong>
                                    <span class="badge bg-secondary"><?php echo count($year_periods); ?> quarters</span>
                                </div>
                                <div class="collapse-indicator">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $year; ?>" 
                         class="accordion-collapse collapse <?php echo ($year === array_key_first($periods_by_year)) ? 'show' : ''; ?>" 
                         aria-labelledby="heading<?php echo $year; ?>">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom period-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Quarter</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($year_periods as $period): 
                                            $is_standard = isset($period['is_standard_dates']) ? 
                                                intval($period['is_standard_dates']) : 
                                                is_standard_quarter_date($period['year'], $period['quarter'], $period['start_date'], $period['end_date']);
                                        ?>
                                            <tr class="period-row" data-year="<?php echo $period['year']; ?>" data-quarter="<?php echo $period['quarter']; ?>">
                                                <td>
                                                    <span class="fw-bold">Q<?php echo $period['quarter']; ?></span>
                                                    <?php if (!$is_standard): ?>
                                                        <span class="ms-1 badge bg-warning" title="Custom date range">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($period['start_date'])); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($period['end_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $period['status'] === 'open' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($period['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-<?php echo $period['status'] === 'open' ? 'danger' : 'success'; ?> toggle-period-status"
                                                                data-period-id="<?php echo $period['period_id']; ?>"
                                                                data-current-status="<?php echo $period['status']; ?>"
                                                                title="<?php echo $period['status'] === 'open' ? 'Close' : 'Open'; ?> this period">
                                                            <i class="fas fa-<?php echo $period['status'] === 'open' ? 'lock' : 'lock-open'; ?>"></i>
                                                            <?php echo $period['status'] === 'open' ? 'Close' : 'Open'; ?>
                                                        </button>
                                                        
                                                        <button type="button" class="btn btn-outline-secondary edit-period-btn" 
                                                                data-id="<?php echo $period['period_id']; ?>"
                                                                data-year="<?php echo $period['year']; ?>"
                                                                data-quarter="<?php echo $period['quarter']; ?>"
                                                                data-start-date="<?php echo $period['start_date']; ?>"
                                                                data-end-date="<?php echo $period['end_date']; ?>"
                                                                data-status="<?php echo $period['status']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger delete-period-btn" 
                                                                data-id="<?php echo $period['period_id']; ?>"
                                                                data-year="<?php echo $period['year']; ?>"
                                                                data-quarter="<?php echo $period['quarter']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="noPeriodsFound" class="alert alert-info text-center d-none">
            <i class="fas fa-info-circle me-2"></i> No periods found matching your search.
        </div>
    </div>
</div>

<!-- Add/Edit Period Modal -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-labelledby="periodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="periodModalLabel">Add Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="periodForm" method="post">
                    <input type="hidden" name="period_id" id="period_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" id="year" name="year" required>
                        </div>
                        <div class="col-md-6">
                            <label for="quarter" class="form-label">Quarter</label>
                            <select class="form-select" id="quarter" name="quarter" required>
                                <option value="1">Q1</option>
                                <option value="2">Q2</option>
                                <option value="3">Q3</option>
                                <option value="4">Q4</option>
                            </select>
                        </div>
                    </div>

                    <div class="date-toggle-container mb-4">
                        <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between">
                            <span>Use custom date range</span>
                            <div class="d-flex align-items-center">
                                <span id="datesModeText" class="me-2">Standard dates</span>
                                <input class="form-check-input m-0" type="checkbox" id="useStandardDates" checked>
                            </div>
                        </div>
                        <div class="toggle-description text-muted small mt-1">
                            Toggle OFF to customize dates. Standard dates follow calendar quarters.
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                                <span class="input-group-text d-none" id="nonStandardStartIndicator" title="Non-standard date">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                                <span class="input-group-text d-none" id="nonStandardEndIndicator" title="Non-standard date">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_period" class="btn btn-primary">Save Period</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the reporting period: <strong id="period-display"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="reporting_periods.php" method="post" id="delete-period-form">
                    <input type="hidden" name="period_id" id="delete-period-id">
                    <button type="submit" name="delete_period" class="btn btn-danger">Delete Period</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>