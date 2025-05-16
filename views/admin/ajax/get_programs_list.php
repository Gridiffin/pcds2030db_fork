<?php
/**
 * AJAX endpoint to get filtered programs list
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/admin_functions.php';
require_once '../../../includes/status_helpers.php';

// Verify user is admin
if (!is_admin()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Process filters
$filters = [];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
if (isset($_GET['sector_id'])) $filters['sector_id'] = intval($_GET['sector_id']);
if (isset($_GET['agency_id'])) $filters['agency_id'] = intval($_GET['agency_id']);
if (isset($_GET['search'])) $filters['search'] = trim($_GET['search']);

// Add period_id handling for historical views
$current_period = get_current_reporting_period();
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);

// Get all programs with filters
$programs = get_admin_programs_list($period_id, $filters);

// Prepare HTML for the programs table
ob_start();
if (empty($programs)): ?>
    <tr>
        <td colspan="7" class="text-center py-4">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <?php if (isset($_GET['period_id'])): ?>
                    No programs were submitted for this reporting period.
                <?php else: ?>
                    No programs found matching your criteria.
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($programs as $program): ?>
        <tr>
            <td>
                <div class="fw-medium">
                    <?php echo htmlspecialchars($program['program_name']); ?>
                    <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                        <span class="badge bg-secondary ms-1">Draft</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($program['description'])): ?>
                    <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 50); ?><?php echo strlen($program['description']) > 50 ? '...' : ''; ?></div>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
            <td><?php echo htmlspecialchars($program['sector_name']); ?></td>
            <td>
                <?php if (isset($program['status'])): ?>
                    <?php 
                    $status = $program['status'];
                    $status_class = 'secondary'; // Default
                    $status_label = 'Not Started';
                    
                    switch($status) {
                        case 'on-track':
                        case 'on-track-yearly':
                            $status_class = 'warning';
                            $status_label = 'On Track';
                            break;
                        case 'delayed':
                        case 'severe-delay':
                            $status_class = 'danger';
                            $status_label = 'Delayed';
                            break;
                        case 'completed':
                        case 'target-achieved':
                            $status_class = 'success';
                            $status_label = 'Target Achieved';
                            break;
                        case 'not-started':
                            $status_class = 'secondary';
                            $status_label = 'Not Started';
                            break;
                    }
                    ?>
                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary">Not Reported</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (isset($program['start_date']) && $program['start_date']): ?>
                    <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                    <?php if (isset($program['end_date']) && $program['end_date']): ?>
                        <span class="text-muted">to</span> <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">Not specified</span>
                <?php endif; ?>
            </td>
            <td><?php echo date('M j, Y', strtotime($program['updated_at'])); ?></td>            <td>
                <div class="btn-group btn-group-sm d-flex flex-wrap justify-content-start">
                    <a href="view_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif;

// Get the HTML content
$tableHtml = ob_get_clean();

// Return response as JSON
echo json_encode([
    'status' => 'success',
    'tableHtml' => $tableHtml,
    'count' => count($programs),
    'filters' => !empty($filters)
]);
?>
