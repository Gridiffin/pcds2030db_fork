<?php
/**
 * Submit Outcomes
 * 
 * Interface for agency users to submit sector outcomes.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';

if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$pageTitle = 'Submit Sector Outcomes';

$settings = [
    'allow_create_outcome' => true
];

$current_period = get_current_reporting_period();
$show_form = $current_period && $current_period['status'] === 'open';

$message = '';
$message_type = '';

// Get outcomes for the sector
$outcomes = get_agency_sector_outcomes($_SESSION['sector_id']);
if (!is_array($outcomes)) {
    $outcomes = [];
}

$draft_outcomes = get_draft_outcome($_SESSION['sector_id']);
if (!is_array($draft_outcomes)) {
    $draft_outcomes = [];
}

// Separate important outcomes from both submitted and draft outcomes
$important_submitted_outcomes = array_filter($outcomes, function($outcome) {
    return isset($outcome['is_important']) && $outcome['is_important'] == 1;
});

$important_draft_outcomes = array_filter($draft_outcomes, function($outcome) {
    return isset($outcome['is_important']) && $outcome['is_important'] == 1;
});

// Remove important outcomes from regular submitted and draft lists
$outcomes = array_filter($outcomes, function($outcome) {
    return !isset($outcome['is_important']) || $outcome['is_important'] != 1;
});

$draft_outcomes = array_filter($draft_outcomes, function($outcome) {
    return !isset($outcome['is_important']) || $outcome['is_important'] != 1;
});

$additionalScripts = [];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Submit Sector Outcomes',
    'subtitle' => 'Update your sector-specific outcomes for this reporting period',
    'variant' => 'green',
    'actions' => []
];

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

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<?php
// Fetch existing outcome details for display
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
?>
<?php if (!$show_form): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= $error_message ?? 'No active reporting period is currently open.' ?> Please try again when a reporting period is active.
    </div>
<?php else: ?>
    <!-- Period Information -->
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2"></i>
            <div>
                <strong>Current Reporting Period:</strong> 
                Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?> 
                (<?= date('d M Y', strtotime($current_period['start_date'])) ?> - 
                <?= date('d M Y', strtotime($current_period['end_date'])) ?>)
            </div>
        </div>
    </div>    

    <!-- Add Outcomes Button Section -->
    <?php if (!empty($settings['allow_create_outcome']) && $settings['allow_create_outcome']): ?>
    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/create_outcome.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Create Outcome (Classic)
            </a>
            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/create_outcome_flexible.php" class="btn btn-primary">
                <i class="fas fa-cogs me-1"></i> Create Flexible Outcome
            </a>
        </div>
        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Use <strong>Classic</strong> for traditional monthly data tables, or <strong>Flexible</strong> to design custom table structures.
            </small>
        </div>
    </div>
    <?php endif; ?>

    <!-- Important Outcomes Section (moved below Add Outcomes button) -->
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
                                    <th>Table Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($important_submitted_outcomes as $outcome): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($outcome['metric_id']) ?></span></td>
                                    <td><?= htmlspecialchars($outcome['table_name']) ?></td>
                                    <td><span class="badge bg-success">Submitted</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/view_outcome.php?outcome_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/edit_outcomes.php?outcome_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
                                    <th>Table Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($important_draft_outcomes as $outcome): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($outcome['metric_id']) ?></span></td>
                                    <td><?= htmlspecialchars($outcome['table_name']) ?></td>
                                    <td><span class="badge bg-warning text-dark">Draft</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/view_outcome.php?outcome_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/edit_outcomes.php?outcome_id=<?= $outcome['metric_id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
    <script>
    // Embed detailsArray as JS object for edit lookup
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
    function editMetricDetail(id) {
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
    }
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
    // Ensure DOM is ready before assigning event handlers
    window.addEventListener('DOMContentLoaded', function() {
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
                // Save via AJAX
                fetch('update_metric_detail.php', {
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
    });
    </script>

    <!-- Submitted Outcomes Section (remains below) -->
    <?php if (!empty($outcomes)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-bar me-2"></i>Submitted Outcomes
            </h5>
            <span class="badge bg-light text-primary"><?= count($outcomes) ?> Outcomes</span>
        </div>
        <div class="card-body">
            <?php if (empty($outcomes)): ?>                <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                No outcomes have been submitted for your sector yet.
            </div>
        <?php else: ?>
            <p class="mb-3">These outcomes have been submitted for the current reporting period (Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?>).</p>
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th width="70%">Outcome Name</th>
                            <th width="30%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fix: Use 'metric_id' as the unique key for outcomes and drafts
                        $unique_outcomes = [];
                        foreach ($outcomes as $outcome):
                            if (!in_array($outcome['metric_id'], $unique_outcomes)):
                                $unique_outcomes[] = $outcome['metric_id'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($outcome['table_name']) ?></strong></td>
                                <td class="text-center">
                                    <a href="view_outcome.php?outcome_id=<?= $outcome['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($draft_outcomes)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-edit me-2"></i>Outcomes Drafts
            </h5>
            <span class="badge bg-light text-primary"><?= count($draft_outcomes) ?> Drafts</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover borsder">
                    <thead class="table-light">
                        <tr>
                            <th width="60%">Outcomes</th>
                            <th width="40%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $unique_drafts = [];
                        foreach ($draft_outcomes as $draft) {
                            if (!in_array($draft['metric_id'], $unique_drafts)) {
                                $unique_drafts[] = $draft['metric_id'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($draft['table_name']) ?></strong></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/view_outcome.php?outcome_id=<?= $draft['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/agency/outcomes/edit_outcomes.php?outcome_id=<?= $draft['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="submit_draft_outcome.php?outcome_id=<?= $draft['metric_id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to submit this draft outcome?');">
                                            <i class="fas fa-check me-1"></i> Submit
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/delete_outcome.php?outcome_id=<?= $draft['metric_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this outcome draft?');">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title m-0">
            <i class="fas fa-info-circle me-2"></i>Guidelines for Outcomes
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-table me-2 text-primary"></i>Outcomes Tables</h6>
                    <p class="small mb-1"> a table for each related set of outcomes that share the same reporting frequency.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Production Volume"</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-columns me-2 text-success"></i>Outcomes Columns</h6>
                    <p class="small mb-1">Each column represents a specific outcomes with its own unit of measurement.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Exports", "Forest Coverage"</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-ruler me-2 text-info"></i>Measurement Units</h6>
                    <p class="small mb-1">Specify the appropriate unit for each outcome. Units can be set individually or for all columns.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Examples: RM, Ha, %, tons, m³</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-chart-line me-2 text-danger"></i>Data Formatting</h6>
                    <p class="small mb-1">Enter numbers directly without commas or symbols. Use consistent decimal places.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Correct: 1250.50 <br>Incorrect: 1,250.50 RM</div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-primary mt-4 mb-0">
            <div class="d-flex">
                <div class="me-3 fs-4">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h6 class="alert-heading">Tips for Effective Metrics</h6>
                    <ul class="mb-0 ps-3">
                        <li>Use clear, descriptive names for tables and metrics</li>
                        <li>Ensure consistent units across similar metrics</li>
                        <li>Review your data before submission for accuracy</li>
                        <li>For metrics with multiple units, use the "Set All Units" button for consistency</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?>