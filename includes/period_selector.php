<?php
/**
 * Period Selector Component
 * 
 * Provides a dropdown to select reporting periods and view historical data.
 * Usage: include this file where a period selector is needed.
 */

// Get all reporting periods for dropdown
$all_periods = get_all_reporting_periods();

// Get currently selected period (from URL parameter or default to current)
$selected_period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$selected_period = null;

if ($selected_period_id) {
    $selected_period = get_reporting_period($selected_period_id);
} else {
    $selected_period = get_current_reporting_period();
    $selected_period_id = $selected_period['period_id'] ?? null;
}

// Is this a historical view?
$is_historical = $selected_period && isset($selected_period['period_id']) && 
                 $selected_period['period_id'] != ($current_period['period_id'] ?? 0);
?>

<div class="period-selector-container mb-4">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block mb-1">Viewing data for:</small>
                    <span class="h5 mb-0" id="currentPeriodLabel">
                        <?php if ($selected_period): ?>
                            Q<?php echo $selected_period['quarter']; ?>-<?php echo $selected_period['year']; ?>
                            <?php if ($is_historical): ?>
                                <span class="badge bg-info ms-2">Historical</span>
                            <?php endif; ?>
                        <?php else: ?>
                            No period selected
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <label for="periodSelector" class="form-label small text-muted mb-0">Change period:</label>
                        <select class="form-select form-select-sm" id="periodSelector">
                            <?php foreach ($all_periods as $period): ?>
                                <option value="<?php echo $period['period_id']; ?>" 
                                        <?php echo $period['period_id'] == $selected_period_id ? 'selected' : ''; ?>>
                                    Q<?php echo $period['quarter']; ?>-<?php echo $period['year']; ?>
                                    <?php echo $period['status'] === 'open' ? ' (Current)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($is_historical): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['period_id' => $current_period['period_id'] ?? ''])); ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync-alt me-1"></i> Return to Current
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
