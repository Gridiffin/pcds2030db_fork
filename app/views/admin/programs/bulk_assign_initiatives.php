<?php
/**
 * Bulk Initiative Assignment for Programs
 * Allows admins to assign multiple programs to initiatives at once
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';
require_once ROOT_PATH . 'app/lib/numbering_helpers.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Handle POST request for bulk assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_ids = $_POST['program_ids'] ?? [];
    $initiative_id = $_POST['initiative_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign' && !empty($program_ids)) {
        $success_count = 0;
        $error_count = 0;
        $updated_programs = [];
          foreach ($program_ids as $program_id) {
            $program_id = intval($program_id);
            
            // Use hierarchical numbering system
            if ($initiative_id === 'remove') {
                // Remove from initiative and clear program number
                $sql = "UPDATE programs SET initiative_id = NULL, program_number = NULL WHERE program_id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt && $stmt->bind_param('i', $program_id) && $stmt->execute()) {
                    $success_count++;
                    $updated_programs[] = $program_id;
                } else {
                    $error_count++;
                }
                if ($stmt) $stmt->close();
            } else {
                // Assign to initiative (no auto-numbering - users must set numbers manually)
                $sql = "UPDATE programs SET initiative_id = ? WHERE program_id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt && $stmt->bind_param('ii', $initiative_id, $program_id) && $stmt->execute()) {
                    $success_count++;
                    $updated_programs[] = $program_id;
                } else {
                    $error_count++;
                }
                if ($stmt) $stmt->close();
            }
        }
        
        // Log the bulk action
        $initiative_name = 'None';
        if ($initiative_id && $initiative_id !== 'remove') {
            $initiative = get_initiative_by_id($initiative_id);
            $initiative_name = $initiative ? $initiative['initiative_name'] : 'Unknown';
        }
        
        $log_message = "Bulk assignment: {$success_count} programs assigned to '{$initiative_name}' initiative. Program IDs: " . implode(', ', $updated_programs);
        if ($error_count > 0) {
            $log_message .= ". {$error_count} programs failed to update.";
        }
        
        log_audit_action('bulk_assign_initiatives', $log_message, $error_count > 0 ? 'warning' : 'success', $_SESSION['user_id']);
        
        // Set session message
        if ($success_count > 0) {
            $_SESSION['message'] = "Successfully updated {$success_count} program(s).";
            $_SESSION['message_type'] = 'success';
            if ($error_count > 0) {
                $_SESSION['message'] .= " {$error_count} program(s) failed to update.";
                $_SESSION['message_type'] = 'warning';
            }
        } else {
            $_SESSION['message'] = "No programs were updated.";
            $_SESSION['message_type'] = 'danger';
        }
        
        // Redirect back to programs page
        header('Location: ' . APP_URL . '/app/views/admin/programs/programs.php');
        exit;
    }
}

// Get current reporting period
$current_period = get_current_reporting_period();
$period_id = $current_period ? $current_period['period_id'] : null;

// Get all programs for the current period
$programs = get_admin_programs_list($period_id, []);

// Get all active initiatives
$initiatives = get_initiatives_for_select(true);

// Get all sectors for grouping
$sectors = get_all_sectors();

// Set page title
$pageTitle = 'Bulk Initiative Assignment';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/bulk_assign_initiatives.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Bulk Initiative Assignment',
    'subtitle' => 'Assign multiple programs to initiatives at once',
    'variant' => 'purple',
    'actions' => [
        [
            'text' => 'Back to Programs',
            'url' => APP_URL . '/app/views/admin/programs/programs.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';

// Check for session messages
$message = '';
$message_type = '';

if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<main class="flex-fill">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
                <div><?php echo htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Assignment Controls -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="fas fa-cogs me-2"></i>Assignment Controls
                    </h5>
                </div>
                <div class="card-body">
                    <form id="bulkAssignForm" method="POST" action="">
                        <input type="hidden" name="action" value="assign">
                        
                        <div class="mb-3">
                            <label for="initiativeSelect" class="form-label">Target Initiative</label>
                            <select class="form-select" id="initiativeSelect" name="initiative_id" required>
                                <option value="">Select Initiative</option>
                                <option value="remove">Remove Initiative Assignment</option>
                                <?php foreach ($initiatives as $initiative): ?>
                                    <option value="<?php echo $initiative['initiative_id']; ?>">
                                        <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label m-0">Selected Programs</label>
                                <span id="selectedCount" class="badge bg-info">0 selected</span>
                            </div>
                            <div id="selectedPrograms" class="selected-programs-list">
                                <p class="text-muted text-center py-3">No programs selected</p>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" id="assignButton" class="btn btn-primary" disabled>
                                <i class="fas fa-link me-2"></i>Assign Selected Programs
                            </button>
                            <button type="button" id="clearSelection" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear Selection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Programs List -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">
                        <i class="fas fa-list me-2"></i>All Programs
                    </h5>
                    <span class="badge bg-info"><?php echo count($programs); ?> Programs</span>
                </div>
                
                <!-- Filters -->
                <div class="card-body pb-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="programSearch" class="form-label">Search Programs</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="programSearch" placeholder="Search by name or number">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="initiativeFilter" class="form-label">Current Initiative</label>
                            <select class="form-select" id="initiativeFilter">
                                <option value="">All Initiatives</option>
                                <option value="none">Not Assigned</option>
                                <?php foreach ($initiatives as $initiative): ?>
                                    <option value="<?php echo $initiative['initiative_id']; ?>">
                                        <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllPrograms">
                                <label class="form-check-label" for="selectAllPrograms">
                                    Select All Visible Programs
                                </label>
                            </div>
                            <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body pt-2 p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0" id="programsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllVisible">
                                        </div>
                                    </th>
                                    <th>Program Name</th>
                                    <th>Current Initiative</th>
                                    <th>Sector</th>
                                    <th>Agency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programs as $program): ?>
                                    <tr data-program-id="<?php echo $program['program_id']; ?>"
                                        data-sector-id="<?php echo $program['sector_id']; ?>"
                                        data-initiative-id="<?php echo $program['initiative_id'] ?? ''; ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input program-checkbox" 
                                                       type="checkbox" 
                                                       value="<?php echo $program['program_id']; ?>"
                                                       name="program_ids[]">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">
                                                <?php if (!empty($program['program_number'])): ?>
                                                    <span class="badge bg-info me-2"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($program['program_name']); ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?php echo htmlspecialchars($program['agency_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($program['initiative_name'])): ?>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    <?php echo htmlspecialchars($program['initiative_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    <i class="fas fa-minus me-1"></i>Not Assigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($program['sector_name']); ?></td>
                                        <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Store program data for JavaScript -->
<script>
    const programsData = <?php echo json_encode($programs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
