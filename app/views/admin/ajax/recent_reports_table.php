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
 * Check if a report should display the "NEW" badge
 * @param array $report Report data
 * @return bool True if report should show NEW badge
 */
function shouldShowNewBadge($report) {
    if (!$report || !isset($report['generated_at'])) {
        return false;
    }
    
    // Show badge for reports generated in the last 10 minutes
    $generatedTime = strtotime($report['generated_at']);
    $currentTime = time();
    $tenMinutesAgo = $currentTime - (10 * 60); // 10 minutes in seconds
    
    return $generatedTime > $tenMinutesAgo;
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
$recentReports = getRecentReports(25);

// Generate the card-grid HTML (matching the format in the main page)
if (!empty($recentReports)): ?>
    <div class="recent-reports-grid">        <?php foreach ($recentReports as $report): ?>
            <div class="report-card" data-report-id="<?php echo $report['report_id']; ?>">
                <?php if (shouldShowNewBadge($report)): ?>
                    <span class="new-report-badge">NEW</span>
                <?php endif; ?>
                <div class="report-card-body">
                    <div class="report-info">
                        <h6 class="report-title" title="<?php echo htmlspecialchars($report['report_name']); ?>">
                            <?php echo htmlspecialchars($report['report_name']); ?>
                        </h6>
                        <div class="report-meta">
                            <span class="period-badge">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatPeriod($report); ?>
                            </span>
                            <span class="date-badge">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($report['generated_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <?php if (!empty($report['pptx_path'])): ?>
                            <a href="<?php echo APP_URL; ?>/download.php?type=report&file=<?php echo urlencode($report['pptx_path']); ?>" 
                               class="btn btn-success btn-sm" 
                               title="Download Report">
                                <i class="fas fa-download"></i>
                            </a>
                        <?php endif; ?>
                        <button type="button" 
                                class="btn btn-outline-danger btn-sm delete-report-btn" 
                                title="Delete Report"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteReportModal"
                                data-report-id="<?php echo $report['report_id']; ?>" 
                                data-report-name="<?php echo htmlspecialchars($report['report_name']); ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state text-center py-5">
        <i class="fas fa-file-powerpoint fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No reports generated yet</h5>
        <p class="text-muted mb-3">Get started by generating your first report below.</p>
        <button type="button" class="btn btn-primary" id="generateReportToggleEmpty">
            <i class="fas fa-plus me-1"></i>Generate First Report
        </button>
    </div>
<?php endif; ?>

<!-- Auto refresh indicator -->
<div id="refreshIndicator" class="text-center mt-2" style="display: none;">
    <small class="text-muted">
        <i class="fas fa-sync fa-spin"></i> Refreshing...
    </small>
</div>