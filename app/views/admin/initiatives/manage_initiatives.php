<?php
/**
 * Manage Initiatives Page
 * 
 * Admin interface for managing initiatives.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';
require_once ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Initiatives';

// Handle AJAX table request
if (isset($_GET['ajax_table']) && $_GET['ajax_table'] == '1') {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $is_active = isset($_GET['is_active']) && $_GET['is_active'] !== '' ? intval($_GET['is_active']) : null;
    
    $filters = [];
    if (!empty($search)) {
        $filters['search'] = $search;
    }
    if ($is_active !== null) {
        $filters['is_active'] = $is_active;
    }
    
    $initiatives = get_all_initiatives($filters);
    
    // Load config and extract column names
    $config = include __DIR__ . '/../../../config/db_names.php';
    $initiative_id_col = $config['columns']['initiatives']['id'];
    $initiative_name_col = $config['columns']['initiatives']['name'];
    $initiative_description_col = $config['columns']['initiatives']['description'];
    $initiative_number_col = $config['columns']['initiatives']['number'];
    $is_active_col = $config['columns']['initiatives']['is_active'];
    $created_at_col = $config['columns']['initiatives']['created_at'];
    
    ?>    <div class="card shadow-sm h-100 d-flex flex-column">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-lightbulb me-2"></i>Initiatives
                <span class="badge bg-primary ms-2"><?php echo count($initiatives); ?></span>
            </h5>
            <a href="create.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Initiative
            </a>
        </div>        <div class="card-body p-0 flex-fill d-flex flex-column"><?php if (empty($initiatives)): ?>
                <div class="text-center py-5 flex-fill d-flex flex-column justify-content-center" style="min-height: 60vh;">
                    <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No initiatives found</h5>
                    <p class="text-muted">Get started by creating your first initiative.</p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Initiative
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Initiative Name</th>
                                <th>Number</th>
                                <th>Programs</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($initiatives as $initiative): ?>
                                <tr data-initiative-id="<?php echo $initiative[$initiative_id_col]; ?>">
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($initiative[$initiative_name_col] ?? ''); ?></div>
                                        <?php if (!empty($initiative[$initiative_description_col])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($initiative[$initiative_description_col], 0, 80)) . (strlen($initiative[$initiative_description_col]) > 80 ? '...' : ''); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($initiative[$initiative_number_col]) ? htmlspecialchars($initiative[$initiative_number_col]) : '<span class="text-muted">—</span>'; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $initiative['program_count']; ?> programs</span>
                                    </td>
                                    <td>
                                        <?php if (!empty($initiative[$is_active_col])): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($initiative['created_by_username'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo !empty($initiative[$created_at_col]) ? date('M j, Y', strtotime($initiative[$created_at_col])) : ''; ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view_initiative.php?id=<?php echo $initiative[$initiative_id_col]; ?>" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $initiative[$initiative_id_col]; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-<?php echo !empty($initiative[$is_active_col]) ? 'warning' : 'success'; ?> btn-toggle-status" 
                                                    data-initiative-id="<?php echo $initiative[$initiative_id_col]; ?>"
                                                    data-current-status="<?php echo $initiative[$is_active_col]; ?>"
                                                    title="<?php echo !empty($initiative[$is_active_col]) ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="fas fa-<?php echo !empty($initiative[$is_active_col]) ? 'pause' : 'play'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    exit;
}

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Manage Initiatives',
    'subtitle' => 'Create and manage strategic initiatives',
    'variant' => 'green'
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>            <main class="flex-fill d-flex flex-column" style="min-height: calc(100vh - 200px);">
                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search Initiatives</label>
                                <input type="text" class="form-control" id="search" placeholder="Search by name, number, or description...">
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                <!-- Initiatives Table Container -->
                <div id="initiativesTableContainer" class="flex-fill" style="min-height: 400px;">
                    <!-- Content loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </main>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle text-success' : 'exclamation-triangle text-danger'; ?> me-1"></i>
                    <?php echo ucfirst($_SESSION['message_type'] ?? 'info'); ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
        </div>
    </div>
    <?php 
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load initial table
    loadInitiativesTable();
    
    // Search functionality
    let searchTimeout;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadInitiativesTable, 300);
    });
    
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', loadInitiativesTable);
    
    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('search').value = '';
        document.getElementById('statusFilter').value = '';
        loadInitiativesTable();
    });
    
    // Load initiatives table via AJAX
    function loadInitiativesTable() {
        const search = document.getElementById('search').value;
        const status = document.getElementById('statusFilter').value;
        
        const params = new URLSearchParams({
            ajax_table: '1',
            search: search,
            is_active: status
        });
        
        fetch('manage_initiatives.php?' + params.toString())
            .then(response => response.text())
            .then(html => {
                document.getElementById('initiativesTableContainer').innerHTML = html;
                attachEventListeners();
            })
            .catch(error => {
                console.error('Error loading table:', error);
                document.getElementById('initiativesTableContainer').innerHTML = 
                    '<div class="alert alert-danger">Error loading initiatives. Please refresh the page.</div>';
            });
    }
    
    // Attach event listeners to dynamic content
    function attachEventListeners() {
        // Toggle status buttons
        document.querySelectorAll('.btn-toggle-status').forEach(button => {
            button.addEventListener('click', function() {
                const initiativeId = this.dataset.initiativeId;
                const currentStatus = this.dataset.currentStatus;
                
                if (confirm(`Are you sure you want to ${currentStatus === '1' ? 'deactivate' : 'activate'} this initiative?`)) {
                    toggleInitiativeStatus(initiativeId);
                }
            });
        });
    }
    
    // Toggle initiative status
    function toggleInitiativeStatus(initiativeId) {
        fetch('<?php echo APP_URL; ?>/app/api/initiatives.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle_status',
                initiative_id: initiativeId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadInitiativesTable(); // Reload table
                showToast('Initiative status updated successfully', 'success');
            } else {
                showToast(data.error || 'Failed to update initiative status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating the initiative status', 'error');
        });
    }
    
    // Show toast notification
    function showToast(message, type) {
        // Simple toast implementation
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(toast);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
