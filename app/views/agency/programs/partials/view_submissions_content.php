<?php
/**
 * View Submissions Content Partial
 * Main content for the view submissions page
 */
?>
<!-- Main Content -->
<main>
<div class="container-fluid">
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <?php require_once __DIR__ . '/submission_overview.php'; ?>
            <?php require_once __DIR__ . '/submission_targets.php'; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php require_once __DIR__ . '/submission_sidebar.php'; ?>
        </div>
    </div>
</div>
</main>

<?php if (is_focal_user() && isset($submission['is_draft']) && $submission['is_draft']): ?>
<!-- Finalization Confirmation Modal -->
<div class="modal fade" id="finalizationModal" tabindex="-1" aria-labelledby="finalizationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizationModalLabel">
                    <i class="fas fa-check-circle me-2 text-success"></i>
                    Finalize Submission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Once finalized, this submission cannot be edited.
                </div>
                
                <p>Are you sure you want to finalize the submission for:</p>
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-1" id="modalProgramName"><?php echo htmlspecialchars($program['program_name']); ?></h6>
                        <p class="card-text text-muted mb-0">
                            <small><?php echo htmlspecialchars($submission['period_display']); ?></small>
                        </p>
                    </div>
                </div>
                
                <!-- Program Rating Selection -->
                <div class="mt-3">
                    <label for="programRating" class="form-label">
                        <i class="fas fa-star me-2"></i>
                        Update Program Rating
                    </label>
                    <select class="form-select" id="programRating" required>
                        <option value="<?php echo htmlspecialchars($program['rating'] ?? 'not_started'); ?>" selected>
                            <?php 
                            $current_rating = $program['rating'] ?? 'not_started';
                            switch($current_rating) {
                                case 'monthly_target_achieved':
                                    echo 'Monthly Target Achieved';
                                    break;
                                case 'on_track_for_year':
                                    echo 'On Track for Year';
                                    break;
                                case 'severe_delay':
                                    echo 'Severe Delays';
                                    break;
                                case 'not_started':
                                default:
                                    echo 'Not Started';
                                    break;
                            }
                            ?> (Current)
                        </option>
                        <option value="monthly_target_achieved">Monthly Target Achieved</option>
                        <option value="on_track_for_year">On Track for Year</option>
                        <option value="severe_delay">Severe Delays</option>
                        <option value="not_started">Not Started</option>
                    </select>
                    <div class="form-text">
                        Choose the appropriate rating for this program before finalizing the submission.
                    </div>
                </div>
                
                <p class="mt-3 mb-0 text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmFinalizeBtn">
                    <i class="fas fa-check-circle me-1"></i>
                    Finalize Submission
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Finalization confirmation and handling for view submission page
 */
function confirmFinalization(programId, periodId, programName) {
    console.log('confirmFinalization called with:', programId, periodId, programName);
    
    // Store the parameters for later use
    window.finalizationParams = { programId, periodId, programName };
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('finalizationModal'));
    modal.show();
}

// Handle the actual finalization when modal confirm button is clicked
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmFinalizeBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const params = window.finalizationParams;
            if (!params) return;
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('finalizationModal'));
            if (modal) modal.hide();
            
            // Show loading state on finalize buttons (both header and any other buttons)
            const finalizeButtons = document.querySelectorAll('a[onclick*="confirmFinalization"], button[onclick*="confirmFinalization"]');
            finalizeButtons.forEach(btn => {
                if (btn.tagName === 'A') {
                    btn.style.pointerEvents = 'none';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Finalizing...';
                } else {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Finalizing...';
                }
            });
            
            // Get submission ID from the current submission data
            const submissionId = <?php echo isset($submission['submission_id']) ? $submission['submission_id'] : 'null'; ?>;
            
            if (!submissionId) {
                alert('Error: Unable to identify submission for finalization.');
                finalizeButtons.forEach(btn => {
                    if (btn.tagName === 'A') {
                        btn.style.pointerEvents = 'auto';
                        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Finalize Submission';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Finalize Submission';
                    }
                });
                return;
            }
            
            // Get the selected program rating
            const programRating = document.getElementById('programRating').value;
            
            if (!programRating) {
                alert('Please select a program rating before finalizing.');
                finalizeButtons.forEach(btn => {
                    if (btn.tagName === 'A') {
                        btn.style.pointerEvents = 'auto';
                        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Finalize Submission';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Finalize Submission';
                    }
                });
                return;
            }
            
            // Make the finalization request
            const payload = {
                submission_id: submissionId,
                program_id: params.programId,
                period_id: params.periodId,
                program_rating: programRating
            };
            
            console.log('Sending finalization request with payload:', payload);
            
            fetch('../../../../app/ajax/simple_finalize.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid server response: ' + text);
                }
                
                if (data.success) {
                    // Show success message
                    const successModal = document.createElement('div');
                    successModal.innerHTML = `
                        <div class="modal fade" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-body text-center py-4">
                                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3">Submission Finalized Successfully!</h5>
                                        <p class="text-muted">Redirecting to programs page...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(successModal);
                    const tempModal = new bootstrap.Modal(successModal.querySelector('.modal'));
                    tempModal.show();
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'view_programs.php';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to finalize submission');
                }
            })
            .catch(error => {
                console.error('Finalization error:', error);
                alert('Error: ' + error.message);
                
                // Re-enable buttons
                finalizeButtons.forEach(btn => {
                    if (btn.tagName === 'A') {
                        btn.style.pointerEvents = 'auto';
                        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Finalize Submission';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Finalize Submission';
                    }
                });
            });
        });
    }
});
</script>
<?php endif; ?>