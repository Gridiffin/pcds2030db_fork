<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
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
            <div class="card-body" id="recentReportsContainer">
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