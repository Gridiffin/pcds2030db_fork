<?php
/**
 * View Outcome Content Partial
 * Content section for the view outcome page
 */

// Include page header with configuration
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<div class="outcomes-container">
    <!-- Success message -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show outcomes-alert" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Outcome Information Card -->
    <div class="outcomes-section">
        <div class="outcomes-section-header">
            <h3>
                <i class="fas fa-info-circle me-2"></i>Outcome Information
            </h3>
        </div>
        <div class="outcomes-section-content">
            <div class="outcome-info-grid">
                <div class="outcome-info-item">
                    <div class="outcome-info-label">Code</div>
                    <div class="outcome-info-value"><?= htmlspecialchars($outcome['code']) ?></div>
                </div>
                <div class="outcome-info-item">
                    <div class="outcome-info-label">Type</div>
                    <div class="outcome-info-value"><?= htmlspecialchars(ucfirst($outcome['type'])) ?></div>
                </div>
                <div class="outcome-info-item">
                    <div class="outcome-info-label">Last Updated</div>
                    <div class="outcome-info-value">
                        <?= (new DateTime($outcome['updated_at']))->format('F j, Y g:i A') ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($outcome['description'])): ?>
                <div class="outcome-info-item">
                    <div class="outcome-info-label">Description</div>
                    <div class="outcome-info-value"><?= htmlspecialchars($outcome['description']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Data Content -->
    <?php if ($outcome['type'] === 'kpi'): ?>
        <!-- KPI Display -->
        <?php require_once __DIR__ . '/kpi_display.php'; ?>
    <?php else: ?>
        <!-- Graph/Table Display -->
        <?php if ($has_data): ?>
            <div class="outcomes-section">
                <div class="outcomes-section-header">
                    <h3>
                        <i class="fas fa-table me-2"></i>Data View
                    </h3>
                </div>
                <div class="outcomes-section-content">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" id="outcomeDetailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                                type="button" role="tab" aria-controls="table-view" aria-selected="true">
                                <i class="fas fa-table me-1"></i> Table View
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-view" 
                                type="button" role="tab" aria-controls="chart-view" aria-selected="false">
                                <i class="fas fa-chart-line me-1"></i> Chart View
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="outcomeDetailTabsContent">
                        <!-- Table View -->
                        <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                            <?php require_once __DIR__ . '/table_display.php'; ?>
                        </div>

                        <!-- Chart View -->
                        <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                            <?php require_once __DIR__ . '/chart_display.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="outcomes-section">
                <div class="outcomes-section-content">
                    <div class="outcome-empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h4>No Data Available</h4>
                        <p>This outcome doesn't have any data to display yet. Please contact your administrator to add data.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- JavaScript data for Chart.js -->
<?php if ($has_data): ?>
<script>
    // Pass data to JavaScript for chart functionality
    window.tableData = <?= json_encode($tableData) ?>;
    window.tableColumns = <?= json_encode($tableColumns) ?>;
    window.tableRows = <?= json_encode($tableRows) ?>;
</script>
<?php endif; ?>
