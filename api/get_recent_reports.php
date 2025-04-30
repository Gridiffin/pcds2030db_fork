<?php
/**
 * Get Recent Reports API Endpoint
 * 
 * Retrieves recent reports for a specific reporting period.
 * Can be filtered by period ID.
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Get query parameters
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

// Build the query based on the user's role and parameters
$query = "SELECT r.*, u.username, u.first_name, u.last_name, s.sector_name, 
          CASE 
            WHEN r.pptx_path IS NOT NULL THEN 'presentation'
            WHEN r.pdf_path IS NOT NULL THEN 'document'
            ELSE 'unknown'
          END as report_type
          FROM reports r
          LEFT JOIN users u ON r.generated_by = u.user_id
          LEFT JOIN reporting_periods rp ON r.period_id = rp.period_id
          LEFT JOIN sector_reporting_periods srp ON rp.period_id = srp.period_id
          LEFT JOIN sectors s ON srp.sector_id = s.sector_id
          WHERE 1=1";

$params = [];
$types = "";

// Add period filter if provided
if ($period_id > 0) {
    $query .= " AND r.period_id = ?";
    $params[] = $period_id;
    $types .= "i";
}

// Add user role-based visibility restrictions
if (is_agency()) {
    // For agency users, show only reports for their own agency/sector or public reports
    $agency_id = $_SESSION['agency_id'];
    $query .= " AND (r.is_public = 1 OR s.agency_id = ?)";
    $params[] = $agency_id;
    $types .= "i";
} elseif (!is_admin()) {
    // For other non-admin users, show only public reports
    $query .= " AND r.is_public = 1";
}

// Order by most recent first
$query .= " ORDER BY r.generated_at DESC LIMIT 20";

// Prepare and execute query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch all reports
$reports = [];
while ($row = $result->fetch_assoc()) {
    // Determine file path
    $file_path = !empty($row['pptx_path']) ? $row['pptx_path'] : $row['pdf_path'];
    
    // Build report object
    $reports[] = [
        'report_id' => $row['report_id'],
        'report_name' => $row['report_name'],
        'description' => $row['description'],
        'file_path' => $file_path,
        'report_type' => $row['report_type'],
        'generated_by' => (!empty($row['first_name']) && !empty($row['last_name'])) 
                          ? $row['first_name'] . ' ' . $row['last_name'] 
                          : $row['username'],
        'generated_at' => $row['generated_at'],
        'sector_name' => $row['sector_name']
    ];
}

// Return JSON response
echo json_encode([
    'success' => true,
    'reports' => $reports
]);
exit;
?>