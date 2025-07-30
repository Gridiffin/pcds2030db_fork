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
$sector_id = 1; // Default to Forestry Sector only
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
error_log("Simplified query - no more multiple submissions per program/period");
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
$sector_leads_query = "SELECT GROUP_CONCAT(a.agency_name SEPARATOR '; ') as sector_leads 
                      FROM users u
                      JOIN agency a ON u.agency_id = a.agency_id
                      WHERE u.role IN ('agency', 'focal') AND u.is_active = 1";
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
$program_filter_condition = "1=1"; // Default filter - get all programs since sector_id no longer exists

// Create the period IN clause (period_ids used only once in the query)
$period_in_clause = implode(',', array_fill(0, count($period_ids), '?'));

// Prepare the parameters for the query
$program_params = $period_ids; // period_ids appear only once in the query
$program_param_types = str_repeat('i', count($period_ids)); // period_ids (once)

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
        $program_params = array_merge($period_ids, $selected_program_ids);
        $program_param_types = str_repeat('i', count($period_ids)) . str_repeat('i', count($selected_program_ids));
        error_log("Filtering by " . count($selected_program_ids) . " selected programs");
    }
}

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

// Simplified: Fetch submissions for each program and period (no more multiple submissions per program/period)
$programs_query = "
    SELECT p.program_id, p.program_name, p.rating, i.initiative_id, i.initiative_name,
           ps.submission_id, ps.period_id, rp.period_number, rp.period_type, rp.year
    FROM programs p
    LEFT JOIN program_submissions ps ON p.program_id = ps.program_id 
        AND ps.period_id IN ($period_in_clause) 
        AND ps.is_draft = 0
    LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
    LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
    WHERE $program_filter_condition
    ORDER BY $order_by_clause";

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
$programs_by_id = []; // Use associative array to consolidate by program_id

while ($program = $programs_result->fetch_assoc()) {
    $program_id = (int)$program['program_id'];
    $submission_id = $program['submission_id'];
    
    // Initialize program entry if not exists
    if (!isset($programs_by_id[$program_id])) {
        $programs_by_id[$program_id] = [
            'program_id' => $program_id,
            'program_name' => (string)($program['program_name'] ?? ''),
            'rating' => (string)($program['rating'] ?? 'not_started'),
            'initiative_id' => (int)($program['initiative_id'] ?? 0),
            'initiative_name' => (string)($program['initiative_name'] ?? ''),
            'period_id' => (int)($program['period_id'] ?? 0),
            'period_number' => (int)($program['period_number'] ?? 0),
            'period_type' => (string)($program['period_type'] ?? ''),
            'year' => (int)($program['year'] ?? 0),
            'targets' => [],
            'submissions' => [] // Track multiple submissions for half-yearly periods
        ];
    }
    
    // Fetch targets for this submission
    $targets = [];
    if ($submission_id) {
        $target_stmt = $conn->prepare("SELECT target_id, target_number, target_description, status_indicator, status_description, remarks, start_date, end_date FROM program_targets WHERE submission_id = ? AND is_deleted = 0");
        $target_stmt->bind_param("i", $submission_id);
        $target_stmt->execute();
        $target_result = $target_stmt->get_result();
        while ($target = $target_result->fetch_assoc()) {
            $targets[] = [
                'target_id' => (int)$target['target_id'],
                'target_number' => (string)($target['target_number'] ?? ''),
                'target_description' => (string)($target['target_description'] ?? ''),
                'status_indicator' => (string)($target['status_indicator'] ?? ''),
                'status_description' => (string)($target['status_description'] ?? ''),
                'remarks' => (string)($target['remarks'] ?? ''),
                'start_date' => (string)($target['start_date'] ?? ''),
                'end_date' => (string)($target['end_date'] ?? '')
            ];
        }
        $target_stmt->close();
        
        // Add submission info with its targets
        $programs_by_id[$program_id]['submissions'][] = [
            'submission_id' => (int)$submission_id,
            'period_id' => (int)($program['period_id'] ?? 0),
            'period_number' => (int)($program['period_number'] ?? 0),
            'period_type' => (string)($program['period_type'] ?? ''),
            'targets' => $targets
        ];
    }
    
    // Merge all targets from all submissions into the main targets array
    $programs_by_id[$program_id]['targets'] = array_merge($programs_by_id[$program_id]['targets'], $targets);
}

// Convert back to indexed array and remove duplicates
$programs = [];
foreach ($programs_by_id as $program) {
    // Remove the submissions array as it's only used for processing
    unset($program['submissions']);
    
    // Remove duplicate targets by target_id
    $unique_targets = [];
    $seen_target_ids = [];
    foreach ($program['targets'] as $target) {
        if (!in_array($target['target_id'], $seen_target_ids)) {
            $unique_targets[] = $target;
            $seen_target_ids[] = $target['target_id'];
        }
    }
    $program['targets'] = $unique_targets;
    
    $programs[] = $program;
}

error_log("Consolidated " . count($programs) . " unique programs from " . $programs_result->num_rows . " database rows");

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
$degraded_area_years = [$previous_year, $current_year]; // Default years
$degraded_area_units = 'Ha'; // Default units
$degraded_area_data = [
    $previous_year => array_fill(0, 12, 0),
    $current_year => array_fill(0, 12, 0)
];

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

// Now query for other metrics data - sector_outcomes_data table doesn't exist in current schema
// Using outcomes table instead
$metrics_result = null; // Placeholder for now

// Prepare the main chart data with the timber export values we found
$timber_export_data = [
    $previous_year => array_fill(0, 12, 0),
    $current_year => array_fill(0, 12, 0)
];

