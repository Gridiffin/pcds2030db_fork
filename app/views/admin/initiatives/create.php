<?php
/**
 * Create Initiative Page
 * 
 * Admin interface for creating new initiatives.
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

// Set page title
$pageTitle = 'Create Initiative';

// Process form submission
$message = '';
$message_type = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = $_POST;
    $result = create_initiative($_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = 'Initiative created successfully.';
        $_SESSION['message_type'] = 'success';
        header('Location: manage_initiatives.php');
        exit;
    } else {
        $message = $result['error'] ?? 'Failed to create initiative.';
        $message_type = 'danger';
    }
}

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Create Initiative',
    'subtitle' => 'Create a new strategic initiative',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Back to Initiatives',
            'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php',
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
                            <div><?php echo htmlspecialchars($message); ?></div>                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-xl-8 col-lg-10">
                        <!-- Initiative Form -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Initiative Details
                                </h5>
                            </div>                            <div class="card-body">
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
                                                   <?php echo (isset($form_data['is_active']) && $form_data['is_active']) || !isset($form_data['is_active']) ? 'checked' : ''; ?>>
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
                                                <i class="fas fa-save me-1"></i>Create Initiative
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help Card -->
                    <div class="col-xl-4 col-lg-12 mt-xl-0 mt-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-1"></i>Initiative Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="small">
                                    <h6 class="fw-semibold">What is an Initiative?</h6>
                                    <p>Initiatives are high-level strategic goals that encompass multiple programs working toward common objectives.</p>
                                    
                                    <h6 class="fw-semibold">Best Practices:</h6>
                                    <ul class="mb-3">
                                        <li>Use clear, descriptive names</li>
                                        <li>Include measurable objectives</li>
                                        <li>Set realistic timelines</li>
                                        <li>Align with organizational strategy</li>
                                    </ul>
                                    
                                    <h6 class="fw-semibold">Examples:</h6>
                                    <ul class="mb-0">
                                        <li>Forest Conservation Initiative</li>
                                        <li>Sustainable Development Program</li>
                                        <li>Biodiversity Protection Strategy</li>
                                    </ul>
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
            </main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
