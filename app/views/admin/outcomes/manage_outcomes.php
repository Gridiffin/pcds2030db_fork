<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes - Enhanced to follow agency side structure.
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
$outcomes = get_all_outcomes_data();

// Ensure $outcomes is always an array to prevent null reference errors
if (!is_array($outcomes)) {
    $outcomes = [];
}

// Get current reporting period for display purposes
$current_period = get_current_reporting_period();

// Separate outcomes into submitted and drafts
$submitted_outcomes = array_filter($outcomes, function($outcome) {
    return !isset($outcome['is_draft']) || $outcome['is_draft'] != 1;
});

$draft_outcomes = array_filter($outcomes, function($outcome) {
    return isset($outcome['is_draft']) && $outcome['is_draft'] == 1;
});

// Separate important outcomes from both submitted and draft outcomes
$important_submitted_outcomes = array_filter($submitted_outcomes, function($outcome) {
    return isset($outcome['is_important']) && $outcome['is_important'] == 1;
});

$important_draft_outcomes = array_filter($draft_outcomes, function($outcome) {
    return isset($outcome['is_important']) && $outcome['is_important'] == 1;
});

// Remove important outcomes from regular submitted and draft lists
$submitted_outcomes = array_filter($submitted_outcomes, function($outcome) {
    return !isset($outcome['is_important']) || $outcome['is_important'] != 1;
});

$draft_outcomes = array_filter($draft_outcomes, function($outcome) {
    return !isset($outcome['is_important']) || $outcome['is_important'] != 1;
});

// Include header
require_once '../../layouts/header.php';

// Get outcome creation setting
require_once ROOT_PATH . 'app/lib/admins/settings.php';
$allow_outcome_creation = get_outcome_creation_setting();

// Fetch existing outcome details for display (similar to agency side)
$result = $conn->query("SELECT detail_id, detail_name, detail_json FROM outcomes_details WHERE is_draft = 0 ORDER BY created_at DESC");
$detailsArray = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $jsonData = json_decode($row['detail_json'], true);
        $items = [];
        $layout_type = 'simple';
        if (isset($jsonData['layout_type']) && isset($jsonData['items'])) {
            $layout_type = $jsonData['layout_type'];
            $items = $jsonData['items'];
        } elseif (isset($jsonData['value']) && isset($jsonData['description'])) {
            $values = explode(';', $jsonData['value']);
            $descriptions = explode(';', $jsonData['description']);
            for ($i = 0; $i < count($values); $i++) {
                $items[] = [
                    'value' => $values[$i],
                    'description' => $descriptions[$i] ?? ''
                ];
            }
        }
        $detailsArray[] = [
            'id' => $row['detail_id'],
            'title' => $row['detail_name'],
            'layout_type' => $layout_type,
            'items' => $items,
            'value' => isset($jsonData['value']) ? $jsonData['value'] : implode(';', array_column($items, 'value')),
            'description' => isset($jsonData['description']) ? $jsonData['description'] : implode(';', array_column($items, 'description'))
        ];
    }
}

// Configure the modern page header
$header_config = [
    'title' => 'Manage Outcomes',
    'subtitle' => 'Admin interface to manage outcomes across all sectors',
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
        'url' => APP_URL . '/app/views/admin/outcomes/create_outcome.php',
        'class' => 'btn-primary',
        'icon' => 'fas fa-plus-circle',
        'id' => 'createMetricBtn'
    ];
}

