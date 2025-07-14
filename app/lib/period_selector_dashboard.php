<?php
/**
 * Period Selector Component
 * 
 * This component shows a dropdown to select different reporting periods
 * for reports and program views.
 */

// Get all periods for the selector
$periods_query = "SELECT * FROM reporting_periods ORDER BY year DESC, period_type DESC, period_number DESC";
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

// Check for view mode preference (default to half-yearly for backward compatibility)
$view_mode = $_GET['view_mode'] ?? 'half-yearly';

// Group periods by year and half-year, and combine into single selectable option per half-year
// Half Year 1: Q1 (Jan-Mar) + Q2 (Apr-Jun)
// Half Year 2: Q3 (Jul-Sep) + Q4 (Oct-Dec) -- starts at July
define('HALF_YEAR_LABELS', [1 => 'Half Year 1', 2 => 'Half Year 2']);
define('QUARTER_LABELS', [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'Half Yearly 1', 6 => 'Half Yearly 2']);

$half_year_options = [];
$quarterly_options = [];

foreach ($periods as $period) {
    $year = $period['year'];
    $type = $period['period_type'];
    $num = (int)$period['period_number'];
    // Build half-yearly options (for period_type = 'half')
    if ($type === 'half') {
        $half_key = $year . '-H' . $num;
        if (!isset($half_year_options[$half_key])) {
            $half_year_options[$half_key] = [
                'year' => $year,
                'half' => $num,
                'periods' => [],
                'start_date' => null,
                'end_date' => null,
                'status' => $period['status'],
            ];
        }
        $half_year_options[$half_key]['periods'][] = $period;
    }
    // Build quarterly options (for period_type = 'quarter')
    if ($type === 'quarter') {
        $quarterly_options[] = $period;
    }
}
// For each half, set start_date and end_date using the periods in the half (for period_type = 'half')
foreach ($half_year_options as &$half) {
    $start = null; $end = null;
    foreach ($half['periods'] as $p) {
        if (!$start || strtotime($p['start_date']) < strtotime($start)) {
            $start = $p['start_date'];
        }
        if (!$end || strtotime($p['end_date']) > strtotime($end)) {
            $end = $p['end_date'];
        }
    }
    $half['start_date'] = $start;
    $half['end_date'] = $end;
}
unset($half);

// Sort by year DESC, half DESC (so H2 appears before H1 for each year)
uksort($half_year_options, function($a, $b) {
    [$yearA, $halfA] = explode('-H', $a);
    [$yearB, $halfB] = explode('-H', $b);
    if ($yearA === $yearB) return $halfB <=> $halfA; // H2 before H1
    return $yearB <=> $yearA; // Newer years first
});

// Sort quarterly options by year DESC, period_number DESC
usort($quarterly_options, function($a, $b) {
    if ($a['year'] === $b['year']) {
        return $b['period_number'] <=> $a['period_number'];
    }
    return $b['year'] <=> $a['year'];
});

// Determine selected half-year key
$selected_half_key = null;
foreach ($half_year_options as $key => $half) {
    foreach ($half['periods'] as $p) {
        if ($selected_period_id == $p['period_id']) {
            $selected_half_key = $key;
            break 2;
        }
    }
}
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
                    <!-- View Mode Toggle -->
                    <div class="me-3">
                        <div class="btn-group btn-group-sm" role="group" aria-label="View Mode">
                            <input type="radio" class="btn-check" name="viewMode" id="halfYearlyView" value="half-yearly" <?php echo $view_mode === 'half-yearly' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="halfYearlyView">Half-Yearly</label>
                            
                            <input type="radio" class="btn-check" name="viewMode" id="quarterlyView" value="quarterly" <?php echo $view_mode === 'quarterly' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="quarterlyView">Quarterly</label>
                        </div>
                    </div>
                    
                    <label for="periodSelector" class="me-2 mb-0">Viewing Period:</label>
                    <div class="position-relative">
                        <select class="form-select form-select-sm" id="periodSelector" style="max-width: 250px;">
                            <?php if ($view_mode === 'half-yearly'): ?>
                                <!-- Half-Yearly Options -->
                                <?php foreach ($half_year_options as $key => $half): ?>
                                    <?php 
                                        $display = $half['year'] . ' - ' . HALF_YEAR_LABELS[$half['half']];
                                        if ($half['start_date'] && $half['end_date']) {
                                            $display .= ' (' . date('M j', strtotime($half['start_date'])) . ' - ' . date('M j, Y', strtotime($half['end_date'])) . ')';
                                        }
                                        $is_selected = ($selected_half_key === $key);
                                        // Use a comma-separated list of period_ids for the half-year value
                                        $value = implode(',', array_column($half['periods'], 'period_id'));
                                    ?>
                                    <option value="<?php echo $value; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                                        <?php echo $display; ?><?php echo $half['status'] === 'open' ? ' (Open)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Quarterly Options -->
                                <?php foreach ($quarterly_options as $period): ?>
                                    <?php 
                                        $display = $period['year'] . ' - ' . QUARTER_LABELS[$period['period_number']];
                                        $display .= ' (' . date('M j', strtotime($period['start_date'])) . ' - ' . date('M j, Y', strtotime($period['end_date'])) . ')';
                                        $is_selected = ($selected_period_id == $period['period_id']);
                                    ?>
                                    <option value="<?php echo $period['period_id']; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                                        <?php echo $display; ?><?php echo $period['status'] === 'open' ? ' (Open)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        const viewModeRadios = document.querySelectorAll('input[name="viewMode"]');
        
        // Handle period selection change
        if (periodSelector) {
            periodSelector.addEventListener('change', function() {
                const selectedPeriodId = this.value;
                // Get current URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                // Set period_id parameter (comma-separated for half-year, single for quarterly)
                urlParams.set('period_id', selectedPeriodId);
                // Redirect to the same page with the new parameter
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            });
        }
        
        // Handle view mode change
        viewModeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('view_mode', this.value);
                    // Remove period_id when switching view modes to prevent conflicts
                    urlParams.delete('period_id');
                    window.location.href = window.location.pathname + '?' + urlParams.toString();
                }
            });
        });
    });
</script>
