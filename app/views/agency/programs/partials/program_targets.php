<?php
/**
 * Program Targets Partial
 * Displays program targets and achievements
 */

// Enhanced target detection - check multiple sources for targets
$has_targets = false;
if (!empty($targets)) {
    $has_targets = true;
} elseif ($has_submissions) {
    // Check if there are any targets in the submission data
    if (isset($latest_submission['targets']) && is_array($latest_submission['targets']) && !empty($latest_submission['targets'])) {
        $has_targets = true;
    } elseif (isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        $content_check = is_string($latest_submission['content_json']) ? 
            json_decode($latest_submission['content_json'], true) : 
            $latest_submission['content_json'];
        
        if (isset($content_check['targets']) && is_array($content_check['targets']) && !empty($content_check['targets'])) {
            $has_targets = true;
        } elseif (isset($content_check['target']) && !empty($content_check['target'])) {
            $has_targets = true;
        }
    } elseif (!empty($latest_submission['target'])) {
        $has_targets = true;
    }
}
?>

<!-- Targets and Achievements Section -->
<div class="card performance-card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-bullseye me-2"></i>Targets & Achievements
        </h5>
        <?php if ($has_submissions): ?>
            <span class="badge rating-<?php echo str_replace([' ', '-'], '_', strtolower($rating)); ?> rating-badge">
                <?php echo ucwords(str_replace(['_', '-'], ' ', $rating)); ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($has_targets): ?>
            <div class="targets-table">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>Target</th>
                            <th>Achievement/Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($targets as $target): ?>
                            <tr>
                                <td class="target-cell">
                                    <div class="cell-content">
                                        <?php echo nl2br(htmlspecialchars($target['text'] ?? $target['target_text'] ?? 'No target specified')); ?>
                                        
                                        <?php if (!empty($target['target_number'])): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary">Target #<?php echo htmlspecialchars($target['target_number']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($target['start_date']) || !empty($target['end_date'])): ?>
                                            <div class="mt-2 text-muted small">
                                                <?php if (!empty($target['start_date'])): ?>
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?php echo date('M j, Y', strtotime($target['start_date'])); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($target['end_date'])): ?>
                                                    <?php if (!empty($target['start_date'])): ?>
                                                        <i class="fas fa-long-arrow-alt-right mx-1"></i>
                                                    <?php endif; ?>
                                                    <?php echo date('M j, Y', strtotime($target['end_date'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="achievement-cell">
                                    <div class="cell-content">
                                        <?php if (!empty($target['status_description']) || !empty($target['achievement'])): ?>
                                            <div class="achievement-description">
                                                <?php echo nl2br(htmlspecialchars($target['status_description'] ?? $target['achievement'] ?? '')); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="empty-value">
                                                <i class="fas fa-info-circle me-1"></i>
                                                No achievement details provided
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($target['status'])): ?>
                                            <div class="mt-2">
                                                <span class="status-pill rating-<?php echo str_replace([' ', '-'], '_', strtolower($target['status'])); ?>">
                                                    <i class="fas fa-flag me-1"></i>
                                                    <?php echo ucwords(str_replace(['_', '-'], ' ', $target['status'])); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Overall Achievement Section -->
            <?php if (!empty($remarks)): ?>
                <div class="overall-achievement mt-4 p-3">
                    <div class="overall-achievement-label">
                        <i class="fas fa-chart-line me-2"></i>Overall Achievement Summary
                    </div>
                    <div class="achievement-description">
                        <?php echo nl2br(htmlspecialchars($remarks)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php elseif ($has_submissions): ?>
            <!-- No targets but has submissions -->
            <div class="text-center text-muted p-4">
                <i class="fas fa-bullseye fa-2x mb-3 opacity-50"></i>
                <h6>No Targets Defined</h6>
                <p class="mb-0">This program has submissions but no specific targets have been defined yet.</p>
                <?php if ($can_edit): ?>
                    <div class="mt-3">
                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Targets
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- No submissions at all -->
            <div class="text-center text-muted p-4">
                <i class="fas fa-clipboard-list fa-2x mb-3 opacity-50"></i>
                <h6>No Submissions Yet</h6>
                <p class="mb-0">This program doesn't have any progress reports submitted yet.</p>
                <?php if ($can_edit): ?>
                    <div class="mt-3">
                        <a href="add_submission.php?program_id=<?php echo $program['program_id']; ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Add First Submission
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>