// Use actual outcome data for timber export chart
$main_chart_data = $outcomes_by_code['timber_export']['data'] ?? [];
if (!is_array($main_chart_data) || !isset($main_chart_data['columns']) || !isset($main_chart_data['rows'])) {
    $main_chart_data = [
        'columns' => [],
        'rows' => []
    ];
}
// Filter columns and rows for current and previous year only
if (isset($main_chart_data['columns']) && isset($main_chart_data['rows'])) {
    $years_to_keep = [$previous_year, $current_year];
    $years_to_keep_str = array_map('strval', $years_to_keep);
    // Filter columns
    $main_chart_data['columns'] = array_values(array_filter($main_chart_data['columns'], function($col) use ($years_to_keep_str) {
        return in_array($col, $years_to_keep_str);
    }));
    // Filter each row's values to only keep the selected years
    foreach ($main_chart_data['rows'] as &$row) {
        if (isset($row['values']) && is_array($row['values'])) {
            $row['values'] = array_intersect_key($row['values'], array_flip($main_chart_data['columns']));
        }
    }
    unset($row);
}
// Use actual outcome data for degraded area chart
$degraded_area_chart_data_prepared = $outcomes_by_code['degraded_area']['data'] ?? [];
if (!is_array($degraded_area_chart_data_prepared) || !isset($degraded_area_chart_data_prepared['columns']) || !isset($degraded_area_chart_data_prepared['rows'])) {
    $degraded_area_chart_data_prepared = [
        'columns' => [],
        'rows' => []
    ];
}
// Filter columns and rows for current and previous year only
if (isset($degraded_area_chart_data_prepared['columns']) && isset($degraded_area_chart_data_prepared['rows'])) {
    $years_to_keep = [$previous_year, $current_year];
    $years_to_keep_str = array_map('strval', $years_to_keep);
    // Filter columns
    $degraded_area_chart_data_prepared['columns'] = array_values(array_filter($degraded_area_chart_data_prepared['columns'], function($col) use ($years_to_keep_str) {
        return in_array($col, $years_to_keep_str);
    }));
    // Filter each row's values to only keep the selected years
    foreach ($degraded_area_chart_data_prepared['rows'] as &$row) {
        if (isset($row['values']) && is_array($row['values'])) {
            $row['values'] = array_intersect_key($row['values'], array_flip($degraded_area_chart_data_prepared['columns']));
        }
    }
    unset($row);
}

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

// Store charts in the arrays using real data
$charts_data['main_chart'] = [
    'type' => 'chart',
    'key' => 'main_chart',
    'title' => $main_chart_title,
    'data' => $main_chart_data
];

$charts_data['degraded_area_chart'] = [
    'type' => 'chart',
    'key' => 'degraded_area_chart',
    'title' => 'Total Degraded Area (Ha)',
    'data' => $degraded_area_chart_data_prepared
];

// --- 5. Generate Draft Date ---
$draft_date = 'DRAFT ' . date('j M Y');

// --- Assemble final data structure ---
// Build KPI boxes from outcomes_by_code
$kpi_boxes = [];
if (isset($outcomes_by_code['kpi_programs'])) {
    $kpi = $outcomes_by_code['kpi_programs'];
    $items = [];
    if (is_array($kpi['data'])) {
        foreach ($kpi['data'] as $entry) {
            $items[] = [
                'value' => $entry['value'],
                'description' => $entry['description']
            ];
        }
    }
    $kpi_boxes[] = [
        'name' => $kpi['title'],
        'layout_type' => 'simple',
        'items' => $items
    ];
}
if (isset($outcomes_by_code['kpi_certification'])) {
    $kpi = $outcomes_by_code['kpi_certification'];
    $items = [];
    if (is_array($kpi['data'])) {
        foreach ($kpi['data'] as $entry) {
            $items[] = [
                'value' => $entry['value'],
                'description' => $entry['description'],
                'extra' => isset($entry['extra']) ? $entry['extra'] : ''
            ];
        }
    }
    $kpi_boxes[] = [
        'name' => $kpi['title'],
        'layout_type' => 'comparison', // changed from 'simple' to 'comparison'
        'items' => $items
    ];
}
if (isset($outcomes_by_code['kpi_global'])) {
    $kpi = $outcomes_by_code['kpi_global'];
    $items = [];
    if (is_array($kpi['data'])) {
        foreach ($kpi['data'] as $entry) {
            $items[] = [
                'value' => $entry['value'],
                'description' => $entry['description']
            ];
        }
    }
    $kpi_boxes[] = [
        'name' => $kpi['title'],
        'layout_type' => 'comparison',
        'items' => $items
    ];
}

$report_data = [
    'reportTitle' => strtoupper($sector['sector_name']),
    'sectorLeads' => (string)$sector_leads,
    'quarter' => (string)$quarter,
    'projects' => $programs,
    'programs' => $programs, // Add programs with the correct key name that the frontend expects
    'charts' => $charts_data,
    'draftDate' => (string)$draft_date,
    'sector_id' => (int)$sector_id,  // Include sector_id for client-side use
    'outcomes' => $outcomes_by_code,  // This is the primary source for KPIs now
    'kpi_boxes' => $kpi_boxes // New structure for frontend/styler
];

// Remove any undefined variables reference that could cause PHP warnings
// Clear all previous output
ob_end_clean();

// Set content type header and output JSON
header('Content-Type: application/json');
echo json_encode($report_data, JSON_PRETTY_PRINT);
exit;
?>