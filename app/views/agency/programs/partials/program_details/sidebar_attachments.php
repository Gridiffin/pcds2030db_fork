<?php
/**
 * Sidebar Attachments
 * 
 * Displays program attachments with download links.
 */
?>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="fas fa-paperclip me-2"></i>Attachments
        </h6>
        <span class="badge bg-secondary">
            <?php echo count($program_attachments); ?>
        </span>
    </div>
    <div class="card-body">
        <?php if (!empty($program_attachments)): ?>
            <div class="attachments-list">
                <?php foreach ($program_attachments as $attachment): ?>
                    <div class="attachment-item mb-3">
                        <div class="attachment-info d-flex align-items-center">
                            <div class="attachment-icon me-2">
                                <i class="fas <?php echo get_file_icon(isset($attachment['mime_type']) ? $attachment['mime_type'] : ''); ?> text-primary"></i>
                            </div>
                            <div class="attachment-details flex-grow-1">
                                <div class="attachment-name small fw-medium"><?php echo htmlspecialchars($attachment['original_filename']); ?></div>
                                <div class="attachment-meta text-muted small">
                                    <?php echo $attachment['file_size_formatted']; ?> • 
                                    <?php echo date('M j, Y', strtotime($attachment['upload_date'])); ?>
                                </div>
                            </div>
                            <div class="attachment-actions">
                                <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   target="_blank"
                                   title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-3">
                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                <p class="text-muted small mb-0">No attachments</p>
            </div>
        <?php endif; ?>
    </div>
</div>
