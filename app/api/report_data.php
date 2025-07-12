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
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/db_names_helper.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php'; // For new outcomes table functions

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
$selected_program_ids_raw = isset($_GET['selected_program_ids']) ? $_GET['selected_program_ids'] : null;
$program_orders_raw = isset($_GET['program_orders']) ? $_GET['program_orders'] : null;
$selected_targets_raw = isset($_GET['selected_targets']) ? $_GET['selected_targets'] : null;

// Handle half-yearly period logic
$period_ids = [$period_id];

// Get period details to check if it's half-yearly
    $period_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods WHERE period_id = ?";
$stmt = $conn->prepare($period_query);
$stmt->bind_param("i", $period_id);
$stmt->execute();
$period_result = $stmt->get_result();
$period_data = $period_result->fetch_assoc();

// Check if this is a half-yearly period based on period_type and period_number
if ($period_data && isset($period_data['period_type']) && isset($period_data['period_number'])) {
    $period_type = $period_data['period_type'];
    $period_number = (int)$period_data['period_number'];
    $year = $period_data['year'];
    
    if ($period_type == 'half' && $period_number == 1) { // Half Yearly 1 includes Q1 and Q2
        // Find all Q1 and Q2 periods for the same year
        $q1q2_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (1, 2)";
        $stmt = $conn->prepare($q1q2_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $q1q2_result = $stmt->get_result();
        
        $period_ids = [$period_id]; // Always include the original period
        while ($row = $q1q2_result->fetch_assoc()) {
            $period_ids[] = $row['period_id'];
        }
        
        error_log("Half Yearly 1 ($year) selected: Including period_ids " . implode(", ", $period_ids));
    } elseif ($period_type == 'half' && $period_number == 2) { // Half Yearly 2 includes Q3 and Q4
        // Find all Q3 and Q4 periods for the same year
        $q3q4_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (3, 4)";
        $stmt = $conn->prepare($q3q4_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $q3q4_result = $stmt->get_result();
        
        $period_ids = [$period_id]; // Always include the original period
        while ($row = $q3q4_result->fetch_assoc()) {
            $period_ids[] = $row['period_id'];
        }
        
        error_log("Half Yearly 2 ($year) selected: Including period_ids " . implode(", ", $period_ids));
    }
}

// Parse program orders if provided
$program_orders = [];
if ($program_orders_raw) {
    try {
        $program_orders = json_decode($program_orders_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error parsing program_orders JSON: " . json_last_error_msg());
            $program_orders = [];
        }
    } catch (Exception $e) {
        error_log("Exception parsing program_orders: " . $e->getMessage());
        $program_orders = [];
    }
}

// Parse selected targets if provided
$selected_targets = [];
if ($selected_targets_raw) {
    try {
        $selected_targets = json_decode($selected_targets_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error parsing selected_targets JSON: " . json_last_error_msg());
            $selected_targets = [];
        }
    } catch (Exception $e) {
        error_log("Exception parsing selected_targets: " . $e->getMessage());
        $selected_targets = [];
    }
}

// Add debug logging for parameters
$period_ids_str = implode(',', $period_ids);
error_log("API parameters - period_ids: {$period_ids_str}, sector_id: {$sector_id}");
error_log("Fixed duplicate submission query - using MAX(submission_id) for tie-breaking");
if (!empty($program_orders)) {
    error_log("Program orders provided: " . json_encode($program_orders));
}
if (!empty($selected_targets)) {
    error_log("Selected targets provided: " . json_encode($selected_targets));
}

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

// Remove all sector logic and references
$sector = [
    'sector_id' => 1,
    'sector_name' => 'Forestry Sector'
];

// Format the quarter/year string (e.g., "Q4 2024", "Half Yearly 1 2025")
$quarter = get_period_display_name($period);

// --- 1. Get Sector Leads (agencies with this sector, including focal agencies) ---
// Remove sector_leads and sector-based filtering from queries and outputs
$sector_leads_query = "SELECT GROUP_CONCAT(agency_name SEPARATOR '; ') as sector_leads 
                      FROM users 
                      WHERE role IN ('agency', 'focal') AND is_active = 1";
$stmt = $conn->prepare($sector_leads_query);
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
// Process selected program IDs if provided
$selected_program_ids = [];
$program_filter_condition = "p.sector_id = ?"; // Default filter by sector only

// Create the period IN clause for the two places where period_id is used
$period_in_clause = implode(',', array_fill(0, count($period_ids), '?'));

// Prepare the parameters for the query
$program_params = array_merge($period_ids, $period_ids); // period_ids appear twice in subquery
$program_params[] = $sector_id; // Add sector_id 
$program_param_types = str_repeat('i', count($period_ids) * 2) . 'i'; // period_ids (twice) + sector_id

if (!empty($selected_program_ids_raw)) {
    error_log("Selected program IDs raw: {$selected_program_ids_raw}");
    if (is_string($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', explode(',', $selected_program_ids_raw));
    } elseif (is_array($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', $selected_program_ids_raw);
    }
    $selected_program_ids = array_filter($selected_program_ids, function($id) { return $id > 0; });
    if (!empty($selected_program_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_program_ids), '?'));
        $program_filter_condition = "p.program_id IN ($placeholders)";
        $program_params = array_merge($period_ids, $period_ids, $selected_program_ids);
        $program_param_types = str_repeat('i', count($period_ids) * 2) . str_repeat('i', count($selected_program_ids));
        error_log("Filtering by " . count($selected_program_ids) . " selected programs");
    }
}

// Refactored: Fetch latest non-draft submission for each program and period, then fetch targets from program_targets
$programs_query = "
    SELECT p.program_id, p.program_name, p.rating, i.initiative_id, i.initiative_name,
           ps.submission_id, ps.period_id, rp.period_number, rp.period_type, rp.year
    FROM programs p
    LEFT JOIN (
        SELECT ps1.*
        FROM program_submissions ps1
        INNER JOIN (
            SELECT program_id, period_id, MAX(submission_date) as latest_date, MAX(submission_id) as latest_submission_id
            FROM program_submissions
            WHERE period_id IN ($period_in_clause) AND is_draft = 0
            GROUP BY program_id, period_id
        ) ps2 ON ps1.program_id = ps2.program_id 
             AND ps1.period_id = ps2.period_id
             AND ps1.submission_date = ps2.latest_date 
             AND ps1.submission_id = ps2.latest_submission_id
        WHERE ps1.period_id IN ($period_in_clause) AND ps1.is_draft = 0
    ) ps ON p.program_id = ps.program_id
    LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
    LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
    WHERE $program_filter_condition
    GROUP BY p.program_id, p.program_name, i.initiative_id, i.initiative_name, ps.submission_id, ps.period_id, rp.period_number, rp.period_type, rp.year
    ORDER BY p.program_name";

$stmt = $conn->prepare($programs_query);
if (!$stmt) {
    error_log("Failed to prepare statement: " . $conn->error);
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error preparing statement']);
    exit;
}
$stmt->bind_param($program_param_types, ...$program_params);
$stmt->execute();
$programs_result = $stmt->get_result();
$programs = [];
while ($program = $programs_result->fetch_assoc()) {
    $submission_id = $program['submission_id'];
    $targets = [];
    if ($submission_id) {
        // Fetch targets for this submission
        $target_stmt = $conn->prepare("SELECT target_id, target_number, target_description, status_indicator, status_description, remarks, start_date, end_date FROM program_targets WHERE submission_id = ? AND is_deleted = 0");
        $target_stmt->bind_param("i", $submission_id);
        $target_stmt->execute();
        $target_result = $target_stmt->get_result();
        while ($target = $target_result->fetch_assoc()) {
            $targets[] = $target;
        }
        $target_stmt->close();
    }
    $programs[] = [
        'program_id' => $program['program_id'],
        'program_name' => $program['program_name'],
        'rating' => $program['rating'] ?? 'not_started',
        'initiative_id' => $program['initiative_id'],
        'initiative_name' => $program['initiative_name'],
        'period_id' => $program['period_id'],
        'period_number' => $program['period_number'],
        'period_type' => $program['period_type'],
        'year' => $program['year'],
        'targets' => $targets
    ];
}

// Add debug logging
error_log("Fetching programs for sector_id: {$sector_id} and period_id: {$period_id}");

// Prepare custom order case statement if program orders are provided
$order_by_clause = "p.program_name"; // Default alphabetical order
if (!empty($program_orders) && !empty($selected_program_ids)) {
    $case_parts = [];
    $valid_orders = 0;
    
    // Validate program orders and create case statements
    foreach ($program_orders as $prog_id => $order) {
        $prog_id = (int)$prog_id;
        $order = (int)$order;
        
        if (in_array($prog_id, $selected_program_ids) && $prog_id > 0 && $order > 0) {
            $case_parts[] = "WHEN p.program_id = {$prog_id} THEN {$order}";
            $valid_orders++;
        }
    }
    
    if (!empty($case_parts)) {
        $order_by_clause = "CASE " . implode(" ", $case_parts) . " ELSE 999999 END, p.program_name";
        error_log("Using custom order by clause with {$valid_orders} valid program orders");
    } else {
        error_log("No valid program orders found, using default alphabetical order");
    }
} else {
    error_log("No program orders provided or no programs selected, using default alphabetical order");
}

// --- 3. Get Monthly Labels ---
$monthly_labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
$current_year = $period['year'];
$previous_year = $current_year - 1;
$year_before_previous = $current_year - 2;

// --- 4. Get Sector Outcomes from Database ---
// Fetch outcomes for this sector and period
$sector_outcomes = [];
$charts_data = [];

// Fetch all outcomes from the new outcomes table
$outcomes = get_all_outcomes();
$outcomes_by_code = [];
foreach ($outcomes as $outcome) {
    $outcomes_by_code[$outcome['code']] = $outcome;
}

// Add degraded area chart data using dynamic years
$degraded_area_chart_data_prepared = [
    'labels' => $monthly_labels,
    'years' => $degraded_area_years, // Add years array for frontend reference
    'units' => $degraded_area_units ?: 'Ha' // Default to 'Ha' if not found
];

// Add data and totals for each year dynamically
foreach ($degraded_area_years as $year) {
    $degraded_area_chart_data_prepared['data' . $year] = $degraded_area_data[$year];
    $degraded_area_chart_data_prepared['total' . $year] = array_sum($degraded_area_data[$year]);
}

// Default secondary chart data - still using placeholder data
$secondary_chart_data = [
    'labels' => $monthly_labels,
    'data' . $year_before_previous => array_fill(0, 12, 0),
    'data' . $previous_year => array_fill(0, 12, 0),
    'data' . $current_year => array_fill(0, 12, 0),
    'total' . $current_year => "0"
];

// Now query for other metrics data
$metrics_query = "SELECT * FROM sector_outcomes_data 
                  WHERE period_id = ? AND is_draft = 0";
$stmt = $conn->prepare($metrics_query);
$stmt->bind_param("i", $period_id);
$stmt->execute();
$metrics_result = $stmt->get_result();

// Prepare the main chart data with the timber export values we found
$main_chart_data = [
    'labels' => $monthly_labels,
    'data' . $previous_year => $timber_export_data[$previous_year],
    'data' . $current_year => $timber_export_data[$current_year],
    'total' . $previous_year => array_sum($timber_export_data[$previous_year]),
    'total' . $current_year => array_sum($timber_export_data[$current_year])
];

// Set the chart titles and values based on the sector
switch ($sector_id) {
    case 1: // Forestry
        $main_chart_title = "Timber Export Value (RM)";
        $secondary_chart_title = "Total Degraded Area Restored (Ha)";
        break;
            
    case 2: // Land
        $main_chart_title = "Land Development (Ha)";
        $secondary_chart_title = "Land Title Applications Processed";
        break;
            
    case 3: // Environment
        $main_chart_title = "Air Quality Index";
        $secondary_chart_title = "Waste Management (Tons)";
        break;
            
    case 4: // Natural Resources
        $main_chart_title = "Resource Extraction (Units)";
        $secondary_chart_title = "Sustainable Resource Management (%)";
        break;
            
    case 5: // Urban Development
        $main_chart_title = "Urban Growth (km²)";
        $secondary_chart_title = "Housing Development (Units)";
        break;
}

// Store default charts in the arrays
$charts_data['main_chart'] = [
    'type' => 'chart',
    'key' => 'main_chart',
    'title' => $main_chart_title,
    'data' => $main_chart_data
];

$charts_data['degraded_area_chart'] = [
    'type' => 'chart',
    'key' => 'degraded_area_chart',
    'title' => 'Total Degraded Area (' . ($degraded_area_units ?: 'Ha') . ')',
    'data' => $degraded_area_chart_data_prepared
];

$charts_data['secondary_chart'] = [
    'type' => 'chart',
    'key' => 'secondary_chart',
];

// --- 5. Generate Draft Date ---
$draft_date = 'DRAFT ' . date('j M Y');

// --- Assemble final data structure ---
$report_data = [
    'reportTitle' => strtoupper($sector['sector_name']),
    'sectorLeads' => $sector_leads,
    'quarter' => $quarter,
    'projects' => $programs,
    'programs' => $programs, // Add programs with the correct key name that the frontend expects
    'charts' => $charts_data,    'draftDate' => $draft_date,
    'sector_id' => $sector_id,  // Include sector_id for client-side use
    'outcomes' => $outcomes_by_code,  // This is the primary source for KPIs now
];

// Remove any undefined variables reference that could cause PHP warnings
// Clear all previous output
ob_end_clean();

// Set content type header and output JSON
header('Content-Type: application/json');
echo json_encode($report_data, JSON_PRETTY_PRINT);
exit;
?>