<?php
/**
 * Admin Edit Program Content
 * Main content for admin edit program page
 */
?>

<main>
    <!-- Save Actions Hero -->
    <div class="hero-section bg-light border-bottom mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-1 text-primary">
                        <i class="fas fa-edit me-2"></i>Editing Program
                    </h6>
                    <p class="mb-0 text-muted">
                        <?php if (!empty($program['program_number'])): ?>
                            <?php echo htmlspecialchars($program['program_number']); ?> - 
                        <?php endif; ?>
                        <?php echo htmlspecialchars($program['program_name']); ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-2"></i>Delete Program
                    </button>
                    <button type="submit" form="editProgramForm" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>Update Program
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Edit Form -->
        <form id="editProgramForm" method="POST" action="<?php echo APP_URL; ?>/app/handlers/admin/save_program.php" enctype="multipart/form-data">
            <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">

            <div class="row">
                <!-- Main Form Column -->
                <div class="col-lg-8">
                    <!-- Program Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-info-circle me-2"></i>Program Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="program_name" class="form-label">
                                        Program Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="program_name" name="program_name" 
                                           value="<?php echo htmlspecialchars($program['program_name']); ?>" required>
                                    <div class="form-text">Enter the full name of the program</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="program_number" class="form-label">Program Number</label>
                                    <input type="text" class="form-control" id="program_number" name="program_number" 
                                           value="<?php echo htmlspecialchars($program['program_number']); ?>"
                                           placeholder="e.g., P001, PROG-A">
                                    <div class="form-text">Optional program number for identification</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="Enter program description..."><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                                <div class="form-text">Detailed description of the program's objectives and scope</div>
                            </div>
                        </div>
                    </div>

                    <!-- Agency & Initiative Assignment -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-building me-2"></i>Assignment & Linkage
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="agency_id" class="form-label">
                                        Agency <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="agency_id" name="agency_id" required>
                                        <option value="">Select agency...</option>
                                        <?php foreach ($agencies as $agency): ?>
                                            <option value="<?php echo $agency['agency_id']; ?>" 
                                                    <?php echo ($agency['agency_id'] == $program['agency_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($agency['agency_name']); ?>
                                                <?php if (!empty($agency['agency_acronym'])): ?>
                                                    (<?php echo htmlspecialchars($agency['agency_acronym']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                        Admin can reassign programs to different agencies
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="initiative_id" class="form-label">Initiative</label>
                                    <select class="form-select" id="initiative_id" name="initiative_id">
                                        <option value="">No initiative linkage</option>
                                        <?php foreach ($initiatives as $initiative): ?>
                                            <option value="<?php echo $initiative['initiative_id']; ?>" 
                                                    <?php echo ($initiative['initiative_id'] == $program['initiative_id']) ? 'selected' : ''; ?>>
                                                <?php if (!empty($initiative['initiative_number'])): ?>
                                                    <?php echo htmlspecialchars($initiative['initiative_number']); ?> - 
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Link this program to a strategic initiative</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                
                                <div class="col-md-6 mb-3">
                                    <label for="rating" class="form-label">
                                        Program Rating <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="rating" name="rating" required>
                                        <option value="">Select rating...</option>
                                        <option value="not_started" <?php echo ($program['rating'] == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                                        <option value="on_track_for_year" <?php echo ($program['rating'] == 'on_track_for_year') ? 'selected' : ''; ?>>On Track for Year</option>
                                        <option value="monthly_target_achieved" <?php echo ($program['rating'] == 'monthly_target_achieved') ? 'selected' : ''; ?>>Monthly Target Achieved</option>
                                        <option value="severe_delay" <?php echo ($program['rating'] == 'severe_delay') ? 'selected' : ''; ?>>Severe Delays</option>
                                    </select>
                                    <div class="form-text">Overall program performance rating</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline & Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-calendar-alt me-2"></i>Timeline & Additional Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?php echo $program['start_date']; ?>">
                                    <div class="form-text">Program start date (optional)</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?php echo $program['end_date']; ?>">
                                    <div class="form-text">Program end date (optional)</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Administrative Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                          placeholder="Enter any administrative notes or remarks..."><?php echo htmlspecialchars($program['remarks'] ?? ''); ?></textarea>
                                <div class="form-text">Internal notes for administrative purposes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">

                    <!-- Program Status -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="card-title m-0">
                                <i class="fas fa-info-circle me-2"></i>Program Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0 small">
                                <dt class="col-5">Current Agency:</dt>
                                <dd class="col-7"><?php echo htmlspecialchars($agency_info['agency_name']); ?></dd>
                                
                                <dt class="col-5">Created:</dt>
                                <dd class="col-7">
                                    <?php if (!empty($program['created_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-5">Last Updated:</dt>
                                <dd class="col-7">
                                    <?php if (!empty($program['updated_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($program['updated_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-5">Current Rating:</dt>
                                <dd class="col-7">
                                    <?php
                                    // Rating mapping consistent with admin program details
                                    $rating_map = [
                                        'not_started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start'],
                                        'on_track_for_year' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
                                        'monthly_target_achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
                                        'severe_delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle']
                                    ];
                                    $current_rating = $program['rating'] ?? 'not_started';
                                    
                                    // Fallback if rating is not in map
                                    if (!isset($rating_map[$current_rating])) {
                                        $current_rating = 'not_started';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?>">
                                        <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="card-title m-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="view_submissions.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file-alt me-2"></i>View Submissions
                                </a>
                                
                                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-edit me-2"></i>Edit Submission
                                </a>
                                
                                <a href="../reports/generate_reports.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-chart-bar me-2"></i>Generate Reports
                                </a>
                                
                                <hr class="my-2">
                                
                                <?php 
                                // Check if user can delete: admin, focal user, or program creator
                                $can_delete = is_admin();
                                if (!$can_delete) {
                                    // Include program permissions for non-admin users
                                    require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
                                    $can_delete = is_focal_user() || is_program_creator($program_id);
                                }
                                ?>
                                
                                <?php if ($can_delete): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-program-btn" 
                                        data-id="<?php echo $program_id; ?>" 
                                        data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-2"></i>Delete Program
                                </button>
                                <?php endif; ?>
                                
                                <a href="programs.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list me-2"></i>All Programs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if ($can_delete): ?>
    <!-- Delete Program Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Program
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to delete the program:</p>
                    <p class="fw-bold text-danger" id="program-name-display"></p>
                    <p class="text-muted small">This will permanently remove the program and all associated data including submissions, attachments, and history records.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <form method="POST" action="delete_program.php" style="display: inline;">
                        <input type="hidden" name="program_id" id="program-id-input" value="">
                        <input type="hidden" name="confirm_delete" value="1">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Program
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
// Initialize delete button functionality
document.addEventListener('DOMContentLoaded', function() {
    initDeleteButton();
});

function initDeleteButton() {
    const deleteButton = document.querySelector('.delete-program-btn');
    
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            
            // Set the program details in the modal
            const programNameDisplay = document.getElementById('program-name-display');
            const programIdInput = document.getElementById('program-id-input');
            
            if (programNameDisplay && programIdInput) {
                programNameDisplay.textContent = programName;
                programIdInput.value = programId;
            }
        });
    }
}

// Form validation and submission
document.getElementById('editProgramForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Basic validation
    const programName = document.getElementById('program_name').value.trim();
    const agencyId = document.getElementById('agency_id').value;
    const rating = document.getElementById('rating').value;
    
    if (!programName || !agencyId || !rating) {
        e.preventDefault();
        alert('Please fill in all required fields (Program Name, Agency, and Rating).');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
});

// Date validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('end_date');
    const endDate = new Date(endDateInput.value);
    
    if (this.value && endDateInput.value && startDate >= endDate) {
        alert('Start date must be before end date.');
        this.value = '';
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    const endDate = new Date(this.value);
    const startDateInput = document.getElementById('start_date');
    const startDate = new Date(startDateInput.value);
    
    if (this.value && startDateInput.value && endDate <= startDate) {
        alert('End date must be after start date.');
        this.value = '';
    }
});
</script>