<?php
/**
 * Reports List Partial
 * Displays the list of reports for the selected period
 */

// Ensure we have access to reports and selected period
global $reports, $selected_period;
?>

<div id="reports-container">
    <?php if ($selected_period): ?>
        <?php if (empty($reports)): ?>
            <div class="alert alert-info reports-alert">
                <i class="fas fa-info-circle me-2"></i>
                No reports found for the selected reporting period.
            </div>
        <?php else: ?>
            <div class="card reports-list shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title">Available Reports</h5>
                    <span class="reports-count-badge"><?php echo count($reports); ?> Reports</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table reports-table">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <?php
                                    $downloadUrl = APP_URL . '/reports/' . ($report['file_path'] ?? '');
                                    $isRecent = (strtotime($report['generated_at']) > strtotime('-7 days'));
                                    $badgeClass = match($report['report_type'] ?? 'general') {
                                        'program' => 'bg-primary',
                                        'sector' => 'bg-info',
                                        'public' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <tr <?php echo $isRecent ? 'class="table-warning"' : ''; ?>>
                                        <td>
                                            <div class="report-name">
                                                <?php echo htmlspecialchars($report['report_name'] ?? 'Untitled Report'); ?>
                                            </div>
                                            <?php if ($isRecent): ?>
                                                <small class="text-warning">
                                                    <i class="fas fa-star"></i> New
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $description = $report['description'] ?? 'No description available';
                                            echo htmlspecialchars(strlen($description) > 80 ? substr($description, 0, 77) . '...' : $description);
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $badgeClass; ?> report-type-badge">
                                                <?php echo ucfirst($report['report_type'] ?? 'General'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($report['generated_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="reports-actions">
                                                <a href="<?php echo $downloadUrl; ?>" 
                                                   class="btn btn-outline-primary btn-sm view-report-btn" 
                                                   target="_blank" 
                                                   title="View Report">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-success btn-sm download-report-btn" 
                                                        data-report-id="<?php echo $report['report_id']; ?>" 
                                                        data-file-type="<?php echo $report['file_type'] ?? 'pdf'; ?>"
                                                        title="Download Report">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info reports-alert">
            <i class="fas fa-info-circle me-2"></i>
            Please select a reporting period to view available reports.
        </div>
    <?php endif; ?>
</div>
