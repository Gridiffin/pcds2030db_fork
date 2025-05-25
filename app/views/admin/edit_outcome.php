<?php
/**
 * Edit/Create Sector Outcome
 * 
 * Admin interface to edit or create sector-specific outcomes.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php'; // Contains get_outcome_data, get_all_sectors, etc.
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php'; // Contains is_admin
require_once PROJECT_ROOT_PATH . 'app/lib/admins/statistics.php'; // For get_sector_by_id

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Edit/Create Outcome';

// Function to log messages to browser console (optional, for debugging)
function console_log($message) {
    echo '<script>console.log(' . json_encode($message) . ');</script>';
}

// Initialize variables
$message = $_SESSION['success_message'] ?? '';
$message_type = 'success';
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'danger';
}
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : (isset($_POST['outcome_id']) ? intval($_POST['outcome_id']) : 0);
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : (isset($_POST['sector_id']) ? intval($_POST['sector_id']) : 0);
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : (isset($_POST['period_id']) ? intval($_POST['period_id']) : 0);

$outcome_data = null;
$table_name = '';
$data_json_structure = []; // For the metric-editor.js
$sector_name = '';
$reporting_periods = get_all_reporting_periods();
$current_reporting_period = get_current_reporting_period();

// If we have an outcome_id, get its data
if ($outcome_id > 0) {
    $outcome_data = get_outcome_data($outcome_id);
    if ($outcome_data) {
        $sector_id = $outcome_data['sector_id'];
        $period_id = $outcome_data['period_id'];
        $table_name = $outcome_data['table_name'];
        $sector_name = $outcome_data['sector_name']; // Assuming get_outcome_data returns this
        $data_json_structure = json_decode($outcome_data['data_json'], true);
        $pageTitle = 'Edit Outcome: ' . htmlspecialchars($table_name ?: 'ID ' . $outcome_id);
    } else {
        $_SESSION['error_message'] = "Outcome with ID {$outcome_id} not found.";
        header("Location: manage_outcomes.php");
        exit;
    }
} else {
    $pageTitle = 'Create New Outcome';
    // For new outcome, if sector_id is passed, get sector_name
    if ($sector_id > 0) {
        $sector_info = get_sector_by_id($sector_id); // Assuming this function exists
        if ($sector_info) {
            $sector_name = $sector_info['sector_name'];
        }
    }
    // Default to current reporting period if not set and creating new
    if (!$period_id && $current_reporting_period) {
        $period_id = $current_reporting_period['period_id'];
    }
}

// Handle form submission for creating/updating outcome metadata (table name, sector, period)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_outcome_metadata'])) {
    $new_table_name = trim($_POST['table_name']);
    $new_sector_id = intval($_POST['sector_id']);
    $new_period_id = intval($_POST['period_id']);

    if (empty($new_table_name)) {
        $message = "Outcome Name (Table Name) cannot be empty.";
        $message_type = "danger";
    } elseif ($new_sector_id <= 0) {
        $message = "Please select a valid Sector.";
        $message_type = "danger";
    } elseif ($new_period_id <= 0) {
        $message = "Please select a valid Reporting Period.";
        $message_type = "danger";
    } else {
        if ($outcome_id > 0) { // Update existing outcome metadata
            $update_query = "UPDATE sector_outcomes_data SET table_name = ?, sector_id = ?, period_id = ?, updated_at = NOW() WHERE outcome_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("siii", $new_table_name, $new_sector_id, $new_period_id, $outcome_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Outcome metadata updated successfully.";
                // Refresh data
                $table_name = $new_table_name;
                $sector_id = $new_sector_id;
                $period_id = $new_period_id;
                $outcome_data = get_outcome_data($outcome_id); // Re-fetch to get fresh data
                if($outcome_data) $data_json_structure = json_decode($outcome_data['data_json'], true);
            } else {
                $message = "Error updating outcome metadata: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else { // Create new outcome
            // Initialize empty data_json structure
            $initial_data_json = json_encode(['columns' => [], 'units' => [], 'data' => []]); 
            $insert_query = "INSERT INTO sector_outcomes_data (table_name, sector_id, period_id, data_json, created_at, updated_at, is_draft) VALUES (?, ?, ?, ?, NOW(), NOW(), 0)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("siis", $new_table_name, $new_sector_id, $new_period_id, $initial_data_json);
            if ($stmt->execute()) {
                $new_outcome_id = $conn->insert_id;
                $_SESSION['success_message'] = "New outcome created successfully. You can now define its structure.";
                header("Location: edit_outcome.php?outcome_id=" . $new_outcome_id);
                exit;
            } else {
                $message = "Error creating new outcome: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

$sectors = get_all_sectors();

// Include header
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/admin_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2 mb-0"><?php echo $pageTitle; ?></h1>
        <a href="manage_metrics.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Manage Outcomes
        </a>
    </div>

    <!-- Placeholder for JavaScript-driven messages -->
    <div id="outcome-editor-messages" style="display: none;"></div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_outcome.php<?php echo $outcome_id > 0 ? '?outcome_id='.$outcome_id : ''; ?>" class="mb-4">
        <input type="hidden" name="outcome_id" value="<?php echo $outcome_id; ?>">
        <div class="card admin-card">
            <div class="card-header">
                <h5 class="card-title m-0">Outcome Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="table_name" class="form-label">Outcome Name / Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="table_name" name="table_name" value="<?php echo htmlspecialchars($table_name); ?>" required>
                        <small class="form-text text-muted">This will be the title of the outcome table shown to users.</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sector_id" class="form-label">Sector <span class="text-danger">*</span></label>
                        <select class="form-select" id="sector_id" name="sector_id" required <?php echo $outcome_id > 0 && isset($outcome_data['is_submitted']) && $outcome_data['is_submitted'] ? 'disabled' : '' ?>>
                            <option value="">Select Sector</option>
                            <?php foreach ($sectors as $s): ?>
                                <option value="<?php echo $s['sector_id']; ?>" <?php echo ($sector_id == $s['sector_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['sector_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                         <?php if ($outcome_id > 0 && isset($outcome_data['is_submitted']) && $outcome_data['is_submitted']): ?>
                            <small class="form-text text-warning">Sector cannot be changed for submitted outcomes.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="period_id" class="form-label">Reporting Period <span class="text-danger">*</span></label>
                        <select class="form-select" id="period_id" name="period_id" required <?php echo $outcome_id > 0 && isset($outcome_data['is_submitted']) && $outcome_data['is_submitted'] ? 'disabled' : '' ?>>
                            <option value="">Select Period</option>
                            <?php foreach ($reporting_periods as $rp): ?>
                                <option value="<?php echo $rp['period_id']; ?>" <?php echo ($period_id == $rp['period_id']) ? 'selected' : ''; ?>>
                                    Q<?php echo $rp['quarter']; ?>-<?php echo $rp['year']; ?> (<?php echo $rp['status']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($outcome_id > 0 && isset($outcome_data['is_submitted']) && $outcome_data['is_submitted']): ?>
                            <small class="form-text text-warning">Period cannot be changed for submitted outcomes.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" name="save_outcome_metadata" class="btn btn-forest">
                    <i class="fas fa-save me-1"></i> <?php echo $outcome_id > 0 ? 'Save Changes' : 'Create Outcome & Proceed'; ?>
                </button>
            </div>
        </div>
    </form>

    <?php if ($outcome_id > 0 && $outcome_data): // Show structure editor only if outcome exists ?>
    <div class="card admin-card mt-4">
        <div class="card-header">
            <h5 class="card-title m-0">Outcome Structure Editor</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Define the columns (indicators/metrics) for this outcome. Agencies will fill data based on this structure.</p>
            <div id="metricEditorContainer">
                <!-- Metric editor will be initialized here by metric-editor.js -->
            </div>
        </div>
        <div class="card-footer text-end">
            <button id="saveMetricStructureBtn" class="btn btn-forest">
                <i class="fas fa-save me-1"></i> Save Outcome Structure
            </button>
        </div>
    </div>
    <?php elseif (!$outcome_id && $sector_id > 0 && $period_id > 0): ?>
        <div class="alert alert-info">Please save the outcome details first to define its structure.</div>
    <?php elseif (!$outcome_id && ($sector_id == 0 || $period_id == 0)) : ?>
         <div class="alert alert-warning">Please select a Sector and Reporting Period, then click "Create Outcome & Proceed" to define the outcome structure.</div>
    <?php endif; ?>

</div>

<?php 
// Pass data to JavaScript
$js_data = [
    'outcome_id' => $outcome_id,
    'table_name' => $table_name, // Current table name for the editor
    'data_json' => $data_json_structure, // Current structure
    'save_url' => APP_URL . '/app/api/save_outcome_json.php', // Specific API endpoint for saving outcome JSON
    'is_admin_view' => true
];
?>
<script>
    const initialMetricData = <?php echo json_encode($js_data); ?>;
</script>

<!-- Ensure outcome-editor.js is used if it's different from metric-editor.js -->
<script src="<?php echo APP_URL; ?>/assets/js/outcome-editor.js?v=<?php echo ASSET_VERSION; ?>"></script>

<?php 
require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php'; 
?>
