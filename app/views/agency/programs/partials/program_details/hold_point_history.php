<?php
/**
 * Hold Point History
 * 
 * Displays read-only history of program hold points.
 */
?>

<div class="card shadow-sm my-4" id="holdPointHistorySection">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-pause-circle me-2"></i>Hold Point History
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($hold_points)): ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Reason</th>
                            <th>Remarks</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hold_points as $hp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hp['reason'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($hp['remarks'] ?? ''); ?></td>
                                <td><span class="text-nowrap"><?php echo date('M j, Y H:i', strtotime($hp['created_at'])); ?></span></td>
                                <td>
                                    <?php if ($hp['ended_at']): ?>
                                        <span class="text-nowrap"><?php echo date('M j, Y H:i', strtotime($hp['ended_at'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$hp['ended_at']): ?>
                                        <span class="badge bg-warning text-dark">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ended</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted p-4">
                <i class="fas fa-folder-open fa-2x mb-2"></i>
                <div>No hold points found for this program.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
