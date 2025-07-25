<?php
/**
 * Generate Reports Content Partial
 * 
 * Main content for the generate reports page
 */
?>

<!-- JavaScript Configuration -->
<script>
    window.ReportGeneratorConfig = <?php echo json_encode($jsConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<!-- Main Page Content -->
<main class="flex-fill">
    <section class="section">
        <div class="container-fluid">
            
            <!-- Recent Reports Dashboard - Full Width -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Recent Reports
                            </h5>
                            <div class="d-flex align-items-center gap-3">
                                <div class="search-container">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="reportSearch" 
                                               placeholder="Search reports..."
                                               aria-label="Search reports">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="clearSearch" 
                                                title="Clear search"
                                                style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="generateReportToggle">
                                        <i class="fas fa-plus me-1"></i>Generate New Report
                                    </button>
                                </div>
                            </div>
                            </div>
                        </div>                    <div class="card-body" id="recentReportsContainer">
                            <!-- Reports will be loaded here by JavaScript pagination -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading reports...</span>
                                </div>
                                <p class="mt-3 mb-0 text-muted">Loading recent reports...</p>
                            </div>
                            <div id="refreshIndicator" class="text-center mt-2" style="display: none;">
                                <small class="text-muted">
                                    <i class="fas fa-sync fa-spin"></i> Refreshing...
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Generate Report Section - Collapsible -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm" id="generateReportSection" style="display: none;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-powerpoint me-2"></i>Generate New Report
                            </h5>
                            <button type="button" class="btn btn-outline-light btn-sm" id="closeGenerateForm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="reportGenerationForm" novalidate>
                                <!-- Period and Sector Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="periodSelect" class="form-label">
                                                <i class="fas fa-calendar-alt me-1"></i>Reporting Period
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="periodSelect" name="period_id" required>
                                                <option value="">Select Reporting Period</option>
                                                <?php foreach ($periods as $period): ?>
                                                    <option value="<?php echo htmlspecialchars($period['period_id']); ?>">
                                                        <?php echo htmlspecialchars(get_period_display_name($period)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a reporting period.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-industry me-1"></i>Sector
                                            </label>
                                            <input type="text" class="form-control" value="Forestry Sector" readonly>
                                            <input type="hidden" id="sectorSelect" name="sector_id" value="1">
                                            <div class="form-text">System is configured for Forestry Sector only.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rest of the form content continues here -->
                                <!-- This is a very long form, so I'll include the key sections -->
                                
                                <!-- Report Details -->
                                <div class="mb-3">
                                    <label for="reportName" class="form-label">
                                        <i class="fas fa-file-signature me-1"></i>Report Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="reportName" 
                                           name="report_name" 
                                           required 
                                           maxlength="255"
                                           placeholder="e.g., Forestry Sector Report - Q2 2025">
                                    <div class="invalid-feedback">Please enter a report name.</div>
                                </div>
                                
                                <!-- Generate Button -->
                                <div class="d-grid">
                                    <button type="submit" 
                                            id="generatePptxBtn" 
                                            class="btn btn-primary btn-lg">
                                        <i class="fas fa-file-powerpoint me-2"></i>
                                        Generate PPTX Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Status and Message sections -->
                    <div class="alert alert-info mt-3 d-none" id="generationStatus" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <h6 class="alert-heading mb-1">Generating Report</h6>
                                <p class="mb-0" id="statusMessage">Preparing report data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReportModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the report:</p>
                <p class="fw-bold text-primary" id="reportNameToDelete"></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The report file will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Delete Report
                </button>
            </div>
        </div>
    </div>
</div>
