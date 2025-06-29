<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes.
*/

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Outcomes';

// Get all outcomes using the JSON-based storage function
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
$outcomes = get_all_outcomes_data($period_id);

// Ensure $outcomes is always an array to prevent null reference errors
if (!is_array($outcomes)) {
    $outcomes = [];
}

// Get current and all reporting periods for filtering
$current_period = get_current_reporting_period();
$reporting_periods = get_all_reporting_periods();

// Get all sectors for filtering
$sectors = get_all_sectors();

// Initialize filter variables
$selected_sector = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;
$selected_period = $period_id ?: ($current_period ? $current_period['period_id'] : 0);

// Filter outcomes by sector if a sector filter is applied
if ($selected_sector > 0 && !empty($outcomes)) {
    $outcomes = array_filter($outcomes, function($outcome) use ($selected_sector) {
        return isset($outcome['sector_id']) && $outcome['sector_id'] == $selected_sector;
    });
}

// Include header
require_once '../../layouts/header.php';

// Get outcome creation setting
require_once ROOT_PATH . 'app/lib/admins/settings.php';
$allow_outcome_creation = get_outcome_creation_setting();

// Configure the modern page header
$header_config = [
    'title' => 'Manage Outcomes',
    'subtitle' => 'Admin interface to manage outcomes',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Refresh',
            'url' => '#',
            'id' => 'refreshPage',
            'class' => 'btn-light',
            'icon' => 'fas fa-sync-alt'
        ]
    ]
];

