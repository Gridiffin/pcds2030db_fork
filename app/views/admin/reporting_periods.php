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
    unset($_SESSION['period_message'], $_SESSION['period_message_type']);
}

// Get all reporting periods grouped by year
$reporting_periods = get_all_reporting_periods();
$periods_by_year = [];
foreach ($reporting_periods as $period) {
    $year = $period['year'];
    if (!isset($periods_by_year[$year])) {
        $periods_by_year[$year] = [];
    }
    $periods_by_year[$year][] = $period;
}
krsort($periods_by_year);

// Set page title and scripts
$pageTitle = 'Manage Periods';
$additionalScripts = [APP_URL . '/assets/js/admin/reporting_periods.js'];

// Setup header variables
$title = "Reporting Periods";
$subtitle = "Manage system reporting periods for quarterly submissions";
$headerStyle = 'light';
$actions = [
    [
        'url' => '#',
        'id' => 'addPeriodBtn',
        'text' => 'Add Period',
        'icon' => 'fas fa-plus-circle',
        'class' => 'btn-light border border-primary text-primary'
    ]
];

require_once '../layouts/header.php';
require_once '../layouts/admin_nav.php';
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';

// Function to get display name for quarter in admin table
function get_admin_quarter_display_name($quarter_val) {
    if ($quarter_val >= 1 && $quarter_val <= 4) {
        return "Q" . $quarter_val;
    } elseif ($quarter_val == 5) {
        return "Half Yearly 1";
    } elseif ($quarter_val == 6) {
        return "Half Yearly 2";
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
            <input type="text" id="periodSearch" class="form-control form-control-sm me-2 w-auto" placeholder="Search periods...">
            <button class="btn btn-light border border-primary text-primary" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i>
                Refresh
            </button>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($periods_by_year)): ?>
            <div class="text-center py-4">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No reporting periods found.
                </div>
            </div>
        <?php else: ?>
            <div class="accordion" id="periodsAccordion">
                <?php foreach ($periods_by_year as $year => $year_periods): 
                    $is_first_year = ($year === array_key_first($periods_by_year));
                ?>
                    <div class="year-group">
                        <button class="year-toggle <?php echo $is_first_year ? 'expanded' : 'collapsed'; ?>" 
                                type="button" 
                                data-year="<?php echo $year; ?>"
                                aria-expanded="<?php echo $is_first_year ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <strong><?php echo $year; ?></strong>
                                <div class="toggle-indicator">
                                    <i class="fas <?php echo $is_first_year ? 'fa-chevron-up' : 'fa-chevron-down'; ?>"></i>
                                </div>
                            </div>
                        </button>
                        
                        <div class="year-content <?php echo $is_first_year ? 'show' : 'hide'; ?>">
                            <div class="year-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-custom period-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" class="text-center w-5">#</th>
                                                <th scope="col" class="w-15">Period</th>
                                                <th scope="col" class="w-20">Dates</th>
                                                <th scope="col" class="text-center w-10">Status</th>
                                                <th scope="col" class="text-center w-15">Last Updated</th>
                                                <th scope="col" class="text-center w-15">Actions</th>
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
                                                        <?php 
                                                        $start_date = date('M j, Y', strtotime($period['start_date']));
                                                        $end_date = date('M j, Y', strtotime($period['end_date']));
                                                        echo "$start_date - $end_date";
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge rounded-pill bg-<?php echo get_reporting_period_status_color($period['status']); ?>">
                                                            <?php echo ucfirst($period['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if (isset($period['updated_at']) && $period['updated_at'] !== null) {
                                                            echo date('M j, Y g:i A', strtotime($period['updated_at'])); 
                                                        } else {
                                                            echo 'N/A'; // Or any placeholder for missing date
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-light toggle-period-status" 
                                                                    data-period-id="<?php echo $period['period_id']; ?>"
                                                                    data-current-status="<?php echo $period['status']; ?>">
                                                                <i class="fas fa-toggle-<?php echo $period['status'] === 'open' ? 'on' : 'off'; ?>"></i>
                                                                <?php echo $period['status'] === 'open' ? 'Open' : 'Closed'; ?>
                                                            </button>
                                                            <button class="btn btn-light edit-period" title="Edit Period">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-light delete-period" title="Delete Period">
                                                                <i class="fas fa-trash-alt"></i>
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
        <?php endif; ?>
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
                                <option value="5">Half Yearly 1</option>
                                <option value="6">Half Yearly 2</option>
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

<?php
// Include footer
require_once '../layouts/footer.php';
?>

