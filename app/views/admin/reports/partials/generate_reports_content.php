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
    // Set global APP_URL for compatibility with existing scripts
    window.APP_URL = window.ReportGeneratorConfig.appUrl;
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
                                                <?php 
                                                // Group periods by type and year for better organization
                                                $groupedPeriods = [];
                                                foreach ($periods as $period) {
                                                    $year = $period['year'];
                                                    $type = $period['period_type'];
                                                    
                                                    if (!isset($groupedPeriods[$year])) {
                                                        $groupedPeriods[$year] = ['quarter' => [], 'half' => [], 'yearly' => []];
                                                    }
                                                    $groupedPeriods[$year][$type][] = $period;
                                                }
                                                
                                                // Sort years in descending order
                                                krsort($groupedPeriods);
                                                
                                                foreach ($groupedPeriods as $year => $types): ?>
                                                    <optgroup label="<?php echo $year; ?>">
                                                        <?php if (!empty($types['half'])): ?>
                                                            <optgroup label="&nbsp;&nbsp;Half Yearly">
                                                                <?php 
                                                                // Sort half yearly periods by period number
                                                                usort($types['half'], function($a, $b) { return $b['period_number'] - $a['period_number']; });
                                                                foreach ($types['half'] as $period): ?>
                                                                    <option value="<?php echo htmlspecialchars($period['period_id']); ?>">
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(get_period_display_name($period)); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($types['quarter'])): ?>
                                                            <optgroup label="&nbsp;&nbsp;Quarters">
                                                                <?php 
                                                                // Sort quarterly periods by period number (descending)
                                                                usort($types['quarter'], function($a, $b) { return $b['period_number'] - $a['period_number']; });
                                                                foreach ($types['quarter'] as $period): ?>
                                                                    <option value="<?php echo htmlspecialchars($period['period_id']); ?>">
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(get_period_display_name($period)); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($types['yearly'])): ?>
                                                            <optgroup label="&nbsp;&nbsp;Yearly">
                                                                <?php foreach ($types['yearly'] as $period): ?>
                                                                    <option value="<?php echo htmlspecialchars($period['period_id']); ?>">
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(get_period_display_name($period)); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endif; ?>
                                                    </optgroup>
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

                                <!-- Program Selection Section -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-list-check me-1"></i>Select Programs to Include
                                        <small class="text-muted">(Optional - Multi-agency reports supported)</small>
                                    </label>
                                    <!-- Enhanced Filter Bar with Integrated Agency Filtering -->
                                    <div class="filter-bar border rounded p-3 mb-3 bg-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   id="programSearchInput" 
                                                                   placeholder="Search programs, numbers, agencies...">
                                                            <button class="btn btn-outline-secondary" 
                                                                    type="button" 
                                                                    id="clearSearchBtn"
                                                                    title="Clear search">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="program-count-display">
                                                            <span class="badge bg-info" id="programCountBadge">
                                                                <i class="fas fa-list me-1"></i>
                                                                <span id="programCount">0</span> programs found
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="bulk-actions d-flex gap-2 justify-content-end">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-sm" 
                                                            id="selectAllPrograms"
                                                            title="Select all visible programs">
                                                        <i class="fas fa-check-square me-1"></i>Select All
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-secondary btn-sm" 
                                                            id="clearAllPrograms"
                                                            title="Clear all selections">
                                                        <i class="fas fa-square me-1"></i>Clear All
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning btn-sm" 
                                                            id="resetAllFilters"
                                                            title="Reset all filters">
                                                        <i class="fas fa-undo me-1"></i>Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Integrated Agency Filter Row -->
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="agency-filter-section">
                                                    <small class="text-muted fw-bold">
                                                        <i class="fas fa-building me-1"></i>Filter by Agency:
                                                    </small>
                                                    <div class="mt-1" id="agencyFilterTags">
                                                        <button type="button" class="btn btn-outline-primary btn-sm me-1 mb-1 agency-filter-btn active" data-agency-id="all">
                                                            <i class="fas fa-globe me-1"></i>All Agencies
                                                        </button>
                                                        <!-- Agency filter buttons will be populated here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="alert alert-info border-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Multi-agency reports:</strong> You can select programs from different agencies for one report. 
                                            Use the agency filters above to quickly find programs, or search by agency name.
                                            If no programs are selected, all filtered programs will be included.
                                        </div>
                                    </div>
                                    <div id="programSelector" class="program-selector">
                                        <div class="program-selector-container border rounded p-3" 
                                             style="max-height: 300px; overflow-y: auto;" 
                                             role="region" 
                                             aria-label="Program selection">
                                            <div class="alert alert-light text-center">
                                                <i class="fas fa-arrow-up me-2"></i>
                                                Please select a reporting period above to load available programs.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Target Selection Section -->
                                <div class="mb-4" id="targetSelectionSection" style="display: none;">
                                    <label class="form-label">
                                        <i class="fas fa-bullseye me-1"></i>Select Targets to Include
                                        <small class="text-muted">(Optional - Choose specific targets per program)</small>
                                    </label>
                                    <div class="target-selection-info mb-3">
                                        <div class="alert alert-info border-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Target Selection:</strong> Review and select specific targets to include in your report. 
                                            All targets are selected by default. Uncheck targets you want to exclude.
                                        </div>
                                    </div>
                                    <div class="target-selector-controls mb-3">
                                        <div class="d-flex gap-2">
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    id="selectAllTargets"
                                                    title="Select all targets">
                                                <i class="fas fa-check-square me-1"></i>Select All Targets
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-sm" 
                                                    id="clearAllTargets"
                                                    title="Deselect all targets">
                                                <i class="fas fa-square me-1"></i>Deselect All
                                            </button>
                                            <div class="ms-auto">
                                                <span class="badge bg-primary" id="selectedTargetCount">
                                                    <i class="fas fa-bullseye me-1"></i>
                                                    <span id="targetCount">0</span> targets selected
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="targetSelector" class="target-selector">
                                        <div class="target-selector-container border rounded p-3" 
                                             style="max-height: 400px; overflow-y: auto;" 
                                             role="region" 
                                             aria-label="Target selection">
                                            <div class="alert alert-light text-center">
                                                <i class="fas fa-arrow-up me-2"></i>
                                                Please select programs above to load available targets.
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                
                                <div class="mb-3">
                                    <label for="reportDescription" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Description
                                        <small class="text-muted">(Optional)</small>
                                    </label>
                                    <textarea class="form-control" 
                                              id="reportDescription" 
                                              name="description" 
                                              rows="3" 
                                              maxlength="1000"
                                              placeholder="Brief description of the report content"></textarea>
                                    <div class="form-text">Maximum 1000 characters</div>
                                </div>
                                            
                                <!-- Report Options -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="isPublic" 
                                               name="is_public" 
                                               value="1">
                                        <label class="form-check-label" for="isPublic">
                                            <i class="fas fa-share-alt me-1"></i>Make available to agencies
                                        </label>
                                        <div class="form-text">
                                            When enabled, agencies will be able to view and download this report.
                                        </div>
                                    </div>
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
                    <!-- Generation Status Alert -->
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
                    
                    <!-- Success Alert -->
                    <div class="alert alert-success mt-3 d-none" id="successMessage" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle me-2"></i>Report Generated Successfully
                        </h6>
                        <p class="mb-3">Your report has been generated and saved successfully.</p>
                        <div class="d-flex gap-2">
                            <a href="#" id="downloadLink" class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i>Download PPTX
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="generateAnotherBtn">
                                <i class="fas fa-plus me-1"></i>Generate Another
                            </button>
                        </div>
                    </div>
                    
                    <!-- Error Alert -->
                    <div class="alert alert-danger mt-3 d-none" id="errorMessage" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>Error
                        </h6>
                        <p id="errorText" class="mb-3">Something went wrong. Please try again.</p>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="retryBtn">
                            <i class="fas fa-redo me-1"></i>Try Again
                        </button>
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
