<?php
/**
 * Outcome History Viewer
 * 
 * Admin interface to view the history of changes to an outcome.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Outcome Change History';

// Get the metric ID from URL parameter
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;

if ($metric_id <= 0) {
    $_SESSION['error_message'] = "Invalid outcome ID.";
    header('Location: manage_outcomes.php');
    exit;
}

// Get outcome data
$outcome_data = get_outcome_data($metric_id);
if (!$outcome_data) {
    $_SESSION['error_message'] = "Outcome with ID {$metric_id} not found.";
    header('Location: manage_outcomes.php');
    exit;
}

// Get outcome history
$history_records = get_outcome_history($metric_id);

// Message handling
$message = $_SESSION['success_message'] ?? '';
$message_type = 'success';
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'danger';
}
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'History: ' . htmlspecialchars($outcome_data['table_name']),
    'subtitle' => 'View historical changes and activity for this outcome',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'edit_outcome.php?metric_id=' . $metric_id,
            'text' => 'Edit Outcome',
            'icon' => 'fa-edit',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card admin-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Outcome Details</h5>
            <span class="badge <?php echo ($outcome_data['is_draft'] == 1) ? 'bg-warning text-dark' : 'bg-success'; ?>">
                <?php echo ($outcome_data['is_draft'] == 1) ? 'Draft' : 'Submitted'; ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <p class="mb-1 text-muted">Outcome Name:</p>
                    <h6><?php echo htmlspecialchars($outcome_data['table_name']); ?></h6>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Sector:</p>
                    <h6><?php echo htmlspecialchars($outcome_data['sector_name'] ?? 'N/A'); ?></h6>
                </div>                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Reporting Period:</p>
                    <h6><?php echo get_period_display_name($outcome_data); ?></h6>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Created At:</p>
                    <h6><?php echo date('M j, Y g:i A', strtotime($outcome_data['created_at'])); ?></h6>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Last Updated:</p>
                    <h6><?php echo date('M j, Y g:i A', strtotime($outcome_data['updated_at'])); ?></h6>
                </div>
                <?php if (!empty($outcome_data['submitted_at'])): ?>
                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Submitted At:</p>
                    <h6><?php echo date('M j, Y g:i A', strtotime($outcome_data['submitted_at'])); ?></h6>
                </div>
                <div class="col-md-3 mb-3">
                    <p class="mb-1 text-muted">Submitted By:</p>
                    <h6><?php echo htmlspecialchars($outcome_data['submitted_by_username'] ?? 'N/A'); ?></h6>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card admin-card">
        <div class="card-header">
            <h5 class="card-title m-0">Change History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($history_records)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No history records found for this outcome.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history_records as $record): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                                switch ($record['action_type']) {
                                                    case 'create': echo 'bg-success'; break;
                                                    case 'edit': echo 'bg-primary'; break;
                                                    case 'submit': echo 'bg-info'; break;
                                                    case 'unsubmit': echo 'bg-warning text-dark'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>
                                        ">
                                            <?php echo ucfirst($record['action_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($record['status'] === 'draft') ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                            <?php echo ucfirst($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['username'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($record['change_description']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info view-history-data" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#historyDataModal"
                                                data-history-id="<?php echo $record['history_id']; ?>"
                                                data-action-type="<?php echo $record['action_type']; ?>"
                                                data-history-date="<?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?>">
                                            <i class="fas fa-eye me-1"></i> View Data
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for viewing history data -->
<div class="modal fade" id="historyDataModal" tabindex="-1" aria-labelledby="historyDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyDataModalLabel">Outcome Data Snapshot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 id="historyDateDisplay" class="text-muted"></h6>
                </div>
                <div id="historyDataContent" class="border rounded p-3 bg-light" style="white-space: pre-wrap; font-family: monospace;">
                    <!-- JSON data will be displayed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle View Data buttons
    document.querySelectorAll('.view-history-data').forEach(button => {
        button.addEventListener('click', function() {
            const historyId = this.getAttribute('data-history-id');
            const actionType = this.getAttribute('data-action-type');
            const historyDate = this.getAttribute('data-history-date');
            
            // Update modal title based on action type
            const modalTitle = document.getElementById('historyDataModalLabel');
            modalTitle.textContent = 'Outcome Data - ' + actionType.charAt(0).toUpperCase() + actionType.slice(1) + ' Action';
            
            // Show history date in modal
            document.getElementById('historyDateDisplay').textContent = 'Snapshot from: ' + historyDate;
            
            // Fetch the history data
            fetch('<?php echo APP_URL; ?>/app/api/outcomes/get_outcome_history_data.php?history_id=' + historyId)
                .then(response => response.json())
                .then(data => {
                    const contentDiv = document.getElementById('historyDataContent');
                    if (data.success && data.data) {
                        try {
                            // Convert parsed JSON to pretty-printed JSON for display
                            const jsonObj = JSON.parse(data.data);
                            contentDiv.textContent = JSON.stringify(jsonObj, null, 2);
                        } catch (e) {
                            // If not valid JSON, show as is
                            contentDiv.textContent = data.data;
                        }
                    } else {
                        contentDiv.textContent = 'Error loading history data: ' + (data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    document.getElementById('historyDataContent').textContent = 'Network error: ' + error.message;
                });
        });
    });
});
</script>

<?php 
require_once ROOT_PATH . 'app/views/layouts/footer.php'; 
?>
