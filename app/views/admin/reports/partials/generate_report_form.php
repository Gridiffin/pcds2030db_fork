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
                <!-- The full report generation form HTML goes here (as in the original file) -->
                <?php /* ...form HTML from generate_reports.php... */ ?>
            </div>
        </div>
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