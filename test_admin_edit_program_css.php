<?php
/**
 * TEST VERSION - Admin Edit Program (No Authentication)
 * This is just to test the CSS loading without authentication requirements
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files for CSS loading
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Mock some data for testing
$program = [
    'program_id' => 1,
    'program_name' => 'Test Program for CSS',
    'program_number' => 'TEST-001',
    'program_description' => 'This is a test program to verify CSS loading',
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'initiative_id' => 1,
    'rating' => 'on_track',
    'agency_id' => 1,
    'agency_name' => 'Test Agency'
];

$active_initiatives = [
    ['initiative_id' => 1, 'initiative_name' => 'Test Initiative', 'initiative_number' => 'INIT-001']
];

$program_agency_info = [
    'agency_id' => 1,
    'agency_name' => 'Test Agency'
];

$assignable_users = [];
$current_user_assignments = [];
$restrict_editors = false;

// Set page title
$pageTitle = 'Edit Program (CSS Test)';

// Include header
require_once 'app/views/layouts/header.php';

// Configure modern page header with admin context
$program_display_name = '';
if (!empty($program['program_number'])) {
    $program_display_name = '<span class="badge bg-info me-2" title="Program Number">' . htmlspecialchars($program['program_number']) . '</span>';
}
$program_display_name .= htmlspecialchars($program['program_name']);

// Add agency information for admin context
if (!empty($program_agency_info)) {
    $program_display_name .= '<br><small class="text-muted">Agency: ' . htmlspecialchars($program_agency_info['agency_name']) . '</small>';
}

$header_config = [
    'title' => 'Edit Program (CSS Test)',
    'subtitle' => $program_display_name,
    'subtitle_html' => true,
    'variant' => 'white'
];

// Include modern page header
require_once 'app/views/layouts/page_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>CSS Test Page:</strong> This is a test version of the admin edit program page to verify CSS loading.
            </div>

            <!-- Simple Program Editing Form -->
            <div class="card shadow-sm mb-4 w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Program Information
                        <?php if (!empty($program_agency_info)): ?>
                            <span class="badge bg-secondary ms-2">
                                <?php echo htmlspecialchars($program_agency_info['agency_name']); ?>
                            </span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="editProgramForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Program Name -->
                                <div class="mb-4">
                                    <label for="program_name" class="form-label">
                                        Program Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="program_name" 
                                           name="program_name" 
                                           required
                                           placeholder="Enter the program name"
                                           value="<?php echo htmlspecialchars($program['program_name']); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This will be the main identifier for your program
                                    </div>
                                </div>

                                <!-- Initiative Selection -->
                                <div class="mb-4">
                                    <label for="initiative_id" class="form-label">
                                        Link to Initiative
                                        <span class="badge bg-secondary ms-1">Optional</span>
                                    </label>
                                    <select class="form-select" id="initiative_id" name="initiative_id">
                                        <option value="">Select an initiative (optional)</option>
                                        <?php foreach ($active_initiatives as $initiative): ?>
                                            <option value="<?php echo $initiative['initiative_id']; ?>"
                                                    <?php echo $program['initiative_id'] == $initiative['initiative_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                                <?php if ($initiative['initiative_number']): ?>
                                                    (<?php echo htmlspecialchars($initiative['initiative_number']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Brief Description -->
                                <div class="mb-4">
                                    <label for="brief_description" class="form-label">Brief Description</label>
                                    <textarea class="form-control" 
                                              id="brief_description" 
                                              name="brief_description"
                                              rows="3"
                                              placeholder="Provide a short summary of the program"><?php echo htmlspecialchars($program['program_description'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Timeline Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Timeline
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Start Date -->
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_date" 
                                                   name="start_date"
                                                   value="<?php echo htmlspecialchars($program['start_date'] ?? ''); ?>">
                                        </div>

                                        <!-- End Date -->
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="end_date" 
                                                   name="end_date"
                                                   value="<?php echo htmlspecialchars($program['end_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Card -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            CSS Test Status
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Bootstrap CSS: Loaded
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-palette text-primary me-2"></i>
                                                Admin Bundle: Should be loaded
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-cog text-info me-2"></i>
                                                FontAwesome: Working
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Program (Test)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'app/views/layouts/footer.php';
?>
