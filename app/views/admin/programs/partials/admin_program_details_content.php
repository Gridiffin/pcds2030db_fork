<?php
/**
 * Admin Program Details Content
 * Main content for admin program details page
 */
?>

<main>
    <div class="container-fluid">
        <!-- Alert for no submissions -->
        <?php if (!$has_submissions): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="alert-icon me-3">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h5 class="alert-heading">No Submissions Found</h5>
                    <p class="mb-0">This program doesn't have any finalized submissions yet. Only finalized submissions are visible in the admin view.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Program Information Card -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">
                            <i class="fas fa-clipboard-list me-2"></i>Program Information
                        </h5>
                        <?php 
                        // Include rating helpers for status mapping
                        require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
                        $current_rating = $rating;
                        $rating_map = [
                            'not_started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start'],
                            'on_track_for_year' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
                            'monthly_target_achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
                            'severe_delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle']
                        ];
                        if (!isset($rating_map[$current_rating])) {
                            $current_rating = 'not_started';
                        }
                        ?>
                        <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> py-2 px-3">
                            <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                            <?php echo $rating_map[$current_rating]['label']; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Program Name</label>
                                <div class="fw-medium">
                                    <?php if (!empty($program['program_number'])): ?>
                                        <span class="badge bg-info me-2"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Agency</label>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($agency_info['agency_name']); ?>
                                    <?php if (!empty($agency_info['agency_acronym'])): ?>
                                        <span class="text-muted">(<?php echo htmlspecialchars($agency_info['agency_acronym']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted">Description</label>
                                <div>
                                    <?php if (!empty($program['description'])): ?>
                                        <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Created Date</label>
                                <div>
                                    <?php if (!empty($program['created_at'])): ?>
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($program['created_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Access & Submission Info Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-users me-2"></i>Program Access & Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Program Assignees -->
                        <?php if (!empty($program_assignees)): ?>
                            <div class="mb-4">
                                <label class="form-label text-muted small">Program Editors & Viewers</label>
                                <div class="assignees-list">
                                    <?php foreach ($program_assignees as $assignee): ?>
                                        <div class="assignee-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <div class="fw-medium">
                                                    <i class="fas fa-user me-1 text-success"></i>
                                                    <?php echo htmlspecialchars($assignee['name']); ?>
                                                    <?php if (!empty($assignee['agency_name'])): ?>
                                                        <small class="text-muted d-block"><?php echo htmlspecialchars($assignee['agency_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <?php 
                                                $role_classes = [
                                                    'owner' => 'bg-danger',
                                                    'editor' => 'bg-warning',
                                                    'viewer' => 'bg-info'
                                                ];
                                                $role_class = $role_classes[$assignee['role']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $role_class; ?> small">
                                                    <?php echo ucfirst($assignee['role']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <label class="form-label text-muted small">Program Access</label>
                                <div class="text-center py-3">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <div class="text-muted small">
                                        No specific user assignments.<br>
                                        Default agency access applies.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Submission Information -->
                        <?php if ($submission_info): ?>
                            <div class="finalization-details">
                                <label class="form-label text-muted small">Latest Submission</label>
                                <div class="mb-3">
                                    <div class="fw-medium">
                                        <i class="fas fa-user me-2 text-success"></i>
                                        <?php echo htmlspecialchars($submission_info['submitted_by_name']); ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($submission_info['submitted_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Finalized Submission
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-question-circle fa-2x text-muted mb-2"></i>
                                <div class="text-muted">
                                    No finalization information available
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Initiative Information -->
        <?php if (!empty($program['initiative_id'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">
                            <i class="fas fa-lightbulb me-2"></i>Initiative Information
                        </h5>
                        <a href="<?php echo APP_URL; ?>/app/views/admin/initiatives/view_initiative.php?id=<?php echo $program['initiative_id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>View Initiative Details
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="initiative-header mb-3">
                                    <?php if (!empty($program['initiative_number'])): ?>
                                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                                    <?php endif; ?>
                                    <span class="fw-bold text-primary fs-5"><?php echo htmlspecialchars($program['initiative_name']); ?></span>
                                </div>
                                
                                <?php if (!empty($program['initiative_description'])): ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Description</label>
                                    <div><?php echo nl2br(htmlspecialchars($program['initiative_description'])); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <div>
                                    <label class="form-label text-muted">Timeline</label>
                                    <div>
                                        <?php if (!empty($program['initiative_start_date']) || !empty($program['initiative_end_date'])): ?>
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <?php 
                                            if (!empty($program['initiative_start_date']) && !empty($program['initiative_end_date'])) {
                                                echo date('M j, Y', strtotime($program['initiative_start_date'])) . ' - ' . date('M j, Y', strtotime($program['initiative_end_date']));
                                            } elseif (!empty($program['initiative_start_date'])) {
                                                echo 'Started: ' . date('M j, Y', strtotime($program['initiative_start_date']));
                                            } elseif (!empty($program['initiative_end_date'])) {
                                                echo 'Due: ' . date('M j, Y', strtotime($program['initiative_end_date']));
                                            }
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">No timeline specified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <label class="form-label text-muted">Related Programs</label>
                                <div class="badge bg-secondary"><?php echo count($related_programs); ?> programs</div>
                                
                                <?php if (!empty($related_programs) && count($related_programs) <= 5): ?>
                                    <div class="mt-2">
                                        <?php foreach (array_slice($related_programs, 0, 5) as $related): ?>
                                            <div class="small mb-1">
                                                <?php if ($related['is_draft_only']): ?>
                                                    <a href="#" class="text-decoration-none text-muted draft-program-link" 
                                                       data-program-name="<?php echo htmlspecialchars($related['program_name']); ?>"
                                                       data-bs-toggle="tooltip" 
                                                       title="This program is still in draft status">
                                                        <i class="fas fa-lock me-1"></i>
                                                        <?php echo htmlspecialchars($related['program_name']); ?>
                                                        <span class="badge bg-warning ms-1 small">Draft</span>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="program_details.php?id=<?php echo $related['program_id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($related['program_name']); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <div class="text-muted small"><?php echo htmlspecialchars($related['agency_name']); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Submission History -->
        <?php if (!empty($submission_history)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-history me-2"></i>Submission History
                            <span class="badge bg-secondary ms-2"><?php echo count($submission_history); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th>Submitted Date</th>
                                        <th>Submitted By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submission_history as $submission): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-info"><?php echo $submission['period_display']; ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($submission['submitted_at'])): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($submission['submitted_by_name'])): ?>
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($submission['submitted_by_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Program Attachments -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">
                            <i class="fas fa-paperclip me-2"></i>Program Attachments
                        </h5>
                        <span class="badge bg-secondary"><?php echo count($attachments); ?> files</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($attachments)): ?>
                            <div class="attachments-list">
                                <?php foreach ($attachments as $attachment): ?>
                                    <div class="attachment-item d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                                        <div class="attachment-info d-flex align-items-center">
                                            <div class="attachment-icon me-3">
                                                <i class="fas fa-file fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($attachment['file_name'] ?? 'Unknown file'); ?></h6>
                                                <div class="text-muted small">
                                                    <span class="me-3"><?php echo $attachment['file_size_formatted']; ?></span>
                                                    <?php if (!empty($attachment['uploaded_at'])): ?>
                                                        <span><?php echo date('M j, Y', strtotime($attachment['uploaded_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No Attachments</h6>
                                <p class="text-muted mb-0">This program doesn't have any supporting documents.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Draft Program Modal -->
<div class="modal fade" id="draftProgramModal" tabindex="-1" aria-labelledby="draftProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="draftProgramModalLabel">
                    <i class="fas fa-lock me-2 text-warning"></i>Program Not Available
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-file-alt fa-3x text-warning mb-3"></i>
                    <h6 class="mb-3">Program Still in Draft</h6>
                    <p class="text-muted mb-0">
                        The program "<span id="draftProgramName" class="fw-bold"></span>" cannot be viewed because it is still in draft status. 
                        Only finalized submissions are visible in the admin view.
                    </p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle draft program link clicks
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle draft program clicks
    document.querySelectorAll('.draft-program-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var programName = this.getAttribute('data-program-name');
            document.getElementById('draftProgramName').textContent = programName;
            var modal = new bootstrap.Modal(document.getElementById('draftProgramModal'));
            modal.show();
        });
    });
});
</script>