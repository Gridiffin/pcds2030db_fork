<?php
/**
 * View Flexible Outcome Details for Admin
 * 
 * Allows admin users to view flexible table structure outcomes from any sector.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/users.php';
require_once ROOT_PATH . 'app/lib/program_status_helpers.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if metric_id is provided
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

$metric_id = (int) $_GET['metric_id'];

// Get outcome data
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}

// Extract basic information
$table_name = $outcome_details['table_name'];
$sector_id = $outcome_details['sector_id'];
$sector_name = $outcome_details['sector_name'] ?? 'Unknown Sector';
$period_id = $outcome_details['period_id'];
$year = $outcome_details['year'] ?? 'N/A';
$quarter = $outcome_details['quarter'] ?? 'N/A';
$reporting_period_name = "Q{$quarter} {$year}";
$status = $outcome_details['status'] ?? 'submitted';
$overall_rating = $outcome_details['overall_rating'] ?? null;

$created_at = new DateTime($outcome_details['created_at']);
$updated_at = new DateTime($outcome_details['updated_at']);

// Extract flexible structure data
$table_structure_type = $outcome_details['table_structure_type'] ?? 'classic';
$row_config = $outcome_details['parsed_row_config'] ?? [];
$column_config = $outcome_details['parsed_column_config'] ?? [];

// Parse outcome data
$outcome_data = $outcome_details['parsed_data'] ?? [];
if (empty($outcome_data) && !empty($outcome_details['data_json'])) {
    $outcome_data = json_decode($outcome_details['data_json'], true) ?? [];
}

$is_flexible = ($table_structure_type === 'flexible');

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/table-structure-designer.css'
];

// Include header
require_once '../../layouts/header.php';

$header_config = [
    'title' => 'Admin: View Flexible Outcome',
    'subtitle' => "Viewing flexible outcome for {$sector_name}",
    'variant' => 'dark',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-light'
        ]
    ]
];

require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-table me-2"></i><?= htmlspecialchars($table_name) ?>
            </h5>
            <div>
                <span class="badge bg-info me-2">
                    <i class="fas fa-cogs me-1"></i> <?= ucfirst($table_structure_type) ?> Structure
                </span>
                <?= get_status_badge($status) ?>
                <?php if ($overall_rating): ?>
                    <?= get_rating_badge($overall_rating) ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure-view" 
                    type="button" role="tab" aria-controls="structure-view" aria-selected="true">
                    <i class="fas fa-project-diagram me-1"></i> Structure
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                    type="button" role="tab" aria-controls="table-view" aria-selected="false">
                    <i class="fas fa-table me-1"></i> Data Table
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-view" 
                    type="button" role="tab" aria-controls="chart-view" aria-selected="false">
                    <i class="fas fa-chart-line me-1"></i> Chart View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" data-bs-target="#metadata-view" 
                    type="button" role="tab" aria-controls="metadata-view" aria-selected="false">
                    <i class="fas fa-info-circle me-1"></i> Metadata
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="outcomeTabsContent">
            <!-- Structure View Tab -->
            <div class="tab-pane fade show active" id="structure-view" role="tabpanel" aria-labelledby="structure-tab">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-grip-lines me-2 text-primary"></i>Row Structure (<?= count($row_config) ?> rows)
                            </h6>
                            <div class="list-group">
                                <?php foreach ($row_config as $index => $row): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                            <strong><?= htmlspecialchars($row['label']) ?></strong>
                                            <small class="text-muted ms-2">(<?= htmlspecialchars($row['type']) ?>)</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-grip-vertical me-2 text-success"></i>Column Structure (<?= count($column_config) ?> columns)
                            </h6>
                            <div class="list-group">
                                <?php foreach ($column_config as $index => $column): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                                            <strong><?= htmlspecialchars($column['label']) ?></strong>
                                            <small class="text-muted ms-2">(<?= htmlspecialchars($column['type']) ?>)</small>
                                            <?php if (!empty($column['unit'])): ?>
                                                <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($column['unit']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Table View Tab -->
            <div class="tab-pane fade" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" class="position-sticky start-0 bg-dark">Category</th>
                                    <?php foreach ($column_config as $column): ?>
                                        <th scope="col" class="text-center">
                                            <?= htmlspecialchars($column['name']) ?>
                                            <?php if (!empty($column['unit'])): ?>
                                                <br><small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($row_config as $row): ?>
                                    <tr>
                                        <td class="fw-bold position-sticky start-0 bg-light">
                                            <?= htmlspecialchars($row['label']) ?>
                                        </td>
                                        <?php foreach ($column_config as $column): ?>
                                            <td class="text-center">
                                                <?php
                                                $value = $outcome_data[$row['label']][$column['id']] ?? '';
                                                if (is_numeric($value)) {
                                                    echo number_format((float)$value, 2);
                                                } else {
                                                    echo htmlspecialchars($value);
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Total row for numeric columns -->
                                <?php
                                $has_numeric_columns = false;
                                foreach ($column_config as $column) {
                                    if ($column['type'] === 'number') {
                                        $has_numeric_columns = true;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($has_numeric_columns): ?>
                                    <tr class="table-light">
                                        <td class="fw-bold position-sticky start-0 bg-warning">
                                            <span class="total-badge">TOTAL</span>
                                        </td>
                                        <?php foreach ($column_config as $column): ?>
                                            <td class="fw-bold text-center">
                                                <?php if ($column['type'] === 'number'): ?>
                                                    <?php
                                                    $total = 0;
                                                    foreach ($row_config as $row) {
                                                        if (isset($outcome_data[$row['label']][$column['id']])) {
                                                            $total += floatval($outcome_data[$row['label']][$column['id']]);
                                                        }
                                                    }
                                                    echo number_format($total, 2);
                                                    ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="chartType" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartType">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="radar">Radar Chart</option>
                                    <option value="doughnut">Doughnut Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartColumnSelect" class="form-label">Columns to Display</label>
                                <select class="form-select" id="chartColumnSelect">
                                    <!-- Options will be populated by JavaScript -->
                                </select>
                                <small class="text-muted">Select which data columns to display in the chart</small>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="cumulativeToggle">
                                <label class="form-check-label" for="cumulativeToggle">
                                    Cumulative Display
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-end">
                            <div class="btn-group mt-4" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadChartImage" title="Download Chart">
                                    <i class="fas fa-image"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadDataCSV" title="Download CSV">
                                    <i class="fas fa-file-csv"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="metricChart"></canvas>
                    </div>
                    
                    <!-- Chart Info -->
                    <div class="mt-3">
                        <div class="alert alert-light">
                            <h6><i class="fas fa-info-circle me-2"></i>Chart Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small><strong>Structure Type:</strong> <?= ucfirst($table_structure_type) ?></small><br>
                                    <small><strong>Rows:</strong> <?= count($row_config) ?> categories</small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Columns:</strong> <?= count($column_config) ?> data series</small><br>
                                    <small><strong>Total Data Points:</strong> <?= count($row_config) * count($column_config) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Metadata View Tab -->
            <div class="tab-pane fade" id="metadata-view" role="tabpanel" aria-labelledby="metadata-tab">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Outcome ID:</strong> <?= $metric_id ?>
                            </div>
                            <div class="mb-3">
                                <strong>Sector:</strong> <?= htmlspecialchars($sector_name) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Reporting Period:</strong> <?= htmlspecialchars($reporting_period_name) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> <?= get_status_badge($status) ?>
                            </div>
                            <?php if ($overall_rating): ?>
                                <div class="mb-3">
                                    <strong>Overall Rating:</strong> <?= get_rating_badge($overall_rating) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Table Structure Type:</strong> 
                                <span class="badge bg-info"><?= ucfirst($table_structure_type) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Created:</strong> <?= $created_at->format('M j, Y g:i A') ?>
                            </div>
                            <div class="mb-3">
                                <strong>Last Updated:</strong> <?= $updated_at->format('M j, Y g:i A') ?>
                            </div>
                            <div class="mb-3">
                                <strong>Data Points:</strong> 
                                <?= count($row_config) ?> rows × <?= count($column_config) ?> columns = <?= count($row_config) * count($column_config) ?> total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between align-items-center">
                <small>
                    <i class="fas fa-info-circle me-1"></i> Admin view of flexible outcome structure
                </small>
                <div>
                    <a href="manage_outcomes.php" class="btn btn-secondary btn-sm me-2">
                        <i class="fas fa-list me-1"></i> Back to List
                    </a>
                    <a href="view_outcome.php?metric_id=<?= $metric_id ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-table me-1"></i> Classic View
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load enhanced charting script -->
<script src="<?= APP_URL ?>/assets/js/charts/enhanced-outcomes-chart.js"></script>

<!-- Initialize the enhanced chart with flexible structure data -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare structure data for chart
        const structure = {
            rows: <?= json_encode($row_config) ?>,
            columns: <?= json_encode($column_config) ?>
        };
        
        // Initialize enhanced chart with flexible data
        initEnhancedOutcomesChart(
            <?= json_encode($outcome_data) ?>, 
            structure,
            "<?= addslashes($table_name) ?>",
            "flexible"
        );
    });
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
