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
                                    <?php echo $is_current_active ? 'Currently Editing a program in a ACTIVE period' : 'Currently editing a program in a CLOSED period.'; ?>
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
                    <label for="periodSelector" class="me-2 mb-0">Editing Period:</label>
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
                // Update the hidden period_id input in the form if it exists
                const periodInput = document.querySelector('form input[name="period_id"]');
                if (periodInput) {
                    periodInput.value = selectedPeriodId;
                }
                // Try to get programId from hidden input, JS global, or URL
                let programId = (typeof PROGRAM_ID !== 'undefined') ? PROGRAM_ID : (window.programId || null);
                if (!programId) {
                    // fallback: try to get from hidden input or URL
                    const hiddenInput = document.querySelector('input[name="program_id"]');
                    if (hiddenInput) {
                        programId = hiddenInput.value;
                    } else {
                        const urlParams = new URLSearchParams(window.location.search);
                        const idFromUrl = urlParams.get('id');
                        if (idFromUrl) {
                            programId = idFromUrl;
                        }
                    }
                }
                if (!programId) {
                    alert('Missing program ID.');
                    return;
                }
                // Show loading spinner
                const spinner = document.querySelector('.selector-spinner');
                if (spinner) spinner.style.display = 'block';
                // AJAX fetch program data for selected period
                fetch(APP_URL + '/app/ajax/get_program_submission.php?program_id=' + programId + '&period_id=' + selectedPeriodId)
                    .then(response => response.json())
                    .then(data => {
                        if (spinner) spinner.style.display = 'none';
                        if (data.success && data.data) {
                            // Dispatch custom event so update_program.php JS can update fields
                            const event = new CustomEvent('ProgramPeriodDataLoaded', { detail: data.data });
                            document.dispatchEvent(event);

                            // --- Update the period label and badge dynamically ---
                            // Find the selected option's text
                            const selectedOption = periodSelector.options[periodSelector.selectedIndex];
                            const periodName = selectedOption.text.replace(/\s*\(Open\)$/, '');
                            const isOpen = selectedOption.text.includes('(Open)');
                            // Update the label and badge
                            const h5 = document.querySelector('.period-selector-info h5');
                            if (h5) {
                                h5.innerHTML =
                                    periodName +
                                    ' <span class="badge ms-2 ' + (isOpen ? 'bg-success' : 'bg-secondary') + '">' +
                                    (isOpen ? 'Currently Editing a program in a ACTIVE period' : 'Currently editing a program in a CLOSED period.') +
                                    '</span>';
                            }
                        } else {
                            alert(data.error || 'Failed to load program data for selected period.');
                        }
                    })
                    .catch(() => {
                        if (spinner) spinner.style.display = 'none';
                        alert('Failed to load program data for selected period.');
                    });
            });
        }
    });
</script>
