<?php
/**
 * AJAX endpoint to get filtered programs list
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/admins/statistics.php';

// Verify user is admin
if (!is_admin()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Process filters
$filters = [];
if (isset($_GET['rating'])) $filters['status'] = $_GET['rating'];  // Still maps to status in DB but uses "rating" in UI
if (isset($_GET['sector_id'])) $filters['sector_id'] = intval($_GET['sector_id']);
if (isset($_GET['agency_id'])) $filters['agency_id'] = intval($_GET['agency_id']);
if (isset($_GET['search'])) $filters['search'] = trim($_GET['search']);

// Add program type filter
if (isset($_GET['program_type']) && in_array($_GET['program_type'], ['assigned', 'agency'])) {
    $filters['is_assigned'] = $_GET['program_type'] === 'assigned' ? true : false;
}

// Add period_id handling for historical views
$current_period = get_current_reporting_period();
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);

// Get all programs with filters
$programs = get_admin_programs_list($period_id, $filters);

// Prepare HTML for the programs table
ob_start();
if (empty($programs)): ?>
    <tr>
        <td colspan="6" class="text-center py-4">
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
<?php else: ?>    <?php foreach ($programs as $program): ?>
        <tr>
            <td>
                <div class="fw-medium">
                    <a href="view_program.php?id=<?php echo $program['program_id']; ?>">
                        <?php echo htmlspecialchars($program['program_name']); ?>
                    </a>
                    <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                        <span class="badge bg-light text-dark ms-1">Draft</span>
                    <?php endif; ?>
                </div>
            </td>
            <td><?php echo htmlspecialchars($program['sector_name']); ?></td>
            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
            <td class="text-center">
                <?php 
                $rating_map = [
                    'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                    'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                    'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                    'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                    'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                    'completed' => ['label' => 'Completed', 'class' => 'primary'],
                    'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                ];
                $current_rating = isset($program['rating']) ? $program['rating'] : 'not-started';
                if (!isset($rating_map[$current_rating])) {
                    $current_rating = 'not-started';
                }
                echo '<span class="badge bg-' . $rating_map[$current_rating]['class'] . '">' . $rating_map[$current_rating]['label'] . '</span>';
                ?>
            </td>
            <td class="text-center">
                <?php if (!empty($program['updated_at']) && $program['updated_at'] !== '0000-00-00 00:00:00'): ?>
                    <small><?php echo date('M j, Y g:i A', strtotime($program['updated_at'])); ?></small>
                <?php elseif (!empty($program['submission_date']) && $program['submission_date'] !== '0000-00-00 00:00:00'): ?>
                    <small><?php echo date('M j, Y g:i A', strtotime($program['submission_date'])); ?></small>
                <?php else: ?>
                    <small class="text-muted">--</small>
                <?php endif; ?>
            </td>
            <td class="text-center">
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
