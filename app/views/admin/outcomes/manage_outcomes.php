<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes - Enhanced to follow agency side structure.
*/

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php'; // Added for get_all_outcomes()

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome creation setting
require_once ROOT_PATH . 'app/lib/admins/settings.php';
$allow_outcome_creation = get_outcome_creation_setting();

// Set page title
$pageTitle = 'Manage Outcomes';

// Get all outcomes using the new outcomes table
$outcomes = get_all_outcomes();

// No more important/regular separation for fixed outcomes

// Get current reporting period for display purposes
$current_period = get_current_reporting_period();

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Manage Outcomes',
    'subtitle' => 'Admin interface to manage outcomes across all sectors',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Refresh',
            'url' => '#',
            'id' => 'refreshPage',
            'class' => 'btn-light',
            'icon' => 'fas fa-sync-alt'
        ]
    ]
];

// Add create button if outcome creation is allowed
if ($allow_outcome_creation) {
    $header_config['actions'][] = [
        'text' => 'Create New Outcome',
        'url' => APP_URL . '/app/views/admin/outcomes/create_outcome_flexible.php',
        'class' => 'btn-primary',
        'icon' => 'fas fa-plus-circle',
        'id' => 'createMetricBtn'
    ];
}

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

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <?php if (!$allow_outcome_creation): ?>
    <!-- Outcome Creation Notice -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Important:</strong> 
        Creation of new outcomes has been disabled by the administrator. This ensures outcomes remain consistent across reporting periods.
        Outcome history is now tracked, and existing outcomes cannot be deleted to maintain data integrity.
        <a href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php" class="alert-link">
            <i class="fas fa-cog ms-1"></i> Manage settings
        </a>
    </div>
    <?php endif; ?>

    <!-- Outcomes Section -->
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
            <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
            <div id="successContainer" class="alert alert-success" style="display: none;"></div>
        </div>
    </div>

    <!-- Guidelines Section -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-info-circle me-2"></i>Admin Guidelines for Outcomes Management
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-users me-2 text-primary"></i>Cross-Sector Management</h6>
                        <p class="small mb-1">As an admin, you can view and manage outcomes across all sectors and agencies.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">All outcomes from every sector are displayed here.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-edit me-2 text-success"></i>Direct Editing</h6>
                        <p class="small mb-1">You can directly edit outcome data and structure for any agency.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Use this to help agencies correct or update their outcomes.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-history me-2 text-info"></i>Change Tracking</h6>
                        <p class="small mb-1">View comprehensive history of changes made to any outcome.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Click the history icon to see all modifications.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-cog me-2 text-danger"></i>System Settings</h6>
                        <p class="small mb-1">Control system-wide outcome creation permissions and other settings.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Manage global outcome policies from system settings.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Outcome Detail Modal -->
<div class="modal fade" id="editOutcomeDetailModal" tabindex="-1" aria-labelledby="editOutcomeDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editOutcomeDetailModalLabel">Edit Outcome Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editOutcomeDetailForm">
          <div id="editItemsContainer"></div>
          <button type="button" class="btn btn-outline-secondary mt-2" id="addItemBtn">Add Item</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveOutcomeDetailBtn">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refreshPage');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => window.location.reload());
        }

        // Important Outcomes editing functionality (from agency side)
        // This section is no longer relevant as outcomes are managed directly
        // and the modal is removed.
    });
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
