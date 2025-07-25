<!-- Main Content for Edit Submission Page -->
<main>
<div class="container-fluid">
    <!-- Remove .row and column wrappers for single-column layout -->
    <!-- Main content and history sidebar will be rendered inside the main card/form -->
    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Wait for global functions to be available
                function waitForToastFunctions() {
                    if (typeof window.showToast === 'function') {
                        showToast('<?= ucfirst($_SESSION['message_type']) ?>', <?= json_encode($_SESSION['message']) ?>, '<?= $_SESSION['message_type'] ?>');
                    } else {
                        setTimeout(waitForToastFunctions, 100);
                    }
                }
                waitForToastFunctions();
            });
        </script>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <!-- Program Info Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Program Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?><br>
                    <strong>Program Number:</strong> <?php echo htmlspecialchars($program['program_number'] ?? 'Not assigned'); ?><br>
                    <strong>Initiative:</strong> 
                    <?php if (!empty($program['initiative_name'])): ?>
                        <?php echo htmlspecialchars($program['initiative_name']); ?>
                        <?php if (!empty($program['initiative_number'])): ?>
                            (<?php echo htmlspecialchars($program['initiative_number']); ?>)
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">Not linked</span>
                    <?php endif; ?><br>
                    <strong>Agency:</strong> <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Description:</strong> <?php echo htmlspecialchars($program['program_description'] ?? 'No description'); ?><br>
                    <strong>Created:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?><br>
                    <strong>Existing Submissions:</strong> <?php echo count($existing_submissions); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Selector Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-alt me-2"></i>
                Select Reporting Period
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="period_selector" class="form-label">
                        Reporting Period <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="period_selector" required>
                        <option value="">Choose a reporting period...</option>
                        <?php foreach ($reporting_periods as $period): ?>
                            <?php 
                            $has_submission = isset($submissions_by_period[$period['period_id']]);
                            $submission = $has_submission ? $submissions_by_period[$period['period_id']] : null;
                            ?>
                            <option value="<?php echo $period['period_id']; ?>"
                                    data-has-submission="<?php echo $has_submission ? 'true' : 'false'; ?>"
                                    data-submission-id="<?php echo $has_submission ? $submission['submission_id'] : ''; ?>"
                                    data-status="<?php echo $period['status']; ?>">
                                <?php echo htmlspecialchars($period['display_name']); ?>
                                <?php if ($period['status'] == 'open'): ?>
                                    (Open)
                                <?php endif; ?>
                                <?php if ($has_submission): ?>
                                    - <?php echo $submission['is_draft'] ? 'Draft' : 'Finalized'; ?>
                                <?php else: ?>
                                    - No Submission
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-calendar me-1"></i>
                        Select a reporting period to edit existing submission or add a new one.
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="period-status-display">
                        <!-- Period status will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Content Area -->
    <div id="dynamic-content">
        <!-- Content will be loaded here based on period selection -->
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted">Select a Reporting Period</h5>
            <p class="text-muted">Choose a reporting period from the dropdown above to view or edit submissions.</p>
        </div>
    </div>
</div>

<!-- Loading Spinner Template -->
<template id="loading-template">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading submission data...</p>
    </div>
</template>

<!-- No Submission Template -->
<template id="no-submission-template">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus-circle me-2"></i>
                Add New Submission
            </h5>
        </div>
        <div class="card-body">
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                </div>
                <h6 class="text-muted">No Submission Found</h6>
                <p class="text-muted mb-3">There is no submission for this reporting period. You can create a new one.</p>
                <button type="button" id="add-new-submission-btn" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Submission
                </button>
            </div>
        </div>
    </div>
</template>

<script>
// Pass PHP variables to JavaScript
window.programId = <?php echo $program_id; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
window.submissionsByPeriod = <?php echo json_encode($submissions_by_period); ?>;
window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
window.programName = <?= json_encode($program['program_name']) ?>;
window.programNumber = <?= json_encode($program['program_number'] ?? '') ?>;
window.initiativeNumber = <?= json_encode($program['initiative_number'] ?? '') ?>;
</script>

</div>
</main> 