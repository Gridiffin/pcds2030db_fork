<?php
/**
 * Partial for Recent Submissions
 * 
 * @var array $recent_submissions List of recent submissions.
 */
?>
<div class="col-lg-6 mb-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title m-0">Recent Submissions</h5>
        </div>
        <div class="card-body" data-period-content="submissions_section">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Agency</th>
                            <th>Program</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_submissions)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-3">No recent submissions for this period</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_submissions as $submission): ?>
                                <tr>
                                    <td><?php echo $submission['agency_name']; ?></td>
                                    <td><?php echo $submission['program_name']; ?></td>
                                    <td>
                                        <?php 
                                            $date = $submission['submission_date'] ?? '';
                                            if (!empty($date)) {
                                                echo date('M j, g:i a', strtotime($date));
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 