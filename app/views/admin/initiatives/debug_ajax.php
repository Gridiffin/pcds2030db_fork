<?php
/**
 * Debug version of the AJAX part of manage_initiatives.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';

// Simulate admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h1>Debug AJAX Response</h1>";

// Handle AJAX table request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$is_active = isset($_GET['is_active']) && $_GET['is_active'] !== '' ? intval($_GET['is_active']) : null;

echo "<p><strong>Debug Info:</strong></p>";
echo "<p>Search: '" . htmlspecialchars($search) . "'</p>";
echo "<p>Is Active: " . ($is_active !== null ? $is_active : 'null') . "</p>";
echo "<p>Is Admin: " . (is_admin() ? 'Yes' : 'No') . "</p>";

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if ($is_active !== null) {
    $filters['is_active'] = $is_active;
}

echo "<p>Filters: " . json_encode($filters) . "</p>";

$initiatives = get_all_initiatives($filters);

echo "<p>Found " . count($initiatives) . " initiatives</p>";

if (!empty($initiatives)) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>Programs</th><th>Created By</th></tr>";
    foreach ($initiatives as $initiative) {
        echo "<tr>";
        echo "<td>" . $initiative['initiative_id'] . "</td>";
        echo "<td>" . htmlspecialchars($initiative['initiative_name']) . "</td>";
        echo "<td>" . ($initiative['is_active'] ? 'Active' : 'Inactive') . "</td>";
        echo "<td>" . $initiative['program_count'] . "</td>";
        echo "<td>" . htmlspecialchars($initiative['created_by_username'] ?? 'Unknown') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No initiatives found!</p>";
}

// Now test the actual HTML output
echo "<hr>";
echo "<h2>Actual HTML Output:</h2>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
?>
<div class="card shadow-sm h-100 d-flex flex-column">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-lightbulb me-2"></i>Initiatives
            <span class="badge bg-primary ms-2"><?php echo count($initiatives); ?></span>
        </h5>
        <a href="create.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Initiative
        </a>
    </div>
    <div class="card-body p-0 flex-fill d-flex flex-column">
        <?php if (empty($initiatives)): ?>
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
                        <?php foreach ($initiatives as $initiative): ?>
                            <tr data-initiative-id="<?php echo $initiative['initiative_id']; ?>">
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($initiative['initiative_name']); ?></div>
                                    <?php if (!empty($initiative['initiative_description'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($initiative['initiative_description'], 0, 80)) . (strlen($initiative['initiative_description']) > 80 ? '...' : ''); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $initiative['initiative_number'] ? htmlspecialchars($initiative['initiative_number']) : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $initiative['program_count']; ?> programs</span>
                                </td>
                                <td>
                                    <?php if ($initiative['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($initiative['created_by_username'] ?? 'Unknown'); ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($initiative['created_at'])); ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="edit.php?id=<?php echo $initiative['initiative_id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-<?php echo $initiative['is_active'] ? 'warning' : 'success'; ?> btn-toggle-status" 
                                                data-initiative-id="<?php echo $initiative['initiative_id']; ?>"
                                                data-current-status="<?php echo $initiative['is_active']; ?>"
                                                title="<?php echo $initiative['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-<?php echo $initiative['is_active'] ? 'pause' : 'play'; ?>"></i>
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
echo "</div>";
?>
