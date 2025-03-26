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

?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <?php if ($selected_period): ?>
                        <span class="badge bg-<?php echo $selected_period['status'] === 'open' ? 'success' : 'secondary'; ?> me-2">
                            <?php echo $selected_period['status'] === 'open' ? 'Open' : 'Closed'; ?>
                        </span>
                        Q<?php echo $selected_period['quarter']; ?>-<?php echo $selected_period['year']; ?> 
                        <span class="text-muted">
                            (<?php echo date('M j, Y', strtotime($selected_period['start_date'])); ?> - 
                            <?php echo date('M j, Y', strtotime($selected_period['end_date'])); ?>)
                        </span>
                    <?php else: ?>
                        Select Reporting Period
                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
                    <label for="periodSelector" class="me-2">Viewing Period:</label>
                    <select class="form-select" id="periodSelector" style="max-width: 200px;">
                        <?php foreach ($periods as $period): ?>
                            <option value="<?php echo $period['period_id']; ?>" 
                                    <?php echo ($selected_period_id == $period['period_id']) ? 'selected' : ''; ?>>
                                Q<?php echo $period['quarter']; ?>-<?php echo $period['year']; ?>
                                <?php echo $period['status'] === 'open' ? ' (Open)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                // Get current URL without query parameters
                const baseUrl = window.location.href.split('?')[0];
                // Redirect to the same page with the new period_id
                window.location.href = baseUrl + '?period_id=' + selectedPeriodId;
            });
        }
    });
</script>
