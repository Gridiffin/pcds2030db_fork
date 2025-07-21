<?php
/**
 * Program Attachments Section
 * Displays attachments related to the program
 */

// Get program attachments
$attachments = get_program_attachments($program_id);
?>

<!-- Program Attachments (if any) -->
<?php if (!empty($attachments)): ?>
<div class="card submission-card attachments-section mb-4">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-white">
                <i class="fas fa-paperclip me-2"></i>
                Program Attachments (<?php echo count($attachments); ?>)
            </h5>
            <?php if ($can_edit): ?>
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-sm btn-outline-light">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($attachments as $attachment): ?>
                <div class="col-md-6 mb-3">
                    <div class="attachment-item">
                        <div class="d-flex align-items-center">
                            <?php
                            // Determine file icon based on extension
                            $file_extension = strtolower(pathinfo($attachment['original_filename'] ?? '', PATHINFO_EXTENSION));
                            $icon_class = 'fas fa-file';
                            
                            switch ($file_extension) {
                                case 'pdf':
                                    $icon_class = 'fas fa-file-pdf text-danger';
                                    break;
                                case 'doc':
                                case 'docx':
                                    $icon_class = 'fas fa-file-word text-primary';
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $icon_class = 'fas fa-file-excel text-success';
                                    break;
                                case 'ppt':
                                case 'pptx':
                                    $icon_class = 'fas fa-file-powerpoint text-warning';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                    $icon_class = 'fas fa-file-image text-info';
                                    break;
                                case 'zip':
                                case 'rar':
                                case '7z':
                                    $icon_class = 'fas fa-file-archive text-secondary';
                                    break;
                                default:
                                    $icon_class = 'fas fa-file text-muted';
                            }
                            ?>
                            
                            <i class="<?php echo $icon_class; ?> attachment-icon"></i>
                            
                            <div class="flex-grow-1">
                                <a href="<?php echo htmlspecialchars($attachment['file_path'] ?? '#'); ?>" 
                                   class="attachment-name d-block"
                                   data-action="download-attachment"
                                   data-attachment-id="<?php echo $attachment['attachment_id'] ?? ''; ?>"
                                   data-file-name="<?php echo htmlspecialchars($attachment['original_filename'] ?? 'Unknown'); ?>"
                                   target="_blank">
                                    <?php echo htmlspecialchars($attachment['original_filename'] ?? 'Unknown File'); ?>
                                </a>
                                
                                <div class="attachment-meta">
                                    <?php if (!empty($attachment['file_size'])): ?>
                                        <span data-file-size="<?php echo $attachment['file_size']; ?>">
                                            <?php echo number_format($attachment['file_size'] / 1024, 1); ?> KB
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($attachment['uploaded_at'])): ?>
                                        • Uploaded <?php echo date('M j, Y', strtotime($attachment['uploaded_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="attachment-actions">
                                <a href="<?php echo htmlspecialchars($attachment['file_path'] ?? '#'); ?>" 
                                   class="btn btn-sm btn-outline-primary"
                                   data-action="download-attachment"
                                   data-attachment-id="<?php echo $attachment['attachment_id'] ?? ''; ?>"
                                   target="_blank"
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                
                                <?php if (in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary"
                                            data-action="preview-attachment"
                                            data-file-name="<?php echo htmlspecialchars($attachment['original_filename'] ?? ''); ?>"
                                            data-file-url="<?php echo htmlspecialchars($attachment['file_path'] ?? ''); ?>"
                                            title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($can_edit): ?>
            <div class="mt-3 pt-3 border-top text-center">
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Add More Attachments
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- No Attachments State -->
<div class="card submission-card attachments-section mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0 text-white">
            <i class="fas fa-paperclip me-2"></i>
            Program Attachments
        </h5>
    </div>
    <div class="card-body no-attachments-state">
        <div class="icon">
            <i class="fas fa-paperclip fa-3x text-muted"></i>
        </div>
        <h6 class="text-muted">No Attachments</h6>
        <p class="text-muted mb-0">This program doesn't have any attachments yet.</p>
        <?php if ($can_edit): ?>
            <div class="mt-3">
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Add Attachments
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
