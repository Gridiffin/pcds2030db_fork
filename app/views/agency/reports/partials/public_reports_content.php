<?php
/**
 * Public Reports Content
 * Main content for the public reports page
 */

// Configure modern page header
$header_config = [
    'title' => 'Public Reports',
    'subtitle' => 'Download reports made available by administrators',
    'variant' => 'blue',
    'actions' => [
        [
            'url' => 'javascript:void(0)',
            'text' => 'Refresh',
            'icon' => 'fas fa-sync',
            'class' => 'btn-outline-primary refresh-reports-btn'
        ]
    ]
];

// Include modern page header
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<!-- Public Reports Content -->
<section class="section reports-container">
    <div class="container-fluid">
        <div class="reports-content">
            
            <!-- Search and Filter Section -->
            <div class="card reports-filter shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title">Search & Filter Reports</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="reports-search" class="form-label">Search Reports</label>
                            <input type="text" class="form-control" id="reports-search" 
                                   placeholder="Search by name or description...">
                        </div>
                        <div class="col-md-4">
                            <label for="report-type-filter" class="form-label">Report Type</label>
                            <select class="form-select" id="report-type-filter">
                                <option value="all">All Types</option>
                                <option value="pdf">PDF Reports</option>
                                <option value="pptx">PowerPoint Reports</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <span class="reports-count badge bg-primary">
                                    <?php echo count($public_reports); ?> Reports
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Public Reports List Section -->
            <div id="public-reports-container">
                <?php if (empty($public_reports)): ?>
                    <div class="col-12">
                        <div class="text-center py-5 reports-empty-state">
                            <i class="fas fa-file-alt"></i>
                            <p>No public reports are currently available for download.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($public_reports as $report): ?>
                            <?php
                            $downloadUrl = APP_URL . '/download.php?type=report&file=' . urlencode($report['file_path']);
                            $isRecent = (strtotime($report['generated_at']) > strtotime('-7 days'));
                            $fileTypeIcon = match($report['report_type'] ?? 'pdf') {
                                'pdf' => 'fas fa-file-pdf',
                                'pptx' => 'fas fa-file-powerpoint',
                                'xlsx' => 'fas fa-file-excel',
                                default => 'fas fa-file'
                            };
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 report-card <?php echo $isRecent ? 'recent-report' : ''; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <i class="<?php echo $fileTypeIcon; ?> fa-2x text-primary"></i>
                                        <?php if ($isRecent): ?>
                                            <span class="badge bg-warning text-dark">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title">
                                            <?php echo htmlspecialchars($report['report_name'] ?? 'Untitled Report'); ?>
                                        </h6>
                                        <p class="card-text flex-grow-1">
                                            <?php 
                                            $description = $report['description'] ?? 'No description available';
                                            echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 97) . '...' : $description);
                                            ?>
                                        </p>
                                        <div class="mt-auto">
                                            <small class="text-muted d-block mb-2">
                                                <i class="fas fa-calendar-alt"></i> 
                                                <?php echo date('M j, Y', strtotime($report['generated_at'])); ?>
                                            </small>
                                            <div class="d-flex gap-2">
                                                <a href="<?php echo $downloadUrl; ?>" 
                                                   class="btn btn-outline-primary btn-sm view-report-btn flex-grow-1" 
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <button class="btn btn-success btn-sm download-report-btn" 
                                                        data-report-id="<?php echo $report['report_id']; ?>" 
                                                        data-file-type="<?php echo $report['report_type'] ?? 'pdf'; ?>"
                                                        title="Download Report">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Info Section -->
            <div class="card reports-info shadow-sm">
                <div class="card-header">
                    <h5 class="card-title">About Public Reports</h5>
                </div>
                <div class="card-body">
                    <div class="reports-info-section">
                        <div class="reports-info-icon public">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="reports-info-content">
                            <h6 class="reports-info-title">What are Public Reports?</h6>
                            <p class="reports-info-description">
                                Public reports are reports generated by administrators that have been made available 
                                to all agencies for download. These reports may contain sector-wide insights, 
                                comparative analysis, or other information relevant to all agencies.
                            </p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Available Features</h6>
                            <ul class="reports-feature-list">
                                <li>
                                    <i class="fas fa-download"></i>
                                    Download reports in multiple formats
                                </li>
                                <li>
                                    <i class="fas fa-search"></i>
                                    Search reports by name or description
                                </li>
                                <li>
                                    <i class="fas fa-filter"></i>
                                    Filter reports by file type
                                </li>
                                <li>
                                    <i class="fas fa-sync"></i>
                                    Refresh to see new reports
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Report Information</h6>
                            <ul class="reports-feature-list">
                                <li>
                                    <i class="fas fa-star"></i>
                                    Recent reports are highlighted
                                </li>
                                <li>
                                    <i class="fas fa-eye"></i>
                                    View reports directly in browser
                                </li>
                                <li>
                                    <i class="fas fa-clock"></i>
                                    See when each report was generated
                                </li>
                                <li>
                                    <i class="fas fa-info-circle"></i>
                                    Detailed descriptions available
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<style>
/* Additional styles for public reports */
.report-card.recent-report {
    border-left: 4px solid #ffc107;
    background: linear-gradient(135deg, #fff, #fffbf0);
}

.report-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
