<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Reporting Periods Management</h5>
                <?php /* Remove redundant button, dashboard_header.php provides this
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                    <i class="fas fa-plus me-1"></i> Add New Period
                </button>
                */ ?>
            </div>
            <div class="card-body">
                <!-- Periods table will be loaded here -->
                <div id="periodsTable">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Loading periods...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Period Modal -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="addPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPeriodModalLabel">Add New Reporting Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPeriodForm">
                    <!-- Hidden fields -->
                    <input type="hidden" id="periodId" name="period_id" value="">
                    <input type="hidden" id="period-dates-changed" value="false">
                    <input type="hidden" id="useCustomDates" name="use_custom_dates" value="0">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="periodType" class="form-label">Period Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="periodType" name="period_type" required>
                                <option value="" disabled selected>Select Period Type</option>
                                <option value="quarter">Quarter</option>
                                <option value="half">Half Yearly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="periodNumber" class="form-label">Period Number <span class="text-danger">*</span></label>
                            <select class="form-select" id="periodNumber" name="period_number" required>
                                <option value="" disabled selected>Select Number</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="year" name="year" required 
                                   placeholder="YYYY" min="2000" max="2099">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                            <small class="form-text text-muted">Auto-calculated based on period type, but can be customized</small>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                            <small class="form-text text-muted">Auto-calculated based on period type, but can be customized</small>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="open">Open</option>
                            <option value="closed" selected>Closed</option> 
                        </select>
                        <div class="form-text">Set the initial status for this period. Defaults to Closed.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="savePeriod">
                    <i class="fas fa-save me-1"></i> Save Period
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize APP_URL for JavaScript
const APP_URL = '<?php echo APP_URL; ?>';
</script>
