<?php
/**
 * Manage Reporting Periods
 * 
 * Admin page to manage reporting periods.
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Reporting Periods';

// Get all reporting periods
$periods = get_all_reporting_periods();

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/manage_periods.js'
];

// Include header
require_once '../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Manage Reporting Periods',
    'subtitle' => 'Admin interface to manage reporting periods',
    'variant' => 'green',
    'actions' => []
];

// Include modern page header
require_once '../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Reporting Periods</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Automated Period Management Active</strong>
                <p class="mb-0 mt-1">Reporting periods are now automatically managed based on calendar quarters. The current quarter (Q<?php echo ceil(date('n')/3); ?>) is automatically set to "open" status.</p>
            </div>
            
            <!-- Display the periods table as before -->
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Quarter</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $period): ?>
                            <tr>
                                <td><?php echo $period['year']; ?></td>
                                <td>Q<?php echo $period['quarter']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $period['status'] === 'open' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($period['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($period['start_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($period['end_date'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPeriodModal" data-period-id="<?php echo $period['period_id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div class="modal fade" id="editPeriodModal" tabindex="-1" aria-labelledby="editPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPeriodModalLabel">Edit Reporting Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPeriodForm">
                    <input type="hidden" name="period_id" id="editPeriodId">
                    <div class="mb-3">
                        <label for="editYear" class="form-label">Year</label>
                        <input type="number" class="form-control" id="editYear" name="year" required>
                    </div>
                    <div class="mb-3">
                        <label for="editQuarter" class="form-label">Quarter</label>
                        <select class="form-select" id="editQuarter" name="quarter" required>
                            <option value="1">Q1</option>
                            <option value="2">Q2</option>
                            <option value="3">Q3</option>
                            <option value="4">Q4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="editStartDate" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="editEndDate" name="end_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

