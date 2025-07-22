<?php
/**
 * Timeline Section Partial
 * Contains the program timeline input fields
 */
?>

<!-- Timeline Section -->
<div class="card timeline-card">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-calendar-alt me-2"></i>
            Timeline
        </h6>
    </div>
    <div class="card-body">
        <!-- Start Date -->
        <div class="timeline-date-group">
            <label for="start_date" class="form-label">
                Start Date
            </label>
            <input type="date" 
                   class="form-control" 
                   id="start_date" 
                   name="start_date"
                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
            <div class="timeline-date-help">
                <i class="fas fa-info-circle me-1"></i>
                Please enter a full date in <strong>YYYY-MM-DD</strong> format. Partial dates (year or year-month) are not accepted.
            </div>
        </div>

        <!-- End Date -->
        <div class="timeline-date-group">
            <label for="end_date" class="form-label">
                End Date
            </label>
            <input type="date" 
                   class="form-control" 
                   id="end_date" 
                   name="end_date"
                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
            <div class="timeline-date-help">
                <i class="fas fa-info-circle me-1"></i>
                Please enter a full date in <strong>YYYY-MM-DD</strong> format. Partial dates (year or year-month) are not accepted.
            </div>
        </div>

        <!-- Date Range Error -->
        <div class="date-range-error">
            <i class="fas fa-exclamation-circle me-1"></i>
            <span class="error-message"></span>
        </div>
    </div>
</div> 