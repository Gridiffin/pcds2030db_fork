<?php
/**
 * Reports Filter Partial
 * Filter form for selecting reporting periods
 */

// Ensure we have access to reporting periods
global $reporting_periods, $selected_period;
?>

<div class="card reports-filter shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title">Select Reporting Period</h5>
    </div>
    <div class="card-body">
        <form method="get" class="filter-form" id="period-filter-form">
            <div class="form-group">
                <label for="period_id" class="form-label">Reporting Period</label>
                <select class="form-select" id="period_id" name="period_id" required>
                    <option value="">-- Select Period --</option>
                    <?php if (!empty($reporting_periods)): ?>
                        <?php foreach ($reporting_periods as $period): ?>
                            <option value="<?php echo $period['period_id']; ?>" 
                                    <?php echo $selected_period == $period['period_id'] ? 'selected' : ''; ?>>
                                <?php echo get_period_display_name($period); ?> 
                                (<?php echo date('M j, Y', strtotime($period['start_date'])); ?> - 
                                <?php echo date('M j, Y', strtotime($period['end_date'])); ?>)
                                <?php echo $period['status'] === 'open' ? ' - OPEN' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary filter-btn" 
                        <?php echo !$selected_period ? 'disabled' : ''; ?>>
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <?php if ($selected_period): ?>
                    <a href="view_reports.php" class="btn btn-outline-secondary clear-filter-btn">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
