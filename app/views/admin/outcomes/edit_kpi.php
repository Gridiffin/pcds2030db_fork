<?php
/**
 * Edit KPI Outcome - Admin Version
 *
 * Dedicated page for editing KPI-type outcomes only.
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

if ($outcome['type'] !== 'kpi') {
    $_SESSION['error_message'] = 'This page is only for editing KPI outcomes.';
    header('Location: manage_outcomes.php');
    exit;
}

// --- POST handling for saving KPI outcome ---
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $data = $_POST['data'] ?? [];
    // Basic validation
    if ($title === '') {
        $error_message = 'Title is required.';
    } else {
        // Use existing code and type
        $code = $outcome['code'];
        $type = $outcome['type'];
        // Sanitize data array
        $clean_data = [];
        foreach ($data as $item) {
            $clean_data[] = [
                'description' => trim($item['description'] ?? ''),
                'value' => trim($item['value'] ?? ''),
                'unit' => trim($item['unit'] ?? ''),
                'extra' => trim($item['extra'] ?? ''),
            ];
        }
        $result = update_outcome_full($outcome_id, $code, $type, $title, $description, $clean_data);
        if ($result) {
            $success_message = 'KPI outcome updated successfully.';
            // Refresh outcome data
            $outcome = get_outcome_by_id($outcome_id);
        } else {
            $error_message = 'Failed to update KPI outcome. Please try again.';
        }
    }
}

$pageTitle = 'Edit KPI Outcome';
$header_config = [
    'title' => 'Edit KPI Outcome',
    'subtitle' => 'Update KPI details and data',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'view_outcome.php?id=' . $outcome_id,
            'text' => 'View KPI',
            'icon' => 'fas fa-eye',
            'class' => 'btn-secondary'
        ]
    ]
];
require_once '../../layouts/header.php';
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"> <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?> </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"> <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_message) ?> </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Edit KPI Outcome</h5>
            <p class="text-muted mb-0 small">Outcome ID: <?= $outcome_id ?> | Code: <?= htmlspecialchars($outcome['code']) ?></p>
        </div>
        <div class="card-body">
            <form id="editKpiForm" method="post" action="">
                <div class="mb-3">
                    <label for="kpiTitleInput" class="form-label">KPI Title</label>
                    <input type="text" class="form-control" id="kpiTitleInput" name="title" required value="<?= htmlspecialchars($outcome['title']) ?>" />
                </div>
                <div class="mb-3">
                    <label for="kpiDescriptionInput" class="form-label">KPI Description</label>
                    <textarea class="form-control" id="kpiDescriptionInput" name="description" rows="3"><?= htmlspecialchars($outcome['description']) ?></textarea>
                </div>
                <!-- KPI Data Table Section -->
                <?php $kpi_data = is_array($outcome['data']) ? $outcome['data'] : json_decode($outcome['data'], true); ?>
                <?php if (!empty($kpi_data)): ?>
                    <?php foreach ($kpi_data as $idx => $item): ?>
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][description]" value="<?= htmlspecialchars($item['description'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Value</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][value]" value="<?= htmlspecialchars($item['value'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][unit]" value="<?= htmlspecialchars($item['unit'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Extra</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][extra]" value="<?= htmlspecialchars($item['extra'] ?? '') ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning text-center my-4">
                        <i class="fas fa-exclamation-circle me-2"></i> No KPI data available for this outcome.
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-success mt-3">Save Changes</button>
                <a href="manage_outcomes.php" class="btn btn-secondary mt-3 ms-2">Done</a>
            </form>
        </div>
    </div>
</div>

<!-- Placeholder for bundled assets -->
<link rel="stylesheet" href="/dist/js/edit_kpi.bundle.css">
<script src="/dist/js/edit_kpi.bundle.js"></script>

<?php require_once dirname(__DIR__, 2) . '/layouts/footer.php'; ?> 