<?php
/**
 * Recent Reports Table AJAX View
 * 
 * This file is called via AJAX to render just the reports table HTML
 * without requiring a full page reload.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';

// Check if the user is logged in
if (!is_logged_in()) {
    http_response_code(403); // Forbidden
    echo "You must be logged in to view this content.";
    exit;
}

// Fetch reports from the database
$reports = [];
$user_id = $_SESSION['user_id'];
$user_role = get_user_role();

// Build the query based on the user's role
$query = "SELECT r.report_id, r.report_name, r.description, r.pptx_path, r.generated_at, r.is_public, 
                 rp.year, rp.quarter, u.username 
          FROM reports r
          JOIN reporting_periods rp ON r.period_id = rp.period_id
          JOIN users u ON r.generated_by = u.user_id";

$conditions = [];
$params = [];
$types = "";

// Add user role-based visibility restrictions
if ($user_role === 'agency') {
    // Agency users see their reports or public reports
    $conditions[] = "(r.generated_by = ? OR r.is_public = 1)";
    $params[] = $user_id;
    $types .= "i";
} elseif ($user_role !== 'admin') {
    // Non-admin, non-agency users
    $conditions[] = "r.is_public = 1";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order by most recent first
$query .= " ORDER BY r.generated_at DESC LIMIT 20";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
$stmt->close();
?>

<?php if (count($reports) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover reports-table">
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Period</th>
                    <th>Generated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['report_name']); ?></td>
                        <td>Q<?php echo $report['quarter']; ?> <?php echo $report['year']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($report['generated_at'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="../../download.php?type=report&file=<?php echo $report['pptx_path']; ?>" class="btn btn-sm btn-outline-secondary action-btn action-btn-download" title="Download Report">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary action-btn action-btn-delete" title="Delete Report" 
                                        data-bs-toggle="modal" data-bs-target="#deleteReportModal" 
                                        data-report-id="<?php echo $report['report_id']; ?>" 
                                        data-report-name="<?php echo htmlspecialchars($report['report_name']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="reports-empty-state">
        <p class="text-muted">No reports generated yet.</p>
    </div>
<?php endif; ?>
