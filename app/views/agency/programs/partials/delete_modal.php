<!-- Enhanced Delete Confirmation Modal (Double Confirmation) -->
<div class="modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteStep1">
                    <div style="background: #fff3cd; color: #856404; border-radius: 6px; padding: 1rem; margin-bottom: 1rem; border: 1px solid #ffeeba;">
                        <strong style="font-size: 1.1em; display: flex; align-items: center;"><span style="font-size: 1.3em; margin-right: 0.5em;">⚠️</span> Warning: This action cannot be undone</strong>
                        <p class="mb-0" style="margin-top: 0.5em;">
                            You are about to delete the program: 
                            <strong id="program-name-display">Program Name</strong>
                        </p>
                        <hr style="border-color: #ffeeba; margin: 0.5em 0;">
                        <p class="mb-0 small">
                            This will permanently remove:
                        </p>
                        <ul class="small mb-0">
                            <li>All program submissions and progress data</li>
                            <li>All associated targets and achievements</li>
                            <li>All file attachments and documents</li>
                            <li>All historical audit records</li>
                        </ul>
                    </div>
                    <p>Are you sure you want to continue?</p>
                </div>
                <div id="deleteStep2" style="display:none;">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">🚨 Final Confirmation Required</h6>
                        <p class="mb-0">
                            This is your final chance to prevent permanent data loss.
                        </p>
                    </div>
                    <p class="fw-bold text-danger">
                        Click "Delete Permanently" to confirm the irreversible deletion of this program and all its data.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="delete-continue-btn">Continue</button>
                <button type="button" class="btn btn-danger" id="delete-confirm-btn" style="display:none;">
                    Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for deletion -->
<form action="<?php echo APP_URL; ?>/app/views/agency/programs/delete_program.php" method="post" id="delete-program-form" style="display:none;">
    <input type="hidden" name="program_id" id="program-id-input">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
</form>