// Add period badges to actions
if ($current_period) {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success"><i class="fas fa-calendar-alt me-1"></i> Q' . $current_period['quarter'] . '-' . $current_period['year'] . '</span>'
    ];
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success ms-2"><i class="fas fa-clock me-1"></i> Ends: ' . date('M j, Y', strtotime($current_period['end_date'])) . '</span>'
    ];
} else {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i> No Active Reporting Period</span>'
    ];
}

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Ensure Bootstrap JS is included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid px-4 py-4">
    <?php if (!$allow_outcome_creation): ?>
    <!-- Outcome Creation Notice -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Important:</strong> 
        Creation of new outcomes has been disabled by the administrator. This ensures outcomes remain consistent across reporting periods.
        Outcome history is now tracked, and existing outcomes cannot be deleted to maintain data integrity.
        <a href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php" class="alert-link">
            <i class="fas fa-cog ms-1"></i> Manage settings
        </a>
    </div>
    <?php endif; ?>

    <!-- Important Outcomes Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-star me-2"></i>Important Outcomes
            </h5>
            <span class="badge bg-dark text-warning">
                <?= count($detailsArray) + count($important_submitted_outcomes) + count($important_draft_outcomes) ?> Items
            </span>
        </div>
        <div class="card-body">
            <!-- Outcome Details Section -->
            <?php if (!empty($detailsArray)): ?>
                <div class="row mb-4">
                    <?php foreach ($detailsArray as $detail): ?>
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                    <h6 class="card-title mb-0"><?= htmlspecialchars($detail['title']) ?></h6>
                                    <span class="badge bg-secondary"><?= ucfirst($detail['layout_type'] ?? 'simple') ?></span>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $values = explode(';', $detail['value']);
                                    $descriptions = explode(';', $detail['description']);
                                    if (count($values) === 1): ?>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="text-primary fw-bold fs-3"><?= htmlspecialchars($values[0]) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($descriptions[0] ?? '') ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($values as $index => $val): ?>
                                                <div class="col-6 mb-2">
                                                    <div class="text-primary fw-bold"><?= htmlspecialchars($val) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($descriptions[$index] ?? '') ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary flex-fill" onclick="editMetricDetail(<?= $detail['id'] ?>)">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Important Outcomes from Database -->
            <?php if (!empty($important_submitted_outcomes) || !empty($important_draft_outcomes)): ?>
                
                <?php if (!empty($important_submitted_outcomes)): ?>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover border">
                            <thead class="table-light">
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
                                <?php foreach ($important_submitted_outcomes as $outcome): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($outcome['metric_id']) ?></span></td>
                                    <td><?= htmlspecialchars($outcome['sector_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($outcome['table_name']) ?></td>
                                    <td>
                                        <?php if ($outcome['year'] && $outcome['quarter']): ?>
                                            <span class="badge bg-success">Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($outcome['created_at'])) ?></td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($outcome['updated_at'])) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= APP_URL ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="unsubmitOutcome(<?= $outcome['metric_id'] ?>)" 
                                                    title="Unsubmit">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($important_draft_outcomes)): ?>
                    <h6 class="text-secondary mb-3 ms-3"><i class="fas fa-edit me-1"></i> Draft Important Outcomes</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover border">
                            <thead class="table-light">
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
                                <?php foreach ($important_draft_outcomes as $outcome): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($outcome['metric_id']) ?></span></td>
                                    <td><?= htmlspecialchars($outcome['sector_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($outcome['table_name']) ?></td>
                                    <td>
                                        <?php if ($outcome['year'] && $outcome['quarter']): ?>
                                            <span class="badge bg-success">Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($outcome['created_at'])) ?></td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($outcome['updated_at'])) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= APP_URL ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="submitOutcome(<?= $outcome['metric_id'] ?>)" 
                                                    title="Submit">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Empty State -->
            <?php if (empty($detailsArray) && empty($important_submitted_outcomes) && empty($important_draft_outcomes)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No important outcomes found.</p>
                    <p class="small text-muted">Important outcome details and outcomes marked as important will appear here.</p>
                </div>
            <?php endif; ?>

            <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
            <div id="successContainer" class="alert alert-success" style="display: none;"></div>
        </div>
    </div>

    <!-- Submitted Outcomes Section -->
    <?php if (!empty($submitted_outcomes)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-bar me-2"></i>Submitted Outcomes
            </h5>
            <span class="badge bg-light text-primary"><?= count($submitted_outcomes) ?> Outcomes</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
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
                        <?php foreach ($submitted_outcomes as $outcome): ?>
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
                                        <button type="button" 
                                                class="btn btn-outline-warning btn-sm w-100 unsubmit-outcome" 
                                                data-metric-id="<?php echo $outcome['metric_id']; ?>"
                                                title="Unsubmit Outcome">
                                            <i class="fas fa-undo"></i> Unsubmit
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
    <?php endif; ?>

    <!-- Draft Outcomes Section -->
    <?php if (!empty($draft_outcomes)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-edit me-2"></i>Draft Outcomes
            </h5>
            <span class="badge bg-light text-secondary"><?= count($draft_outcomes) ?> Drafts</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
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
                        <?php foreach ($draft_outcomes as $draft): ?>
                            <tr data-metric-id="<?php echo $draft['metric_id']; ?>">
                                <td><?php echo $draft['metric_id']; ?></td>
                                <td><?php echo htmlspecialchars($draft['sector_name'] ?? 'No Sector'); ?></td>
                                <td><?php echo htmlspecialchars($draft['table_name']); ?></td>
                                <td>
                                    <?php if (isset($draft['quarter']) && isset($draft['year'])): ?>
                                        <span class="status-indicator status-info">
                                            Q<?= $draft['quarter'] ?>-<?= $draft['year'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-indicator status-warning">Not Specified</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($draft['created_at'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($draft['updated_at'])); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Primary outcome actions">
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/view_outcome.php?metric_id=<?php echo $draft['metric_id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           title="View Outcome Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?php echo $draft['metric_id']; ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Edit Outcome">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/outcome_history.php?metric_id=<?php echo $draft['metric_id']; ?>" 
                                           class="btn btn-outline-info" 
                                           title="View Change History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </div>
                                    <div class="mt-1 d-grid status-action-container" data-metric-id="<?php echo $draft['metric_id']; ?>">
                                        <button type="button" 
                                                class="btn btn-outline-success btn-sm w-100 submit-outcome" 
                                                data-metric-id="<?php echo $draft['metric_id']; ?>"
                                                title="Submit Outcome">
                                            <i class="fas fa-redo"></i> Submit
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
    <?php endif; ?>

    <!-- No Outcomes Message -->
    <?php if (empty($submitted_outcomes) && empty($draft_outcomes)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Outcomes Found</h5>
            <p class="text-muted mb-4">
                No outcomes have been created in the system yet.
            </p>
            <?php if ($allow_outcome_creation): ?>
            <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/create_outcome.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create First Outcome
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Guidelines Section -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-info-circle me-2"></i>Admin Guidelines for Outcomes Management
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-users me-2 text-primary"></i>Cross-Sector Management</h6>
                        <p class="small mb-1">As an admin, you can view and manage outcomes across all sectors and agencies.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">All outcomes from every sector are displayed here.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-toggle-on me-2 text-success"></i>Submit/Unsubmit Control</h6>
                        <p class="small mb-1">You can submit draft outcomes or unsubmit already submitted outcomes.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Use this to help agencies manage their submission workflow.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-history me-2 text-info"></i>Change Tracking</h6>
                        <p class="small mb-1">View comprehensive history of changes made to any outcome.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Click the history icon to see all modifications.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-cog me-2 text-danger"></i>System Settings</h6>
                        <p class="small mb-1">Control system-wide outcome creation permissions and other settings.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Manage global outcome policies from system settings.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Outcome Detail Modal -->
<div class="modal fade" id="editOutcomeDetailModal" tabindex="-1" aria-labelledby="editOutcomeDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editOutcomeDetailModalLabel">Edit Outcome Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editOutcomeDetailForm">
          <div id="editItemsContainer"></div>
          <button type="button" class="btn btn-outline-secondary mt-2" id="addItemBtn">Add Item</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveOutcomeDetailBtn">Save Changes</button>
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

        const outcomesTableBody = document.querySelectorAll('tbody');

        outcomesTableBody.forEach(tbody => {
            tbody.addEventListener('click', function(event) {
                const targetButton = event.target.closest('.submit-outcome, .unsubmit-outcome');
                if (!targetButton) {
                    return; // Click was not on a relevant button
                }
                
                event.preventDefault();
                handleOutcomeAction(targetButton);
            });
        });

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
                    
                    // Move the row to the appropriate section if needed
                    location.reload(); // Reload to update the section placement
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

        // Important Outcomes editing functionality (from agency side)
        const metricDetails = <?= json_encode($detailsArray) ?>;
        let editingDetailId = null;

        function escapeHtml(text) {
            const map = {'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;','\'': '&#039;'};
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function showAlert(message, type) {
            const container = document.getElementById(`${type}Container`);
            if (container) {
                container.textContent = message;
                container.style.display = 'block';
                setTimeout(() => { container.style.display = 'none'; }, 5000);
            }
        }

        window.editMetricDetail = function(id) {
            // Ensure id is compared as number
            const detail = metricDetails.find(d => Number(d.id) === Number(id));
            if (!detail) return showAlert('Detail not found', 'error');
            
            editingDetailId = id;
            const items = detail.items || [];
            const container = document.getElementById('editItemsContainer');
            if (!container) return showAlert('Edit modal not found', 'error');
            
            container.innerHTML = '';
            items.forEach((item, idx) => {
                container.appendChild(createItemRow(item, idx));
            });
            
            if (items.length === 0) container.appendChild(createItemRow({}, 0));
            
            // Show modal
            const modalEl = document.getElementById('editOutcomeDetailModal');
            if (!modalEl) return showAlert('Edit modal not found', 'error');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        function createItemRow(item, idx) {
            const div = document.createElement('div');
            div.className = 'row g-2 align-items-end mb-2';
            div.innerHTML = `
              <div class="col-md-3">
                <label class="form-label">Value</label>
                <input type="text" class="form-control" name="value" value="${item.value ? escapeHtml(item.value) : ''}" />
              </div>
              <div class="col-md-5">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="2">${item.description ? escapeHtml(item.description) : ''}</textarea>
              </div>
              <div class="col-md-3">
                <label class="form-label">Label <span class="text-muted small">(optional)</span></label>
                <textarea class="form-control" name="label" rows="2">${item.label ? escapeHtml(item.label) : ''}</textarea>
              </div>
            `;
            return div;
        }

        const addItemBtn = document.getElementById('addItemBtn');
        if (addItemBtn) {
            addItemBtn.onclick = function() {
                const container = document.getElementById('editItemsContainer');
                container.appendChild(createItemRow({}, container.children.length));
            };
        }

        const saveBtn = document.getElementById('saveOutcomeDetailBtn');
        if (saveBtn) {
            saveBtn.onclick = function() {
                const container = document.getElementById('editItemsContainer');
                if (!container) return showAlert('Edit modal not found', 'error');
                
                const rows = container.querySelectorAll('.row');
                const items = [];
                
                rows.forEach(row => {
                    const value = row.querySelector('input[name="value"]').value.trim();
                    const description = row.querySelector('textarea[name="description"]').value.trim();
                    const label = row.querySelector('textarea[name="label"]').value.trim();
                    
                    if (value || description || label) {
                        const item = { value, description };
                        if (label) item.label = label;
                        items.push(item);
                    }
                });
                
                if (items.length === 0) {
                    showAlert('At least one item is required.', 'error');
                    return;
                }
                
                // Save via AJAX (using agency-side endpoint)
                fetch('<?php echo APP_URL; ?>/app/views/agency/outcomes/update_metric_detail.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: editingDetailId, items })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const idx = metricDetails.findIndex(d => Number(d.id) === Number(editingDetailId));
                        if (idx !== -1) {
                            metricDetails[idx].items = items;
                            metricDetails[idx].value = items.map(i => i.value).join(';');
                            metricDetails[idx].description = items.map(i => i.description).join(';');
                        }
                        
                        // Hide modal
                        const modalEl = document.getElementById('editOutcomeDetailModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                        showAlert('Outcome detail updated successfully.', 'success');
                        location.reload();
                    } else {
                        showAlert(data.message || 'Failed to update outcome detail.', 'error');
                    }
                })
                .catch(() => showAlert('Failed to update outcome detail.', 'error'));
            };
        }

        // Submit/Unsubmit functions for outcomes
        window.submitOutcome = function(metricId) {
            if (!confirm('Are you sure you want to submit this outcome?')) return;
            
            fetch('<?php echo APP_URL; ?>/app/ajax/submit_outcome.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ metric_id: metricId, action: 'submit' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('Outcome submitted successfully.', 'success');
                    location.reload();
                } else {
                    showAlert(data.message || 'Failed to submit outcome.', 'error');
                }
            })
            .catch(() => showAlert('Failed to submit outcome.', 'error'));
        };

        window.unsubmitOutcome = function(metricId) {
            if (!confirm('Are you sure you want to unsubmit this outcome? This will mark it as draft.')) return;
            
            fetch('<?php echo APP_URL; ?>/app/ajax/submit_outcome.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ metric_id: metricId, action: 'unsubmit' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('Outcome unsubmitted successfully.', 'success');
                    location.reload();
                } else {
                    showAlert(data.message || 'Failed to unsubmit outcome.', 'error');
                }
            })
            .catch(() => showAlert('Failed to unsubmit outcome.', 'error'));
        };
    });
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
