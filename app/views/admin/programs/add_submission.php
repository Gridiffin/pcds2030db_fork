<?php
/**
 * Add Submission to Program
 * 
 * Allows users to add a submission for a specific reporting period to an existing program.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/core.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/program_management.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/statistics.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'No program specified.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program details
$program = get_admin_program_details($program_id);
if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Admin users have full submission permissions
$can_edit = true;

// Get reporting periods for dropdown
$reporting_periods = get_reporting_periods_for_dropdown(true);

// Get existing submissions for this program to show which periods are already covered
$existing_submissions_query = "SELECT ps.period_id, ps.is_draft, ps.is_submitted, ps.submission_id,
                                     rp.year, rp.period_type, rp.period_number
                              FROM program_submissions ps
                              JOIN reporting_periods rp ON ps.period_id = rp.period_id
                              WHERE ps.program_id = ? AND ps.is_deleted = 0
                              ORDER BY rp.year DESC, rp.period_number ASC";
$stmt = $conn->prepare($existing_submissions_query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$existing_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_data = [
        'program_id' => $program_id,
        'period_id' => !empty($_POST['period_id']) ? intval($_POST['period_id']) : null,
        'description' => $_POST['description'] ?? '',
        'targets' => [] // Will be populated from form data
    ];
    
    // Handle targets array data
    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        $target_texts = $_POST['target_text'];
        $target_numbers = $_POST['target_number'] ?? [];
        $target_statuses = $_POST['target_status'] ?? [];
        $target_status_descriptions = $_POST['target_status_description'] ?? [];
        
        for ($i = 0; $i < count($target_texts); $i++) {
            $target_text = trim($target_texts[$i] ?? '');
            if (!empty($target_text)) {
                $submission_data['targets'][] = [
                    'target_number' => trim($target_numbers[$i] ?? ''),
                    'target_text' => $target_text,
                    'target_status' => trim($target_statuses[$i] ?? 'not_started'),
                    'status_description' => trim($target_status_descriptions[$i] ?? ''),
                ];
            }
        }
    }
    
    // Create submission
    $result = create_program_submission($submission_data);
    
    // 1) Change the redirect after saving as draft to programs.php
    if (isset($result['success']) && $result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        header('Location: programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while creating the submission.';
        $messageType = 'danger';
    }
}

// Set up variables for base_admin layout
$pageTitle = 'Add Submission (Admin) - ' . $program['program_name'];
$cssBundle = 'admin-add-submission'; // Specific bundle for add submission page
$jsBundle = 'admin-add-submission';
$contentFile = __DIR__ . '/partials/add_submission_content.php';

// Configure modern page header
$header_config = [
    'title' => 'Add Submission (Admin)',
    'subtitle' => 'Add a submission for ' . htmlspecialchars($program['program_name']) . ' | Agency: ' . ($program['agency_name'] ?? 'Unknown'),
    'variant' => 'admin',
    'actions' => [
        [
            'url' => 'programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>


<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Error/Success Messages -->
            <?php if (!empty($message)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
                    });
                </script>
            <?php endif; ?>

            <!-- Program Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Program Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?><br>
                            <strong>Program Number:</strong> <?php echo htmlspecialchars($program['program_number'] ?? 'Not assigned'); ?><br>
                            <strong>Initiative:</strong> 
                            <?php if (!empty($program['initiative_name'])): ?>
                                <?php echo htmlspecialchars($program['initiative_name']); ?>
                                <?php if (!empty($program['initiative_number'])): ?>
                                    (<?php echo htmlspecialchars($program['initiative_number']); ?>)
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not linked</span>
                            <?php endif; ?><br>
                            <strong>Agency:</strong> <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> <?php echo htmlspecialchars($program['program_description'] ?? 'No description'); ?><br>
                            <strong>Created:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?><br>
                            <strong>Existing Submissions:</strong> <?php echo count($existing_submissions); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Submissions Summary -->
            <?php if (!empty($existing_submissions)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Existing Submissions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existing_submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['year'] . ' ' . ucfirst($submission['period_type']) . ' ' . $submission['period_number']); ?></td>
                                    <td><?php echo $submission['is_draft'] ? 'Draft' : ($submission['is_submitted'] ? 'Finalized' : 'Unknown'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $submission['period_type']))); ?>
                                    </td>
                                    <td>
                                        <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Submission">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Add Submission Form -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Submission
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="addSubmissionForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Reporting Period Selection -->
                                <div class="mb-4">
                                    <label for="period_id" class="form-label">
                                        Reporting Period <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="period_id" name="period_id" required>
                                        <option value="">Select a reporting period</option>
                                        <?php foreach ($reporting_periods as $period): ?>
                                            <?php 
                                            // Check if this period already has a submission
                                            $has_submission = false;
                                            foreach ($existing_submissions as $existing) {
                                                if ($existing['period_id'] == $period['period_id']) {
                                                    $has_submission = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <option value="<?php echo $period['period_id']; ?>"
                                                    <?php echo (isset($_POST['period_id']) && $_POST['period_id'] == $period['period_id']) ? 'selected' : ''; ?>
                                                    data-status="<?php echo $period['status']; ?>"
                                                    <?php echo $has_submission ? 'disabled' : ''; ?>>
                                                <?php echo htmlspecialchars($period['display_name']); ?>
                                                <?php if ($period['status'] == 'open'): ?>
                                                    (Open)
                                                <?php endif; ?>
                                                <?php if ($has_submission): ?>
                                                    (Already has submission)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-calendar me-1"></i>
                                        Select the reporting period for this submission. 
                                        <span class="text-success">Open</span> periods are currently accepting submissions.
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description"
                                              rows="3"
                                              placeholder="Describe the submission for this period"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide details about the program's progress during this reporting period
                                    </div>
                                </div>

                                <!-- Targets Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bullseye me-2"></i>
                                            Targets
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">
                                            Add targets for this submission period. You can add more targets later.
                                        </p>
                                        <div id="targets-container">
                                            <!-- Targets will be added here by JavaScript -->
                                        </div>
                                        <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-plus-circle me-1"></i> Add Target
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Submission Info Card only -->
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Submission Info
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-plus text-primary me-2"></i>
                                                Creates a new submission for the selected period
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-edit text-info me-2"></i>
                                                You can edit this submission later
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-paperclip text-warning me-2"></i>
                                                Add attachments after creating
                                            </li>
                                            <li>
                                                <i class="fas fa-save text-success me-2"></i>
                                                Save as draft or ask focal to submit when ready
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Attachment Section -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">
                                            <i class="fas fa-paperclip me-2"></i>
                                            Attachments
                                        </h6>
                                        <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                            <i class="fas fa-plus me-1"></i> Add File(s)
                                        </button>
                                        <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                                        <div class="form-text mt-1">
                                            You can add files one by one or in batches. Allowed types: PDF, DOCX, XLSX, PNG, JPG, etc.
                                        </div>
                                        <ul id="attachments-list" class="list-unstyled small mt-2"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <div>
                                <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-2"></i>
                                    Save as Draft
                                </button>
                                <!-- Submit button removed -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass program data to JavaScript
window.programId = <?php echo json_encode($program_id); ?>;
window.programNumber = <?php echo json_encode($program['program_number'] ?? ''); ?>;
</script>
<script src="<?php echo asset_url('js/agency', 'add_submission.js'); ?>"></script>

<?php
// Include footer
// Include the admin base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
?> 