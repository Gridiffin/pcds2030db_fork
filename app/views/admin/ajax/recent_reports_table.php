<?php
/**
 * Recent Reports Table AJAX Endpoint
 * 
 * Returns HTML for the recent reports table to be dynamically updated
 * after a new report is generated.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../lib/db_connect.php';
require_once '../../../lib/session.php';
require_once '../../../lib/functions.php';
require_once '../../../lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    echo '<div class="alert alert-danger">Access denied</div>';
    exit;
}

/**
 * Format period display name (copied from main page)
 * @param array $report Report data with quarter and year
 * @return string Formatted period name
 */
function formatPeriod($report) {
    if (!$report || !isset($report['quarter'], $report['year'])) {
        return 'Unknown';
    }
    
    $quarter = (int)$report['quarter'];
    $year = $report['year'];
    
    if ($quarter >= 1 && $quarter <= 4) {
        return "Q{$quarter} {$year}";
    } elseif ($quarter == 5) {
        return "H1 {$year}";
    } elseif ($quarter == 6) {
        return "H2 {$year}";
    }
    
    return "Period {$quarter} {$year}";
}

/**
 * Get recently generated reports directly from database
 * @param int $limit Number of reports to retrieve
 * @return array Array of recent reports
 */
function getRecentReports($limit = 10) {
    global $conn;
    
    $query = "SELECT r.report_id, r.report_name, r.pptx_path, r.generated_at, r.is_public,
                     rp.quarter, rp.year, u.username
              FROM reports r 
              LEFT JOIN reporting_periods rp ON r.period_id = rp.period_id 
              LEFT JOIN users u ON r.generated_by = u.user_id 
              ORDER BY r.generated_at DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

// Get recent reports
$recentReports = getRecentReports(10);

// Generate the table HTML (matching the format in the main page)
if (!empty($recentReports)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Period</th>
                    <th>Generated</th>
                    <th>By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentReports as $report): ?>
                    <tr>
                        <td>
                            <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                                 title="<?php echo htmlspecialchars($report['report_name']); ?>">
                                <?php echo htmlspecialchars($report['report_name']); ?>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php echo formatPeriod($report); ?>
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php echo date('M j, Y g:i A', strtotime($report['generated_at'])); ?>
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($report['username'] ?? 'Unknown'); ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (!empty($report['pptx_path'])): ?>
                                    <a href="<?php echo APP_URL; ?>/download.php?type=report&file=<?php echo urlencode($report['pptx_path']); ?>" 
                                       class="btn btn-outline-success btn-sm" 
                                       title="Download Report">
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php endif; ?>                                <button type="button" 
                                        class="btn btn-outline-danger btn-sm delete-report-btn" 
                                        title="Delete Report"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteReportModal"
                                        data-report-id="<?php echo $report['report_id']; ?>" 
                                        data-report-name="<?php echo htmlspecialchars($report['report_name']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="text-center text-muted py-4">
        <i class="fas fa-file-powerpoint fa-3x mb-3 opacity-50"></i>
        <p class="mb-0">No reports generated yet.</p>
        <small>Generated reports will appear here.</small>
    </div>
<?php endif; ?>