// Add create button if outcome creation is allowed
if ($allow_outcome_creation) {
    $header_config['actions'][] = [
        'text' => 'Create New Outcome',
        'url' => APP_URL . '/app/views/admin/outcomes/create_outcome_flexible.php',
        'class' => 'btn-primary',
        'icon' => 'fas fa-plus-circle',
        'id' => 'createMetricBtn'
    ];
}

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Ensure Bootstrap JS is included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid px-4 py-4">
    <!-- Sector Filter -->
    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Filter Outcomes</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 filter-controls">
                <div class="col-md-4">
                    <label for="period_id" class="form-label">Filter by Reporting Period</label>
                    <select name="period_id" id="period_id" class="form-select" onchange="this.form.submit()">
                        <option value="0">All Reporting Periods</option>
                        <?php foreach ($reporting_periods as $period): ?>
                            <option value="<?= $period['period_id'] ?>" <?= $selected_period == $period['period_id'] ? 'selected' : '' ?>>
                                Q<?= $period['quarter'] ?>-<?= $period['year'] ?> 
                                (<?= $period['status'] == 'open' ? 'Current' : 'Closed' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sector_id" class="form-label">Filter by Sector</label>
                    <select name="sector_id" id="sector_id" class="form-select" onchange="this.form.submit()">
                        <option value="0">All Sectors</option>
                        <?php foreach ($sectors as $sector): ?>
                            <option value="<?= $sector['sector_id'] ?>" <?= $selected_sector == $sector['sector_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sector['sector_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-forest me-2">Apply Filter</button>
                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/manage_outcomes.php" class="btn btn-forest-light">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Outcomes</h5>
        </div>

        <?php if (!$allow_outcome_creation): ?>
        <div class="card-body border-bottom">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Important:</strong> 
                Creation of new outcomes has been disabled by the administrator. This ensures outcomes remain consistent across reporting periods.
                Outcome history is now tracked, and existing outcomes cannot be deleted to maintain data integrity.
                <a href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php" class="alert-link">
                    <i class="fas fa-cog ms-1"></i> Manage settings
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                    type="button" role="tab" aria-controls="table-view" aria-selected="true">
                    <i class="fas fa-table me-1"></i> Table View
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="outcomesTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body p-0">
                    <table id="metricsTable" class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Outcome ID</th>
                                <th>Sector</th>
                                <th>Table Name</th>
                                <th>Reporting Period</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $display_outcomes = !empty($outcomes) ? array_values($outcomes) : [];
                            if (empty($display_outcomes)): 
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="alert alert-forest alert-info mb-0">
                                            <i class="fas fa-info-circle alert-icon"></i>
                                            <?php
                                            if ($selected_sector > 0 && $selected_period > 0) {
                                                echo 'No outcomes found for the selected sector and reporting period.';
                                            } elseif ($selected_sector > 0) {
                                                echo 'No outcomes found for the selected sector.';
                                            } elseif ($selected_period > 0) {
                                                echo 'No outcomes found for the selected reporting period.';
                                            } else {
                                                echo 'No outcomes found in the system.';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($display_outcomes as $outcome): ?>
                                    <tr data-metric-id="<?php echo $outcome['metric_id']; ?>">
                                        <td><?php echo $outcome['metric_id']; ?></td>
                                        <td><?php echo htmlspecialchars($outcome['sector_name'] ?? 'No Sector'); ?></td>
                                        <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>
                                        <td>
                                            <?php if (isset($outcome['quarter']) && isset($outcome['year'])): ?>
                                                <span class="status-indicator <?= ($current_period && $outcome['period_id'] == $current_period['period_id']) ? 'status-success' : 'status-info' ?>">
                                                    Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-indicator status-warning">Not Specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($outcome['created_at'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Primary outcome actions">
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/view_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="View Outcome Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-secondary" 
                                                   title="Edit Outcome">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/outcome_history.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="View Change History">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                            <div class="mt-1 d-grid status-action-container" data-metric-id="<?php echo $outcome['metric_id']; ?>">
                                                <?php if (isset($outcome['is_draft']) && $outcome['is_draft'] == 1): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-success btn-sm w-100 submit-outcome" 
                                                            data-metric-id="<?php echo $outcome['metric_id']; ?>"
                                                            title="Submit Outcome">
                                                        <i class="fas fa-redo"></i> Submit
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning btn-sm w-100 unsubmit-outcome" 
                                                            data-metric-id="<?php echo $outcome['metric_id']; ?>"
                                                            title="Unsubmit Outcome">
                                                        <i class="fas fa-undo"></i> Unsubmit
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refreshPage');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => window.location.reload());
        }

        const outcomesTableBody = document.querySelector('#metricsTable tbody');

        if (outcomesTableBody) {
            outcomesTableBody.addEventListener('click', function(event) {
                const targetButton = event.target.closest('.submit-outcome, .unsubmit-outcome');
                if (!targetButton) {
                    return; // Click was not on a relevant button
                }
                
                event.preventDefault();
                handleOutcomeAction(targetButton);
            });
        }

        function updateButtonAppearance(button, is_draft) {
            button.disabled = false; // Re-enable button
            if (is_draft == 1) { // Now a draft, show "Submit"
                button.classList.remove('unsubmit-outcome', 'btn-outline-warning');
                button.classList.add('submit-outcome', 'btn-outline-success');
                button.innerHTML = '<i class="fas fa-redo"></i> Submit';
                button.title = 'Submit Outcome';
            } else { // Now submitted, show "Unsubmit"
                button.classList.remove('submit-outcome', 'btn-outline-success');
                button.classList.add('unsubmit-outcome', 'btn-outline-warning');
                button.innerHTML = '<i class="fas fa-undo"></i> Unsubmit';
                button.title = 'Unsubmit Outcome';
            }
        }

        async function handleOutcomeAction(button) {
            const metricId = button.dataset.metricId;
            const action = button.classList.contains('submit-outcome') ? 'submit' : 'unsubmit';
            const originalButtonText = button.innerHTML;
            const originalButtonTitle = button.title;

            if (!confirm(`Are you sure you want to ${action} this outcome?`)) {
                return;
            }

            // Disable button and show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const formData = new FormData();
            formData.append('metric_id', metricId);
            formData.append('action', action);

            try {
                const response = await fetch('<?php echo APP_URL; ?>/app/views/admin/outcomes/handle_outcome_status.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' // Ensure server knows we expect JSON
                    }
                });

                if (!response.ok) {
                    // Attempt to get error message from server if JSON, otherwise use status text
                    let errorMsg = `HTTP error ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        errorMsg = errorData.message || errorMsg;
                    } catch (e) {
                        // Failed to parse JSON, stick with HTTP status
                    }
                    throw new Error(errorMsg);
                }

                const data = await response.json();

                if (data.success) {
                    updateButtonAppearance(button, data.is_draft);
                    alert(data.message); // Or use a more sophisticated notification
                } else {
                    throw new Error(data.message || 'An unknown error occurred.');
                }

            } catch (error) {
                console.error('Error handling outcome action:', error);
                alert('Error: ' + error.message);
                // Restore button to its original state on error
                button.disabled = false;
                button.innerHTML = originalButtonText;
                button.title = originalButtonTitle;
            }
        }
    });
</script>
