<?php
/**
 * Manage Program-Outcome Links
 * 
 * Admin interface for creating and managing links between programs and outcomes
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$pageTitle = 'Manage Program-Outcome Links';

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Program-Outcome Links',
    'subtitle' => 'Manage relationships between programs and outcomes for automated updates',
    'variant' => 'purple',
    'actions' => [
        [
            'url' => 'bulk_link_programs.php',
            'text' => 'Bulk Link Programs',
            'icon' => 'fas fa-link',
            'class' => 'btn-success'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-search me-2"></i>Search & Filter
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Program Search</label>
                    <input type="text" class="form-control" id="programSearch" placeholder="Search programs...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Outcome Search</label>
                    <input type="text" class="form-control" id="outcomeSearch" placeholder="Search outcomes...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter by Sector</label>
                    <select class="form-select" id="sectorFilter">
                        <option value="">All Sectors</option>
                        <!-- Populated via JavaScript -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs & Outcomes Grid -->
    <div class="row">
        <!-- Programs List -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks me-2"></i>Programs
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Program</th>
                                    <th>Agency</th>
                                    <th class="text-center">Links</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="programsList">
                                <!-- Populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outcomes List -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Outcomes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Outcome</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Links</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="outcomesList">
                                <!-- Populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Links Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-link me-2"></i>Active Program-Outcome Links
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="linksTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Agency</th>
                            <th>Outcome</th>
                            <th>Type</th>
                            <th>Created</th>
                            <th>Created By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="linksList">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Link Modal -->
<div class="modal fade" id="createLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-link me-2"></i>Create Program-Outcome Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createLinkForm">
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <select class="form-select" id="linkProgramId" required>
                            <option value="">Select a program...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Outcome</label>
                        <select class="form-select" id="linkOutcomeId" required>
                            <option value="">Select an outcome...</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        When the selected program reaches 'Completed' or 'Target Achieved' status, 
                        the linked outcome data will be automatically updated.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createLinkBtn">
                    <i class="fas fa-plus me-1"></i>Create Link
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Links Modal -->
<div class="modal fade" id="viewLinksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLinksTitle">
                    <i class="fas fa-eye me-2"></i>View Links
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewLinksContent">
                    <!-- Populated via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo APP_URL; ?>/assets/js/program_outcome_links.js"></script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.badge-link-count {
    background-color: #6f42c1;
}

.btn-link-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

#programsList tr, #outcomesList tr {
    cursor: pointer;
}

#programsList tr:hover, #outcomesList tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.selected-item {
    background-color: rgba(40, 167, 69, 0.2) !important;
}
</style>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
