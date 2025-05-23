<?php
/**
 * Period Selector Component
 * 
 * This component shows a dropdown to select different reporting periods
 * for reports and program views.
 */

// Get all periods for the selector
$periods_query = "SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC";
$periods_result = $conn->query($periods_query);

$periods = [];
if ($periods_result) {
    while ($row = $periods_result->fetch_assoc()) {
        $periods[] = $row;
    }
}

// Determine the selected period
$selected_period = $viewing_period ?? $current_period ?? null;
$selected_period_id = $selected_period ? $selected_period['period_id'] : null;

// Include functions to get display name
require_once __DIR__ . '/functions.php';

// Determine if this is the current active period
$is_current_active = $selected_period && $selected_period['status'] === 'open';
?>

<div class="card shadow-sm mb-4 period-selector-card">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="period-indicator <?php echo $is_current_active ? 'active' : 'inactive'; ?> me-3">
                        <i class="fas fa-<?php echo $is_current_active ? 'calendar-check' : 'calendar'; ?> fa-lg"></i>
                    </div>
                    <div class="period-selector-info">
                        <h5 class="mb-0 d-flex align-items-center">
                            <?php if ($selected_period): ?>
                                <?php echo get_period_display_name($selected_period); ?> 
                                <span class="badge ms-2 <?php echo $is_current_active ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $is_current_active ? 'Active Period' : 'Closed'; ?>
                                </span>
                            <?php else: ?>
                                Select Reporting Period
                            <?php endif; ?>
                        </h5>
                        <?php if ($selected_period): ?>
                            <p class="text-muted mb-0 small">
                                <?php echo date('M j, Y', strtotime($selected_period['start_date'])); ?> - 
                                <?php echo date('M j, Y', strtotime($selected_period['end_date'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                    <label for="periodSelector" class="me-2 mb-0">Viewing Period:</label>
                    <div class="position-relative">
                        <select class="form-select form-select-sm" id="periodSelector" style="max-width: 200px;">
                            <?php foreach ($periods as $period): ?>
                                <option value="<?php echo $period['period_id']; ?>" 
                                        <?php echo ($selected_period_id == $period['period_id']) ? 'selected' : ''; ?>>
                                    <?php echo get_period_display_name($period); ?>
                                    <?php echo $period['status'] === 'open' ? ' (Open)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="selector-spinner position-absolute" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize the period selector
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelector = document.getElementById('periodSelector');
        if (periodSelector) {
            periodSelector.addEventListener('change', function() {
                const selectedPeriodId = this.value;
                // Get current URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                // Set period_id parameter
                urlParams.set('period_id', selectedPeriodId);
                // Redirect to the same page with the new parameter
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            });
        }
    });
</script>
