<?php
/**
 * Reporting Periods Management
 * 
 * Interface for admin users to manage reporting periods.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Fix database structure first to prevent errors
$fix_url = APP_URL . '/ajax/fix_reporting_periods_table.php';
$fix_result = @file_get_contents($fix_url);
if ($fix_result) {
    $fix_data = json_decode($fix_result, true);
    if (isset($fix_data['success']) && $fix_data['success'] && $fix_data['fix_count'] > 0) {
        // Store a message about the fixes
        $_SESSION['period_message'] = 'Database structure was automatically updated.';
        $_SESSION['period_message_type'] = 'info';
    }
}

// Process form submission
$message = '';
$messageType = '';

// Check if form was processed and redirect to prevent resubmission on refresh
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
        
        // Store message in session
        $_SESSION['period_message'] = $message;
        $_SESSION['period_message_type'] = $messageType;
        
        // Redirect to the same page to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
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
            
            // Store message in session
            $_SESSION['period_message'] = $message;
            $_SESSION['period_message_type'] = $messageType;
        }
        
        // Redirect to the same page to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Check for flash messages from redirects
if (isset($_SESSION['period_message'])) {
    $message = $_SESSION['period_message'];
    $messageType = $_SESSION['period_message_type'];
    
    // Clear the session variables
    unset($_SESSION['period_message']);
    unset($_SESSION['period_message_type']);
}

// Get all reporting periods
$reporting_periods = get_all_reporting_periods();

// Set page title
$pageTitle = 'Manage Periods';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/reporting_periods.js'
];

// Setup header variables for common dashboard header
$title = "Reporting Periods";
$subtitle = "Manage system reporting periods for quarterly submissions";
$headerStyle = 'light'; // Use light style to match other admin pages
$actions = [
    [
        'url' => '#',
        'id' => 'addPeriodBtn',
        'text' => 'Add Period',
        'icon' => 'fas fa-plus-circle',
        'class' => 'btn-light border border-primary text-primary' // Changed to white background with primary border and text
    ]
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Include the dashboard header component
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';

// Function to get display name for quarter in admin table
function get_admin_quarter_display_name($quarter_val) {
    if ($quarter_val >= 1 && $quarter_val <= 4) {
        return "Q" . $quarter_val;
    } elseif ($quarter_val == 5) {
        return "Half Year 1";
    } elseif ($quarter_val == 6) {
        return "Half Year 2";
    } else {
        return "Unknown";
    }
}
?>

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
        <div class="ms-auto d-flex align-items-center">
            <input type="text" id="periodSearch" class="form-control form-control-sm me-2" placeholder="Search periods..." style="width: 200px;">
            <button class="btn btn-light border border-primary text-primary" id="refreshPage"> <!-- Changed to white background with primary border and text -->
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
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
        
        <div class="year-accordion" id="yearGroups">
            <?php foreach ($periods_by_year as $year => $year_periods): ?>
                <div class="year-group mb-3">
                    <div class="year-header" id="heading<?php echo $year; ?>">
                        <button class="year-toggle <?php echo ($year === array_key_first($periods_by_year)) ? 'expanded' : 'collapsed'; ?>" 
                                type="button" 
                                data-year="<?php echo $year; ?>"
                                aria-expanded="<?php echo ($year === array_key_first($periods_by_year)) ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div>
                                    <strong class="fs-5 me-2"><?php echo $year; ?></strong>
                                    <span class="badge bg-secondary"><?php echo count($year_periods); ?> quarters</span>
                                </div>
                                <div class="toggle-indicator">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </button>
                    </div>
                    <div id="collapse<?php echo $year; ?>" 
                        class="year-content <?php echo ($year === array_key_first($periods_by_year)) ? 'show' : 'hide'; ?>">
                        <div class="year-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom period-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 5%;">#</th>
                                            <th scope="col" style="width: 15%;">Period</th>
                                            <th scope="col" style="width: 20%;">Dates</th>
                                            <th scope="col" class="text-center" style="width: 10%;">Status</th>
                                            <th scope="col" class="text-center" style="width: 15%;">Last Updated</th>
                                            <th scope="col" class="text-center" style="width: 15%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($year_periods as $index => $period): ?>
                                            <tr data-period-id="<?php echo $period['period_id']; ?>">
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo get_admin_quarter_display_name($period['quarter']); ?></strong> 
                                                    <small class="text-muted">(<?php echo $period['year']; ?>)</small>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, Y', strtotime($period['start_date'])) . ' - ' . date('M j, Y', strtotime($period['end_date'])); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?php echo $period['status'] === 'open' ? 'success' : 'secondary'; ?> rounded-pill px-3">
                                                        <?php echo ucfirst($period['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                        if (!empty($period['updated_at'])) {
                                                            echo date('M j, Y g:i A', strtotime($period['updated_at'])); 
                                                        } else {
                                                            echo 'N/A'; // Or some other placeholder
                                                        }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-<?php echo $period['status'] === 'open' ? 'danger' : 'success'; ?> toggle-period-status"
                                                                data-period-id="<?php echo $period['period_id']; ?>"
                                                                data-current-status="<?php echo $period['status']; ?>"
                                                                title="<?php echo $period['status'] === 'open' ? 'Close' : 'Open'; ?> this period">
                                                            <i class="fas fa-<?php echo $period['status'] === 'open' ? 'lock' : 'lock-open'; ?> me-2"></i>
                                                            <span class="button-text"><?php echo $period['status'] === 'open' ? 'Close' : 'Open'; ?></span>
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
                            <span>Use standard quarter dates</span>
                            <div class="d-flex align-items-center">
                                <span id="datesModeText" class="me-2">Standard dates</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="useStandardDates" checked>
                                    <label class="form-check-label" for="useStandardDates"></label>
                                </div>
                            </div>
                        </div>
                        <div class="toggle-description text-muted small mt-1">
                            When enabled, dates will automatically follow standard calendar quarters.
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="start_date" name="start_date" required readonly>
                                <span class="input-group-text d-none" id="nonStandardStartIndicator" title="Non-standard date">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="end_date" name="end_date" required readonly>
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

<!-- Add style for accordion arrows -->
<style>
    /* Improved accordion styling */
    .year-header {
        margin-bottom: 0;
    }
    
    .year-toggle {
        width: 100%;
        text-align: left;
        padding: 1rem 1.25rem;
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
        cursor: pointer;
    }
    
    .year-toggle.expanded {
        color: #212529;
        background-color: #f1f3f5;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    .year-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .toggle-indicator i {
        transition: transform 0.3s ease;
    }
    
    .year-toggle.expanded .toggle-indicator i {
        transform: rotate(180deg);
    }
    
    .year-content {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-top: none;
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    
    .year-content.hide {
        display: none;
    }
    
    .year-content.show {
        display: block;
    }
    
    /* Improved styling for period status badge */
    .badge.rounded-pill {
        font-weight: normal;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
    
    /* Improve button spacing and hover effects */
    .toggle-period-status {
        min-width: 90px;
        transition: all 0.2s;
        border: 1px solid;
    }
    
    /* Button group styling with borders and consistent spacing */
    .btn-group-sm .btn {
        padding: 0.375rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        margin-right: -1px;
    }
    
    /* Make all icon buttons square for consistency */
    .btn-group-sm .btn:not(.toggle-period-status) {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Remove right margin from icons in action buttons */
    .btn-group-sm .btn:not(.toggle-period-status) i {
        margin-right: 0;
    }
    
    /* Keep spacing between icon and text for the toggle button */
    .btn-group-sm .toggle-period-status i {
        margin-right: 0.5rem;
    }
    
    /* Improve table styling */
    .table-custom thead th {
        background-color: #f3f5f7;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .period-row {
        transition: background-color 0.2s;
    }
    
    .period-row:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    /* Improve accordion item spacing */
    .year-group {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
        overflow: hidden;
    }
</style>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

