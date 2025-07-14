<?php
/**
 * Edit Outcome Details
 * 
 * Agency page to edit outcome details with support for flexible table structures
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agencies/outcomes.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: submit_outcomes.php');
    exit;
}

// Handle form submission
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = $_POST['data'] ?? [];
    if (update_outcome_data_by_code($outcome['code'], $post_data)) {
        header('Location: view_outcome.php?id=' . $outcome_id . '&saved=1');
        exit;
    } else {
        $message = 'Error updating outcome.';
        $message_type = 'danger';
    }
}

// Get flexible structure configuration
$table_structure_type = $outcome['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome['row_config'] ?? '{}', true);
$column_config = json_decode($outcome['column_config'] ?? '{}', true);

// Determine if this is a flexible structure or legacy
$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
} elseif (isset($outcome['columns'], $outcome['data']) && is_array($outcome['columns']) && is_array($outcome['data'])) {
    // New JSON structure: columns and data keys
    $columns = array_map(function($col) {
        return ['id' => $col, 'label' => $col, 'type' => 'number', 'unit' => ''];
    }, $outcome['columns']);
    $rows = array_map(function($row_id) {
        return ['id' => $row_id, 'label' => $row_id, 'type' => 'data'];
    }, array_keys($outcome['data']));
} else {
    // Legacy structure - convert to flexible format
    $metric_names = $outcome['columns'] ?? [];
    
    // Create default monthly rows
    $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    $rows = array_map(function($month) {
        return ['id' => $month, 'label' => $month, 'type' => 'data'];
    }, $month_names);
    
    $columns = array_map(function($col) {
        return ['id' => $col, 'label' => $col, 'type' => 'number', 'unit' => ''];
    }, $metric_names);
}

// Organize data for display
$table_data = [];
// Support both legacy and new JSON structure with 'data' key
if (isset($outcome['data']) && is_array($outcome['data'])) {
    // New structure: outcome_data['columns'] is an array of objects (id, label, type, unit, ...)
    // and outcome_data['data'][row_label][col_id]
    // Ensure $columns is an array of objects
    if (isset($outcome['columns'][0]) && is_array($outcome['columns'][0])) {
        $columns = $outcome['columns'];
    }
    // Build $rows from the data keys
    $row_labels = array_keys($outcome['data']);
    $rows = array_map(function($row_id) {
        return ['id' => $row_id, 'label' => $row_id, 'type' => 'data'];
    }, $row_labels);
    foreach ($rows as $row_def) {
        $row_data = ['row' => $row_def, 'metrics' => []];
        if (isset($outcome['data'][$row_def['id']]) && is_array($outcome['data'][$row_def['id']])) {
            $row_data['metrics'] = $outcome['data'][$row_def['id']];
        }
        $table_data[] = $row_data;
    }
} else {
    // Legacy structure: outcome_data[row_id][column_id]
    foreach ($rows as $row_def) {
        $row_data = ['row' => $row_def, 'metrics' => []];
        if (isset($outcome['data'][$row_def['id']])) {
            $row_data['metrics'] = $outcome['data'][$row_def['id']];
        }
        $table_data[] = $row_data;
    }
}

// Add CSS and JS references
$additionalStyles = [
    APP_URL . '/assets/css/table-structure-designer.css',
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Add JS references for edit mode
$additionalScripts = [
    APP_URL . '/assets/js/outcomes/edit-outcome.js',
    APP_URL . '/assets/js/outcomes/chart-manager.js',
    APP_URL . '/assets/js/table-calculation-engine.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Edit Outcome Details',
    'subtitle' => htmlspecialchars($outcome['title']),
    'variant' => 'white',
    'actions' => [
        [
            'html' => '<button type="button" class="btn btn-success me-2 saveOutcomeBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                       </button>'
        ],
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Cancel',
            'icon' => 'fas fa-times',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4">
    <!-- Error/Message display -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Outcome Information -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-edit me-2"></i>Editing: <?= htmlspecialchars($outcome['title']) ?>
                </h5>
                <!-- Removed badges for draft/submitted and flexible structure -->
            </div>
        </div>
        
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Structure Type:</strong> 
                        <span class="badge bg-secondary"><?= ucfirst($table_structure_type) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong> <?= isset($outcome['created_at']) ? htmlspecialchars($outcome['created_at']) : '-' ?>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong> <?= isset($outcome['updated_at']) ? htmlspecialchars($outcome['updated_at']) : '-' ?>
                    </div>
                </div>
            </div>

            <!-- Edit Mode: Editable Form -->
            <form id="editFlexibleOutcomeForm" method="post" action="">
                <!-- Table Name Editor -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label for="table_name" class="form-label">Outcome Name</label>
                        <input type="text" class="form-control" id="table_name" name="table_name" 
                               value="<?= htmlspecialchars($outcome['title']) ?>" required>
                    </div>
                </div>

                <?php if ($outcome['type'] === 'kpi'): ?>
                    <?php $kpi_data = is_array($outcome['data']) ? $outcome['data'] : json_decode($outcome['data'], true); ?>
                    <?php if (!empty($kpi_data)): ?>
                        <?php foreach ($kpi_data as $idx => $item): ?>
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Description</label>
                                    <input type="text" class="form-control" name="data[<?= $idx ?>][description]" value="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Value</label>
                                    <input type="text" class="form-control" name="data[<?= $idx ?>][value]" value="<?= htmlspecialchars($item['value'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unit</label>
                                    <input type="text" class="form-control" name="data[<?= $idx ?>][unit]" value="<?= htmlspecialchars($item['unit'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Extra</label>
                                    <input type="text" class="form-control" name="data[<?= $idx ?>][extra]" value="<?= htmlspecialchars($item['extra'] ?? '') ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center my-4">
                            <i class="fas fa-exclamation-circle me-2"></i> No KPI data available for this outcome.
                        </div>
                    <?php endif; ?>
                <?php elseif ($outcome['type'] === 'graph'): ?>
                <?php if (!empty($columns) && !empty($table_data)): ?>
                <!-- Editable Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="editableDataTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Row</th>
                                <?php foreach ($columns as $column): ?>
                                    <th class="text-center" data-column-id="<?= htmlspecialchars($column['id']) ?>">
                                        <div><?= htmlspecialchars($column['label']) ?></div>
                                        <?php if (!empty($column['unit'])): ?>
                                            <small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($table_data as $row_index => $row_data): ?>
                                <tr data-row-id="<?= htmlspecialchars($row_data['row']['id']) ?>" 
                                    class="<?= $row_data['row']['type'] === 'separator' ? 'table-secondary' : '' ?>">
                                    <td>
                                        <span class="row-badge <?= $row_data['row']['type'] === 'calculated' ? 'calculated' : '' ?>">
                                            <?= htmlspecialchars($row_data['row']['label']) ?>
                                        </span>
                                    </td>
                                    <?php foreach ($columns as $column): ?>
                                        <td class="text-center">
                                            <?php if ($row_data['row']['type'] === 'separator'): ?>
                                                —
                                            <?php elseif ($row_data['row']['type'] === 'calculated'): ?>
                                                <span class="calculated-value">
                                                    <?php 
                                                    $value = $row_data['metrics'][$column['id']] ?? 0;
                                                    if ($column['type'] === 'currency') {
                                                        echo 'RM ' . number_format($value, 2);
                                                    } elseif ($column['type'] === 'percentage') {
                                                        echo number_format($value, 1) . '%';
                                                    } else {
                                                        echo number_format($value, 2);
                                                    }
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <input type="number" 
                                                       class="form-control form-control-sm data-input text-end" 
                                                       data-row="<?= htmlspecialchars($row_data['row']['id']) ?>" 
                                                       data-column="<?= htmlspecialchars($column['id']) ?>" 
                                                       value="<?= $row_data['metrics'][$column['id']] ?? 0 ?>" 
                                                       step="0.01">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Total Row for numeric columns -->
                            <?php if (!empty($columns) && array_filter($columns, function($col) { return in_array($col['type'], ['number', 'currency']); })): ?>
                            <tr class="table-light total-row">
                                <td class="fw-bold">
                                    <span class="total-badge">TOTAL</span>
                                </td>
                                <?php foreach ($columns as $column): ?>
                                    <td class="fw-bold text-end" data-column="<?= htmlspecialchars($column['id']) ?>">
                                        <?php if (in_array($column['type'], ['number', 'currency'])): ?>
                                            <?php
                                            $total = 0;
                                            foreach ($table_data as $row_data) {
                                                if ($row_data['row']['type'] === 'data') {
                                                    $total += $row_data['metrics'][$column['id']] ?? 0;
                                                }
                                            }
                                            if ($column['type'] === 'currency') {
                                                echo 'RM ' . number_format($total, 2);
                                            } else {
                                                echo number_format($total, 2);
                                            }
                                            ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning text-center my-4">
                    <i class="fas fa-exclamation-circle me-2"></i> No data available for this outcome.
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Hidden form fields for structured data -->
                <input type="hidden" id="data_json" name="data_json" value="">
                <input type="hidden" id="row_config" name="row_config" value="<?= htmlspecialchars(json_encode($row_config)) ?>">
                <input type="hidden" id="column_config" name="column_config" value="<?= htmlspecialchars(json_encode($column_config)) ?>">
                <input type="hidden" name="structure_type" value="<?= htmlspecialchars($table_structure_type) ?>">
            </form>
        </div>
        
        <!-- Footer with Actions -->
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between align-items-center">
                <small>
                    <i class="fas fa-edit me-1"></i> Editing mode - Make your changes and click Save
                </small>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="window.location.href='submit_outcomes.php'">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success me-2 saveOutcomeBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Helper to get columns and rows from PHP
const columns = <?php echo json_encode($columns); ?>;
const rows = <?php echo json_encode(array_map(function($row) { return $row['id']; }, $rows)); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const saveBtns = document.querySelectorAll('.saveOutcomeBtn');
    const form = document.getElementById('editFlexibleOutcomeForm');
    if (form) {
        // Prevent default form submission (e.g. Enter key)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }
    if (saveBtns.length && form) {
        saveBtns.forEach(function(saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default button behavior
                // Build data JSON
                const data = {};
                rows.forEach(function(rowId) {
                    data[rowId] = {};
                    columns.forEach(function(col) {
                        const colId = col.id || col;
                        const input = document.querySelector(
                            `input.data-input[data-row="${rowId}"][data-column="${colId}"]`
                        );
                        if (input) {
                            data[rowId][colId] = parseFloat(input.value) || 0;
                        }
                    });
                });
                // Build final JSON structure
                const json = {
                    columns: columns, // full column definitions
                    data: data
                };
                document.getElementById('data_json').value = JSON.stringify(json);
                form.submit();
            });
        });
    }
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
