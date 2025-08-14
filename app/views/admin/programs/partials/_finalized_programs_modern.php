<?php
// Ensure the necessary variables are defined to prevent errors when the partial is loaded without the controller.
?>
<!-- Programs with Finalized Submissions Card - Modern Box Layout -->
<div class="card shadow-sm mb-4 w-100 admin-finalized-programs-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-view-programs-card-title m-0 d-flex align-items-center">
            <i class="fas fa-check-circle text-success me-2"></i>
            Programs with Finalized Submissions
            <span class="badge bg-success ms-2" title="These programs have finalized submissions">
                <i class="fas fa-check me-1"></i> Finalized
            </span>
            <span class="badge bg-secondary ms-2" id="finalized-count"><?php echo count($programs_with_submissions); ?></span>
        </h5>
    </div>
    
    <!-- Finalized Programs Filters -->
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-3 col-sm-12">
                <label for="finalizedProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="finalizedProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="finalizedRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="finalizedRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="monthly_target_achieved">Monthly Target Achieved</option>
                    <option value="on_track_for_year">On Track for Year</option>
                    <option value="severe_delay">Severe Delay</option>
                    <option value="not_started">Not Started</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="finalizedAgencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="finalizedAgencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['agency_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="finalizedInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="finalizedInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 d-flex align-items-end">
                <button id="resetFinalizedFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="finalizedFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2">
        <!-- Loading indicator -->
        <div class="pagination-loading-overlay" id="paginationLoadingOverlay" style="display: none;">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading programs...</div>
                <div class="loading-progress">
                    <div class="progress-bar" id="loadingProgressBar"></div>
                </div>
            </div>
        </div>
        
        <!-- Programs Container -->
        <div class="admin-programs-container" id="finalizedProgramsContainer">
            <?php if (empty($programs_with_submissions)): ?>
                <div class="admin-programs-empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="empty-title">No Finalized Programs Found</h3>
                    <p class="empty-description">There are no programs with finalized submissions matching your current filters. Try adjusting your search criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($programs_with_submissions as $program): ?>
                    <?php 
                    // Set the program variable for the partial
                    $current_program = $program;
                    $program = $current_program; // Make sure $program is available in the partial
                    include __DIR__ . '/admin_program_box.php'; 
                    ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4" id="finalizedPaginationContainer" style="display: none;">
            <nav aria-label="Programs pagination">
                <ul class="pagination" id="finalizedPagination">
                    <!-- Pagination will be dynamically generated -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<style>
/* Additional CSS for program type info */
.admin-program-type-info {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--color-secondary);
    font-size: var(--font-size-sm);
}

.admin-program-type-info i {
    color: var(--color-info);
}

/* Loading overlay styles */
.pagination-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(2px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.loading-content {
    text-align: center;
    padding: 2rem;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #28a745;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 1rem;
}

.loading-progress {
    width: 200px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin: 0 auto;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 2px;
    width: 0%;
    transition: width 0.3s ease;
}

/* Smooth transitions for program cards */
.admin-program-box {
    transition: opacity 0.3s ease, transform 0.2s ease;
}

.admin-program-box.fade-out {
    opacity: 0;
    transform: translateY(-10px);
}

.admin-program-box.fade-in {
    opacity: 1;
    transform: translateY(0);
}

/* Container positioning for overlay */
.admin-programs-container {
    position: relative;
    min-height: 200px;
}
</style>