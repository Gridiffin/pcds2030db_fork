<?php
/**
 * Agency Sectors View AJAX Data Provider
 * 
 * Returns JSON with HTML content for the sectors view based on selected period.
 */

// Include necessary files
require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../lib/db_connect.php';
require_once __DIR__ . '/../../../../lib/session.php';
require_once __DIR__ . '/../../../../lib/functions.php';
require_once __DIR__ . '/../../../../lib/agencies/index.php';
require_once __DIR__ . '/../../../../lib/rating_helpers.php';
require_once __DIR__ . '/../../../../lib/audit_log.php';

// Verify user is an agency
if (!is_agency()) {
    // Log unauthorized agency sectors access attempt
    log_audit_action(
        'agency_sectors_access_denied',
        'Unauthorized attempt to access agency sectors data',
        'failure'
    );
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Get the requested period ID
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$period = $period_id ? get_reporting_period($period_id) : get_current_reporting_period();

if (!$period) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Period not found']);
    exit;
}

// Get all sectors programs for the selected period
$all_programs = get_all_sectors_programs($period_id);

// Get current agency's sector
$current_sector_id = $_SESSION['sector_id'];

// Get all sectors from the database
$sectors = get_all_sectors();

// Start output buffer to capture HTML
ob_start();
?>

<!-- Programs Content HTML -->
<?php if (empty($all_programs) || isset($all_programs['error'])): ?>
    <?php if (!empty($infoMessage)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Info', <?= json_encode($infoMessage) ?>, 'info');
        });
    </script>
    <?php endif; ?>
<?php else: ?>
    <!-- Unified Programs Table (replacing tabs) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary">
            <h5 class="card-title m-0 text-white"><i class="fas fa-list me-2 text-white"></i>Programs <span class="badge bg-light text-primary ms-2" id="programCount"><?php echo count($all_programs); ?></span></h5>
        </div>
        <div class="card-body">
            <!-- No Results Message (initially hidden) -->
            <div id="noResultsMessage" class="alert alert-info" style="display: none;">
                <i class="fas fa-search me-2"></i>
                No programs match your search criteria. Try adjusting your filters.
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="programsTable">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Sector</th>
                            <th>Agency</th>
                            <th>Status</th>
                            <th>Timeline</th>
                            <th>Current Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($all_programs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No programs found for this view.
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_programs as $program): ?>
                                <tr class="<?php echo ($program['sector_id'] == $current_sector_id) ? 'current-sector-row' : ''; ?>"
                                    data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                    data-agency="<?php echo htmlspecialchars($program['agency_name']); ?>"
                                    data-sector="<?php echo htmlspecialchars($program['sector_name']); ?>"
                                    data-sector-id="<?php echo $program['sector_id']; ?>"
                                    data-status="<?php echo htmlspecialchars(convert_legacy_status($program['status'] ?? 'not-reported')); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($program['program_name']); ?></strong>
                                        <?php if (!empty($program['description'])): ?>
                                            <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($program['sector_name']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                    <td>
                                        <?php if (isset($program['status']) && $program['status']): ?>
                                            <?php echo get_status_badge($program['status']); ?>
                                            <?php if (isset($program['status_date']) && $program['status_date']): ?>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($program['status_date'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Not Reported</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                            <div>
                                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                                <?php if (isset($program['end_date']) && $program['end_date']): ?>
                                                    - <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($program['achievement']) && $program['achievement']): ?>
                                            <?php echo htmlspecialchars($program['achievement']); ?>
                                        <?php elseif (isset($program['status_text']) && $program['status_text']): ?>
                                            <?php echo htmlspecialchars($program['status_text']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No progress reported</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$programs_html = ob_get_clean();

// Return just the HTML content without chart data
echo json_encode([
    'programs_content' => $programs_html
]);
?>
