<?php
/**
 * Period Selector Component (Editing Version)
 * 
 * This component shows a dropdown to select different reporting periods
 * for editing a program. It uses program_id and fetches program data for the selected period.
 * 
 * Note: Half-yearly periods (quarters 5 and 6) are excluded from this selector
 * to provide a cleaner editing interface focused on quarterly reporting.
 */

// Get all periods for the selector (excluding half-yearly periods for cleaner editing interface)
require_once __DIR__ . '/functions.php';
$periods_query = "SELECT * FROM reporting_periods WHERE period_type = 'quarter' ORDER BY year DESC, period_number DESC";
$periods_result = $conn->query($periods_query);

$periods = [];
if ($periods_result) {
    while ($row = $periods_result->fetch_assoc()) {
        $periods[] = $row;
    }
}

// Determine the selected period
$selected_period = $viewing_period ?? $current_period ?? null;
$selected_period_id = $viewing_period_id ?? ($selected_period ? $selected_period['period_id'] : null);
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
                                    <?php echo $is_current_active ? 'Active Period' : 'Closed Period'; ?>
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
                    <label for="periodSelectorEdit" class="me-2 mb-0">Editing Period:</label>
                    <div class="position-relative">
                        <select class="form-select form-select-sm" id="periodSelectorEdit" style="max-width: 200px;">
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
    // Editing period selector: handles program_id and AJAX fetch
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelector = document.getElementById('periodSelectorEdit');
        if (periodSelector) {
            periodSelector.addEventListener('change', function() {
                const selectedPeriodId = this.value;
                // Update the hidden period_id input in the form if it exists
                const periodInput = document.querySelector('form input[name="period_id"]');
                if (periodInput) {
                    periodInput.value = selectedPeriodId;
                }
                // Update the URL query parameter period_id and reload the page
                const url = new URL(window.location.href);
                url.searchParams.set('period_id', selectedPeriodId);
                window.location.href = url.toString(); // This will reload the page with the new period
            });
        }
    });
</script>
