<?php
/**
 * Submit Outcomes
 * 
 * Interface for agency users to submit sector outcomes.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/outcomes.php'; // Use new backend function

if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$pageTitle = 'Manage Outcomes';

$current_period = get_current_reporting_period();
$show_form = $current_period && $current_period['status'] === 'open';

$message = '';
$message_type = '';

// Get all outcomes using the new outcomes table
$outcomes = get_all_outcomes();

// Remove calculation of $important_outcomes and $regular_outcomes

$additionalScripts = [];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Manage Outcomes',
    'subtitle' => 'View and manage all outcomes for this reporting period',
    'variant' => 'green',
    'actions' => []
];

// Add period badges to actions
if ($current_period) {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success"><i class="fas fa-calendar-alt me-1"></i> Q' . $current_period['quarter'] . '-' . $current_period['year'] . '</span>'
    ];
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-success ms-2"><i class="fas fa-clock me-1"></i> Ends: ' . date('M j, Y', strtotime($current_period['end_date'])) . '</span>'
    ];
} else {
    $header_config['actions'][] = [
        'html' => '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i> No Active Reporting Period</span>'
    ];
}

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<?php
// Remove detailsArray and outcomes_details logic
?>
<?php if (!$show_form): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= $error_message ?? 'No active reporting period is currently open.' ?> Please try again when a reporting period is active.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2"></i>
            <div>
                <strong>Current Reporting Period:</strong> 
                Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?> 
                (<?= date('d M Y', strtotime($current_period['start_date'])) ?> - 
                <?= date('d M Y', strtotime($current_period['end_date'])) ?>)
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-list-alt me-2"></i>Outcomes
            </h5>
            <span class="badge bg-light text-primary">
                <?= count($outcomes) ?> Items
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($outcomes)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Last Updated</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($outcomes as $outcome): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($outcome['title']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($outcome['description']) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y g:i A', strtotime($outcome['updated_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="view_outcome.php?id=<?= $outcome['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_outcome.php?id=<?= $outcome['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Edit Outcome">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No outcomes found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title m-0">
            <i class="fas fa-info-circle me-2"></i>Guidelines for Outcomes
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-table me-2 text-primary"></i>Outcomes Tables</h6>
                    <p class="small mb-1"> a table for each related set of outcomes that share the same reporting frequency.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Production Volume"</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-columns me-2 text-success"></i>Outcomes Columns</h6>
                    <p class="small mb-1">Each column represents a specific outcomes with its own unit of measurement.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Exports", "Forest Coverage"</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-ruler me-2 text-info"></i>Measurement Units</h6>
                    <p class="small mb-1">Specify the appropriate unit for each outcome. Units can be set individually or for all columns.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Examples: RM, Ha, %, tons, m³</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="h-100 p-3 border rounded bg-light-subtle">
                    <h6><i class="fas fa-chart-line me-2 text-danger"></i>Data Formatting</h6>
                    <p class="small mb-1">Enter numbers directly without commas or symbols. Use consistent decimal places.</p>
                    <div class="alert alert-light py-2 px-3 mb-0 small">Correct: 1250.50 <br>Incorrect: 1,250.50 RM</div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-primary mt-4 mb-0">
            <div class="d-flex">
                <div class="me-3 fs-4">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h6 class="alert-heading">Tips for Effective Metrics</h6>
                    <ul class="mb-0 ps-3">
                        <li>Use clear, descriptive names for tables and metrics</li>
                        <li>Ensure consistent units across similar metrics</li>
                        <li>Review your data before submission for accuracy</li>
                        <li>For metrics with multiple units, use the "Set All Units" button for consistency</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?>