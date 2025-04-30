<?php
/**
 * Report Data API Endpoint
 * 
 * Retrieves and formats data needed for PPTX report generation.
 * This endpoint provides JSON data to the client-side JavaScript
 * which then generates the PowerPoint file using PptxGenJS.
 * 
 * Only admin users are allowed to access this endpoint.
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/admins/index.php';
require_once '../includes/status_helpers.php';

// Verify user is admin
if (!is_admin()) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin users can generate reports.']);
    exit;
}

// Get parameters from request
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 1; // Default to Forestry (sector_id 1)

// If no period_id provided, use current reporting period
if (!$period_id) {
    $current_period = get_current_reporting_period();
    $period_id = $current_period['period_id'] ?? null;
}

// Validate period_id
if (!$period_id) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid or missing reporting period']);
    exit;
}

// Get reporting period information
$period = get_reporting_period($period_id);
if (!$period) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Reporting period not found']);
    exit;
}

// Get sector information
$sector_query = "SELECT * FROM sectors WHERE sector_id = ?";
$stmt = $conn->prepare($sector_query);
$stmt->bind_param("i", $sector_id);
$stmt->execute();
$sector_result = $stmt->get_result();

if ($sector_result->num_rows === 0) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Sector not found']);
    exit;
}

$sector = $sector_result->fetch_assoc();

// Format the quarter/year string (e.g., "Q4 2024")
$quarter = "Q" . $period['quarter'] . " " . $period['year'];

// --- 1. Get Sector Leads (agencies with this sector) ---
$sector_leads_query = "SELECT GROUP_CONCAT(agency_name SEPARATOR '; ') as sector_leads 
                      FROM users 
                      WHERE sector_id = ? AND role = 'agency' AND is_active = 1";
$stmt = $conn->prepare($sector_leads_query);
$stmt->bind_param("i", $sector_id);
$stmt->execute();
$sector_leads_result = $stmt->get_result();
$sector_leads_row = $sector_leads_result->fetch_assoc();
$sector_leads = $sector_leads_row['sector_leads'] ?: 'No assigned agencies';

// Add department name before sector leads (e.g., "MUDENR; Sector Leads: ...")
$dept_prefix = '';
if ($sector_id == 1) { // Forestry
    $dept_prefix = 'MUDENR; Sector Leads: ';
} elseif ($sector_id == 2) { // Land
    $dept_prefix = 'MIPD; Sector Leads: ';
} elseif ($sector_id == 3) { // Environment
    $dept_prefix = 'MUDENR; Sector Leads: ';
} elseif ($sector_id == 4) { // Natural Resources
    $dept_prefix = 'MNRD; Sector Leads: ';
} elseif ($sector_id == 5) { // Urban Development
    $dept_prefix = 'MIPD; Sector Leads: ';
}

$sector_leads = $dept_prefix . $sector_leads;

// --- 2. Get Programs for this Sector ---
$programs_query = "SELECT p.program_id, p.program_name, 
                    ps.status, ps.content_json
                  FROM programs p
                  LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ? AND ps.is_draft = 0
                  WHERE p.sector_id = ?
                  ORDER BY p.program_name";
$stmt = $conn->prepare($programs_query);
$stmt->bind_param("ii", $period_id, $sector_id);
$stmt->execute();
$programs_result = $stmt->get_result();

$programs = [];
while ($program = $programs_result->fetch_assoc()) {
    // Extract target from content_json
    $content = json_decode($program['content_json'] ?? '{}', true);
    $target = $content['target'] ?? 'No target set';
    $status_text = $content['status_text'] ?? 'No status update available';
    
    // Map status to color (green, yellow, red, grey)
    $status_color = 'grey'; // Default for not reported
    
    if (isset($program['status'])) {
        switch ($program['status']) {
            case 'on-track':
            case 'on-track-yearly':
            case 'target-achieved':
                $status_color = 'green';
                break;
            case 'delayed':
            case 'minor-issues':
                $status_color = 'yellow';
                break;
            case 'severe-delay':
            case 'major-issues':
            case 'at-risk':
                $status_color = 'red';
                break;
            case 'not-started':
            default:
                $status_color = 'grey';
        }
    }
    
    $programs[] = [
        'name' => $program['program_name'],
        'target' => $target,
        'rating' => $status_color,
        'status' => $status_text
    ];
}

// --- 3. Get Timber Export Value Chart Data from sector_metrics_data --- 
// Determine the year from the current period
$current_year = $period['year'];
$previous_year = $current_year - 1;

// Get monthly labels
$monthly_labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

// --- Use mock data for simplicity and to avoid database issues in the demo ---
$prev_year_data = [
    230000000, 245000000, 260000000, 252000000, 278000000, 265000000, 
    270000000, 285000000, 260000000, 245000000, 235000000, 270000000
];

$current_year_data = [
    245000000, 265000000, 285000000, 272000000, 295000000, 290000000, 
    300000000, 310000000, 285000000, 0, 0, 0 // Zeros for future months
];

// Calculate totals
$total_prev_year = array_sum($prev_year_data);
$total_current_year = array_sum($current_year_data);

// Format totals with comma for thousands and RM prefix
$formatted_prev_total = 'RM' . number_format($total_prev_year, 2);
$formatted_current_total = 'RM' . number_format($total_current_year, 2);

// --- 4. TPA/Biodiversity KPI --- 
// Use mock data for demo
$tpa_value = 32;
$tpa_description = "On-going conservation programs across protected areas";

// --- 5. Certification KPIs ---
// Mock data for forest certification percentages
$certification_data = [
    'fmu_percent' => 78,
    'fmu_value' => '2,327,221 ha',
    'fpmu_percent' => 69,
    'fpmu_value' => '122,800 ha'
];

// --- 6. Degraded Area Restored Chart ---
// Use mock data for demo
$area_restored_data = [
    'data2022' => [1200, 1500, 2000, 1800, 2200, 1900, 2100, 2300, 2000, 1700, 1500, 1800],
    'data2023' => [1500, 1800, 2200, 2000, 2500, 2300, 2400, 2600, 2300, 2000, 1900, 2100],
    'data2024' => [1700, 2000, 2500, 2300, 2800, 2600, 2700, 2900, 2500, 0, 0, 0], // Zeros for future months
    'total2024' => '21,028.90 ha' // Formatted total
];

// --- 7. World Recognition KPIs ---
$recognition_data = [
    'sdgp_percent' => 50,
    'niah_percent' => 100
];

// --- 8. Generate Draft Date ---
$draft_date = 'DRAFT ' . date('j M Y');

// --- Assemble final data structure ---
$report_data = [
    'reportTitle' => strtoupper($sector['sector_name']),
    'sectorLeads' => $sector_leads,
    'quarter' => $quarter,
    'projects' => $programs,
    'timberExportChart' => [
        'labels' => $monthly_labels,
        'data2023' => $prev_year_data,
        'data2024' => $current_year_data,
        'total2023' => $formatted_prev_total,
        'total2024' => $formatted_current_total
    ],
    'kpiTPA' => [
        'value' => $tpa_value,
        'description' => $tpa_description
    ],
    'kpiCertification' => $certification_data,
    'areaRestoredChart' => [
        'labels' => $monthly_labels,
        'data2022' => $area_restored_data['data2022'],
        'data2023' => $area_restored_data['data2023'], 
        'data2024' => $area_restored_data['data2024'],
        'total2024' => $area_restored_data['total2024']
    ],
    'kpiRecognition' => $recognition_data,
    'draftDate' => $draft_date
];

// Clear all previous output
ob_end_clean();

// Set content type header and output JSON
header('Content-Type: application/json');
echo json_encode($report_data, JSON_PRETTY_PRINT);
exit;
?>