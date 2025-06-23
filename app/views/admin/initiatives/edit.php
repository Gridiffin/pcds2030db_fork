<?php
/**
 * Edit Initiative Page
 * 
 * Admin interface for editing existing initiatives.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get initiative data
$initiative = get_initiative_by_id($initiative_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Set page title
$pageTitle = 'Edit Initiative - ' . $initiative['initiative_name'];

// Process form submission
$message = '';
$message_type = '';
$form_data = $initiative; // Start with existing data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = array_merge($initiative, $_POST); // Merge with form data
    $result = update_initiative($initiative_id, $_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = 'Initiative updated successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: manage_initiatives.php');
        exit;
    } else {
        $message = $result['error'] ?? 'Failed to update initiative.';
        $message_type = 'danger';
    }
}

// Get associated programs
$associated_programs = get_initiative_programs($initiative_id);

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0"><?php echo $pageTitle; ?></h1>
                    <p class="text-muted mb-0">Modify initiative details and settings</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="manage_initiatives.php">Initiatives</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-xl-8 col-lg-10">
                    <!-- Error Message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Initiative Form -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lightbulb me-2"></i>Initiative Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="initiativeForm">
                                <div class="row">
                                    <!-- Initiative Name -->
                                    <div class="col-md-8 mb-3">
                                        <label for="initiative_name" class="form-label">
                                            Initiative Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="initiative_name" 
                                               name="initiative_name" 
                                               value="<?php echo htmlspecialchars($form_data['initiative_name'] ?? ''); ?>" 
                                               required>
                                        <div class="form-text">Enter a clear, descriptive name for the initiative</div>
                                    </div>
                                    
                                    <!-- Initiative Number -->
                                    <div class="col-md-4 mb-3">
                                        <label for="initiative_number" class="form-label">Initiative Number</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="initiative_number" 
                                               name="initiative_number" 
                                               value="<?php echo htmlspecialchars($form_data['initiative_number'] ?? ''); ?>" 
                                               placeholder="e.g., PCDS-CI-001">
                                        <div class="form-text">Optional reference number</div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="initiative_description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="initiative_description" 
                                              name="initiative_description" 
                                              rows="4" 
                                              placeholder="Describe the initiative's purpose, goals, and scope..."><?php echo htmlspecialchars($form_data['initiative_description'] ?? ''); ?></textarea>
                                    <div class="form-text">Provide a detailed description of the initiative</div>
                                </div>

                                <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="start_date" 
                                               name="start_date" 
                                               value="<?php echo htmlspecialchars($form_data['start_date'] ?? ''); ?>">
                                        <div class="form-text">When does this initiative begin?</div>
                                    </div>
                                    
                                    <!-- End Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="end_date" 
                                               name="end_date" 
                                               value="<?php echo htmlspecialchars($form_data['end_date'] ?? ''); ?>">
                                        <div class="form-text">When is this initiative expected to complete?</div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               <?php echo (!empty($form_data['is_active'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Active initiatives can have programs assigned to them</div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-between">
                                    <a href="manage_initiatives.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Initiatives
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-undo me-1"></i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Update Initiative
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Info and Associated Programs -->
                <div class="col-xl-4 col-lg-12 mt-xl-0 mt-4">
                    <!-- Initiative Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-1"></i>Initiative Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <div class="row mb-2">
                                    <div class="col-sm-5 fw-semibold">Created:</div>
                                    <div class="col-sm-7"><?php echo date('M j, Y g:i A', strtotime($initiative['created_at'])); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-5 fw-semibold">Created By:</div>
                                    <div class="col-sm-7"><?php echo htmlspecialchars($initiative['created_by_username'] ?? 'Unknown'); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-5 fw-semibold">Last Updated:</div>
                                    <div class="col-sm-7"><?php echo date('M j, Y g:i A', strtotime($initiative['updated_at'])); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5 fw-semibold">Status:</div>
                                    <div class="col-sm-7">
                                        <span class="badge bg-<?php echo $initiative['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $initiative['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Programs -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-project-diagram me-1"></i>Associated Programs
                                <span class="badge bg-primary ms-2"><?php echo count($associated_programs); ?></span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($associated_programs)): ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                    <p class="mb-0 small">No programs assigned to this initiative yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($associated_programs as $program): ?>
                                        <div class="list-group-item px-0">
                                            <div class="fw-semibold small"><?php echo htmlspecialchars($program['program_name']); ?></div>
                                            <div class="text-muted small">
                                                <?php if ($program['program_number']): ?>
                                                    <?php echo htmlspecialchars($program['program_number']); ?> • 
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($program['sector_name']); ?> • 
                                                <?php echo htmlspecialchars($program['agency_name']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($associated_programs) > 5): ?>
                                    <div class="mt-2 text-center">
                                        <small class="text-muted">Showing first 5 programs</small>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('initiativeForm');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    // Date validation
    function validateDates() {
        if (startDate.value && endDate.value) {
            if (new Date(startDate.value) >= new Date(endDate.value)) {
                endDate.setCustomValidity('End date must be after start date');
            } else {
                endDate.setCustomValidity('');
            }
        } else {
            endDate.setCustomValidity('');
        }
    }
    
    startDate.addEventListener('change', validateDates);
    endDate.addEventListener('change', validateDates);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        validateDates();
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php
// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
