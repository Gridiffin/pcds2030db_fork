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
require_once ROOT_PATH . 'app/lib/agency_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : 0;

if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

$sector_id = $_SESSION['sector_id'] ?? 0;

// Get outcome data with flexible structure support
$query = "SELECT sod.*, u.username as submitted_by_username 
          FROM sector_outcomes_data sod 
          LEFT JOIN users u ON sod.submitted_by = u.user_id 
          WHERE sod.metric_id = ? AND sod.sector_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Outcome not found or you do not have permission to edit it.';
    header('Location: submit_outcomes.php');
    exit;
}

$row = $result->fetch_assoc();
$table_name = $row['table_name'];
$created_at = new DateTime($row['created_at']);
$updated_at = new DateTime($row['updated_at']);
$outcome_data = json_decode($row['data_json'], true);
$is_draft = (bool)$row['is_draft'];

// Initialize message variables
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_table_name = trim($_POST['table_name'] ?? '');
    $post_data_json = $_POST['data_json'] ?? '';
    $post_row_config = $_POST['row_config'] ?? '';
    $post_column_config = $_POST['column_config'] ?? '';
    $post_structure_type = $_POST['structure_type'] ?? 'flexible';

    if (empty($post_table_name) || empty($post_data_json)) {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        try {
            // Validate JSON data
            $data_check = json_decode($post_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data format.');
            }

            // Update the outcome
            $update_query = "UPDATE sector_outcomes_data 
                           SET table_name = ?, data_json = ?, row_config = ?, column_config = ?, table_structure_type = ?, updated_at = NOW() 
                           WHERE metric_id = ? AND sector_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssssii", 
                $post_table_name, 
                $post_data_json, 
                $post_row_config, 
                $post_column_config, 
                $post_structure_type,
                $outcome_id, 
                $sector_id
            );

            if ($update_stmt->execute()) {
                // Log the update
                log_audit_action(
                    'outcome_updated',
                    "Updated flexible outcome '{$post_table_name}' (ID: {$outcome_id}) for sector {$sector_id}",
                    'success',
                    $_SESSION['user_id']
                );

                // Redirect with success message
                header('Location: view_outcome.php?outcome_id=' . $outcome_id . '&saved=1');
                exit;
            } else {
                throw new Exception('Failed to update outcome: ' . $conn->error);
            }
        } catch (Exception $e) {
            $message = 'Error updating outcome: ' . $e->getMessage();
            $message_type = 'danger';
            error_log("Outcome update error: " . $e->getMessage());
        }
    }
}

// Get flexible structure configuration
$table_structure_type = $row['table_structure_type'] ?? 'monthly';
$row_config = json_decode($row['row_config'] ?? '{}', true);
$column_config = json_decode($row['column_config'] ?? '{}', true);

// Determine if this is a flexible structure or legacy
$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
} else {
    // Legacy structure - convert to flexible format
    $metric_names = $outcome_data['columns'] ?? [];
    
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
foreach ($rows as $row_def) {
    $row_data = ['row' => $row_def, 'metrics' => []];
    
    // Add data for each metric in this row
    if (isset($outcome_data['data'][$row_def['id']])) {
        $row_data['metrics'] = $outcome_data['data'][$row_def['id']];
    }
    
    $table_data[] = $row_data;
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
    'subtitle' => htmlspecialchars($table_name),
    'variant' => 'white',
    'actions' => [
        [
            'html' => '<button type="button" id="saveOutcomeBtn" class="btn btn-success me-2">
                        <i class="fas fa-save me-1"></i> Save Changes
                       </button>'
        ],
        [
            'url' => 'view_outcome.php?outcome_id=' . $outcome_id,
            'text' => 'Cancel',
            'icon' => 'fa-times',
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
                    <i class="fas fa-edit me-2"></i>Editing: <?= htmlspecialchars($table_name) ?>
                </h5>
                <div>
                    <?php if ($is_draft): ?>
                        <span class="badge bg-warning">
                            <i class="fas fa-file-alt me-1"></i> Draft
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Submitted
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($is_flexible): ?>
                        <span class="badge bg-primary ms-2">
                            <i class="fas fa-cogs me-1"></i> Flexible Structure
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Outcome ID:</strong> <?= $outcome_id ?>
                    </div>
                    <div class="mb-3">
                        <strong>Structure Type:</strong> 
                        <span class="badge bg-secondary"><?= ucfirst($table_structure_type) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong> <?= $created_at->format('F j, Y g:i A') ?>
                    </div>
                    <?php if ($created_at->format('Y-m-d H:i:s') !== $updated_at->format('Y-m-d H:i:s')): ?>
                    <div class="mb-3">
                        <strong>Last Updated:</strong> <?= $updated_at->format('F j, Y g:i A') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Mode: Editable Form -->
            <form id="editFlexibleOutcomeForm" method="post" action="">
                <!-- Table Name Editor -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label for="table_name" class="form-label">Outcome Name</label>
                        <input type="text" class="form-control" id="table_name" name="table_name" 
                               value="<?= htmlspecialchars($table_name) ?>" required>
                    </div>
                </div>

                <!-- Dynamic Table Structure Editor -->
                <div id="table-designer-container">
                    <!-- Will be populated by table structure designer -->
                </div>
                
                <!-- Live Preview Help -->
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-lightbulb me-2"></i>
                    <div>
                        <strong>Live Preview:</strong> Use the controls above to add or remove columns and rows. 
                        Changes appear immediately in the table below and your existing data is preserved.
                    </div>
                </div>

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
                    <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="window.location.href='view_outcome.php?outcome_id=<?= $outcome_id ?>'">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="saveOutcomeBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
