<?php
/**
 * Add Submission to Program
 * 
 * Allows users to add a submission for a specific reporting period to an existing program.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'No program specified.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);
if (!$program) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get reporting periods for dropdown
$reporting_periods = get_reporting_periods_for_dropdown(true);

// Get existing submissions for this program to show which periods are already covered
$existing_submissions_query = "SELECT ps.period_id, ps.is_draft, ps.is_submitted, ps.status_indicator, ps.rating,
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
        'status_indicator' => $_POST['status_indicator'] ?? 'not_started',
        'rating' => $_POST['rating'] ?? 'not_started',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'targets' => [] // Will be populated from form data
    ];
    
    // Handle targets array data
    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        $target_texts = $_POST['target_text'];
        $target_numbers = $_POST['target_number'] ?? [];
        $target_statuses = $_POST['target_status'] ?? [];
        $target_status_descriptions = $_POST['target_status_description'] ?? [];
        $target_start_dates = $_POST['target_start_date'] ?? [];
        $target_end_dates = $_POST['target_end_date'] ?? [];
        
        for ($i = 0; $i < count($target_texts); $i++) {
            $target_text = trim($target_texts[$i] ?? '');
            if (!empty($target_text)) {
                $submission_data['targets'][] = [
                    'target_number' => trim($target_numbers[$i] ?? ''),
                    'target_text' => $target_text,
                    'target_status' => trim($target_statuses[$i] ?? 'not_started'),
                    'status_description' => trim($target_status_descriptions[$i] ?? ''),
                    'start_date' => !empty($target_start_dates[$i]) ? $target_start_dates[$i] : null,
                    'end_date' => !empty($target_end_dates[$i]) ? $target_end_dates[$i] : null
                ];
            }
        }
    }
    
    // Create submission
    $result = create_program_submission($submission_data);
    
    if (isset($result['success']) && $result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        header('Location: program_details.php?id=' . $program_id);
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while creating the submission.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Add Submission - ' . $program['program_name'];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Add Submission',
    'subtitle' => 'Add a submission for ' . htmlspecialchars($program['program_name']),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'program_details.php?id=' . $program_id,
            'text' => 'Back to Program',
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
                                    <th>Rating</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existing_submissions as $submission): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $period_display = $submission['period_type'] . ' ' . $submission['period_number'] . ' ' . $submission['year'];
                                        echo htmlspecialchars(ucfirst($period_display));
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($submission['is_draft']): ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php elseif ($submission['is_submitted']): ?>
                                            <span class="badge bg-success">Submitted</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $submission['rating']))); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $submission['status_indicator']))); ?>
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

                                <!-- Status and Rating -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="status_indicator" class="form-label">Status Indicator</label>
                                        <select class="form-select" id="status_indicator" name="status_indicator">
                                            <option value="not_started" <?php echo (isset($_POST['status_indicator']) && $_POST['status_indicator'] == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                                            <option value="in_progress" <?php echo (isset($_POST['status_indicator']) && $_POST['status_indicator'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo (isset($_POST['status_indicator']) && $_POST['status_indicator'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="delayed" <?php echo (isset($_POST['status_indicator']) && $_POST['status_indicator'] == 'delayed') ? 'selected' : ''; ?>>Delayed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="rating" class="form-label">Rating</label>
                                        <select class="form-select" id="rating" name="rating">
                                            <option value="not_started" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                                            <option value="monthly_target_achieved" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'monthly_target_achieved') ? 'selected' : ''; ?>>Monthly Target Achieved</option>
                                            <option value="on_track_for_year" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'on_track_for_year') ? 'selected' : ''; ?>>On Track for Year</option>
                                            <option value="severe_delay" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'severe_delay') ? 'selected' : ''; ?>>Severe Delay</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description"
                                              rows="3"
                                              placeholder="Describe the progress and status for this period"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide details about the program's progress during this reporting period
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="start_date" 
                                               name="start_date"
                                               value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="end_date" 
                                               name="end_date"
                                               value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
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

                                <!-- Info Card -->
                                <div class="card shadow-sm mt-3">
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
                                                Save as draft or submit when ready
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <div>
                                <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-2"></i>
                                    Save as Draft
                                </button>
                                <button type="submit" name="submit" value="1" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#period_id option[data-status="open"] {
    font-weight: bold;
    color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period_id');
    const targetsContainer = document.getElementById('targets-container');
    const addTargetBtn = document.getElementById('add-target-btn');
    
    // Highlight open periods
    Array.from(periodSelect.options).forEach(option => {
        if (option.dataset.status === 'open') {
            option.classList.add('text-success', 'fw-bold');
        }
    });

    // Target management
    let targetCounter = 0;

    const addNewTarget = () => {
        targetCounter++;
        const targetEntry = document.createElement('div');
        targetEntry.className = 'target-entry border rounded p-2 mb-2 position-relative';
        targetEntry.innerHTML = `
            <button type="button" class="btn-close remove-target" aria-label="Remove target" style="position: absolute; top: 5px; right: 5px;"></button>
            <div class="mb-2">
                <label class="form-label small">Target ${targetCounter}</label>
                <textarea class="form-control form-control-sm" name="target_text[]" rows="2" placeholder="Define a measurable target" required></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" class="form-control form-control-sm" name="target_number[]" placeholder="Number (optional)">
                </div>
                <div class="col-6">
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1" placeholder="Status description (optional)"></textarea>
            </div>
        `;
        targetsContainer.appendChild(targetEntry);

        // Add remove functionality
        const removeBtn = targetEntry.querySelector('.remove-target');
        removeBtn.addEventListener('click', () => {
            targetEntry.remove();
            updateTargetNumbers();
        });
    };

    const updateTargetNumbers = () => {
        const targets = targetsContainer.querySelectorAll('.target-entry');
        targets.forEach((target, index) => {
            const label = target.querySelector('label');
            if (label) {
                label.textContent = `Target ${index + 1}`;
            }
        });
        targetCounter = targets.length;
    };

    addTargetBtn.addEventListener('click', addNewTarget);

    // Add one target by default
    addNewTarget();
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?> 