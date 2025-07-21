<?php
/**
 * Program Summary Sidebar Card
 * Displays basic program information
 */
?>

<!-- Program Summary Card -->
<div class="card sidebar-card">
    <div class="card-header bg-success text-white">
        <h6 class="card-title text-white">
            <i class="fas fa-info-circle me-2"></i>Program Summary
        </h6>
    </div>
    <div class="card-body program-summary">
        <dl class="row mb-0 small">
            <dt class="col-5">Program:</dt>
            <dd class="col-7"><?php echo htmlspecialchars($program['program_name']); ?></dd>
            
            <dt class="col-5">Number:</dt>
            <dd class="col-7">
                <?php if (!empty($program['program_number'])): ?>
                    <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span>
                <?php else: ?>
                    <span class="text-muted">Not assigned</span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-5">Agency:</dt>
            <dd class="col-7"><?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?></dd>
            
            <dt class="col-5">Initiative:</dt>
            <dd class="col-7">
                <?php if (!empty($program['initiative_name'])): ?>
                    <?php echo htmlspecialchars($program['initiative_name']); ?>
                    <?php if (!empty($program['initiative_number'])): ?>
                        <br><span class="badge bg-secondary mt-1"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">Not linked</span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-5">Rating:</dt>
            <dd class="col-7">
                <span class="badge" style="background-color: <?php echo $rating_info['color']; ?>; color: white;">
                    <?php echo $rating_info['label']; ?>
                </span>
            </dd>
            
            <?php if (!empty($program['created_at'])): ?>
                <dt class="col-5">Created:</dt>
                <dd class="col-7"><?php echo date('M j, Y', strtotime($program['created_at'])); ?></dd>
            <?php endif; ?>
            
            <?php if (!empty($program['updated_at'])): ?>
                <dt class="col-5">Modified:</dt>
                <dd class="col-7"><?php echo date('M j, Y', strtotime($program['updated_at'])); ?></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>
