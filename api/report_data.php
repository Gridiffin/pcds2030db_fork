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

// --- 3. Get Monthly Labels ---
$monthly_labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
$current_year = $period['year'];
$previous_year = $current_year - 1;
$year_before_previous = $current_year - 2;

// --- 4. Get Sector Metrics from Database ---
// Fetch metrics for this sector and period
$sector_metrics = [];
$charts_data = [];
$kpis_data = [];

// NEW: Fetch metrics_details data for KPI sections
$metrics_details = [];

// First, try to find a "TPA Protection" metric specifically
$tpa_query = "SELECT detail_id, detail_name, detail_json FROM metrics_details 
              WHERE is_draft = 0 
              AND (detail_name LIKE '%TPA%' OR detail_name LIKE '%Protection%' OR detail_name LIKE '%Biodiversity%') 
              ORDER BY created_at DESC LIMIT 1";
$tpa_result = $conn->query($tpa_query);

// Then get other metrics
$metrics_details_query = "SELECT detail_id, detail_name, detail_json FROM metrics_details 
                          WHERE is_draft = 0 
                          AND (detail_name NOT LIKE '%TPA%' AND detail_name NOT LIKE '%Protection%' AND detail_name NOT LIKE '%Biodiversity%')
                          ORDER BY created_at DESC LIMIT 2";
$metrics_details_result = $conn->query($metrics_details_query);

// Process TPA metric if found
if ($tpa_result && $tpa_result->num_rows > 0) {
    $row = $tpa_result->fetch_assoc();
    $jsonData = json_decode($row['detail_json'], true);
    // Process the values and descriptions (they're stored as semicolon-separated strings)
    $values = explode(';', $jsonData['value'] ?? '');
    $descriptions = explode(';', $jsonData['description'] ?? '');
    
    // Create an array of items with value-description pairs
    $items = [];
    for ($i = 0; $i < count($values); $i++) {
        if (isset($values[$i]) && isset($descriptions[$i])) {
            $items[] = [
                'value' => $values[$i],
                'description' => $descriptions[$i]
            ];
        }
    }
    
    // Add TPA as first element
    $metrics_details[] = [
        'id' => $row['detail_id'],
        'name' => $row['detail_name'],
        'items' => $items
    ];
}

// Process other metrics
if ($metrics_details_result && $metrics_details_result->num_rows > 0) {
    while ($row = $metrics_details_result->fetch_assoc()) {
        $jsonData = json_decode($row['detail_json'], true);
        // Process the values and descriptions
        $values = explode(';', $jsonData['value'] ?? '');
        $descriptions = explode(';', $jsonData['description'] ?? '');
        
        // Create an array of items with value-description pairs
        $items = [];
        for ($i = 0; $i < count($values); $i++) {
            if (isset($values[$i]) && isset($descriptions[$i])) {
                $items[] = [
                    'value' => $values[$i],
                    'description' => $descriptions[$i]
                ];
            }
        }
        
        $metrics_details[] = [
            'id' => $row['detail_id'],
            'name' => $row['detail_name'],
            'items' => $items
        ];
    }
}

// Initialize timber export data for current year and previous year (instead of hardcoded 2022/2023)
$timber_export_data = [
    $current_year => array_fill(0, 12, 0), // Initialize with zeros for each month of current year
    $previous_year => array_fill(0, 12, 0)  // Initialize with zeros for each month of previous year
];

// Query to find Timber Export Value records
$timber_query = "SELECT m.data_json, m.table_name 
                FROM sector_metrics_data m 
                WHERE m.sector_id = ? 
                AND m.is_draft = 0 
                AND (m.table_name LIKE '%Timber Export%' OR m.table_name LIKE '%Export Value%')
                ORDER BY m.updated_at DESC";
                
$stmt = $conn->prepare($timber_query);
$stmt->bind_param("i", $sector_id);
$stmt->execute();
$timber_result = $stmt->get_result();

if ($timber_result->num_rows > 0) {
    // Process timber export metrics data
    while ($row = $timber_result->fetch_assoc()) {
        $data = json_decode($row['data_json'], true);
        
        // Check if we have the structure with years as columns and months as rows
        if (isset($data['columns']) && isset($data['data'])) {
            // Try to find current year and previous year data
            $current_year_str = (string)$current_year;
            $previous_year_str = (string)$previous_year;
            
            // Check if the data follows the format where years are columns
            if (in_array($current_year_str, $data['columns']) || in_array($previous_year_str, $data['columns'])) {
                // Direct year-based structure with months as keys
                foreach ($data['data'] as $month => $values) {
                    // Get month index (0-based)
                    $month_index = array_search(strtoupper(substr($month, 0, 3)), array_map('strtoupper', $monthly_labels));
                    if ($month_index !== false) {
                        // Store values for current year if available
                        if (isset($values[$current_year_str]) && is_numeric($values[$current_year_str])) {
                            $timber_export_data[$current_year][$month_index] = floatval($values[$current_year_str]);
                        }
                        // Store values for previous year if available
                        if (isset($values[$previous_year_str]) && is_numeric($values[$previous_year_str])) {
                            $timber_export_data[$previous_year][$month_index] = floatval($values[$previous_year_str]);
                        }
                    }
                }
                // We found the data, no need to look further
                break;
            } else {
                // Try the alternative structure where column names contain "timber export"
                foreach ($data['columns'] as $column) {
                    if (stripos($column, 'timber export') !== false || stripos($column, 'export value') !== false) {
                        // This column contains timber export data
                        foreach ($data['data'] as $month => $values) {
                            if (isset($values[$column]) && is_numeric($values[$column])) {
                                // Get month index (0-based)
                                $month_index = array_search(strtoupper(substr($month, 0, 3)), array_map('strtoupper', $monthly_labels));
                                if ($month_index !== false) {
                                    // Check if year is specified in the data
                                    if (isset($data['year'])) {
                                        $year = (int)$data['year'];
                                        if ($year == $current_year) {
                                            $timber_export_data[$current_year][$month_index] = floatval($values[$column]);
                                        } else if ($year == $previous_year) {
                                            $timber_export_data[$previous_year][$month_index] = floatval($values[$column]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Now query for other metrics data
$metrics_query = "SELECT * FROM sector_metrics_data 
                  WHERE sector_id = ? AND period_id = ? AND is_draft = 0";
$stmt = $conn->prepare($metrics_query);
$stmt->bind_param("ii", $sector_id, $period_id);
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

// Default secondary chart data - still using placeholder data
$secondary_chart_data = [
    'labels' => $monthly_labels,
    'data' . $year_before_previous => array_fill(0, 12, 0),
    'data' . $previous_year => array_fill(0, 12, 0),
    'data' . $current_year => array_fill(0, 12, 0),
    'total' . $current_year => "0"
];

// Default KPI values (can be customized by sector)
$kpi1_data = ['name' => '', 'value' => '0', 'description' => ''];
$kpi2_data = ['name' => '', 'value' => '0', 'description' => ''];
$kpi3_data = ['name' => '', 'value' => '0', 'description' => ''];

// Update KPI data with metrics_details if available
if (!empty($metrics_details)) {
    // If we have TPA Protection data (first detail), use it for KPI1
    if (isset($metrics_details[0]) && strpos(strtolower($metrics_details[0]['name']), 'tpa') !== false) {
        $kpi1_data = [
            'name' => $metrics_details[0]['name'],
            'value' => $metrics_details[0]['items'][0]['value'] ?? '0',
            'description' => $metrics_details[0]['items'][0]['description'] ?? ''
        ];
    }
    
    // Use other metrics for KPI2 and KPI3 if available
    if (isset($metrics_details[1])) {
        $kpi2_data = [
            'name' => $metrics_details[1]['name'],
            'value' => $metrics_details[1]['items'][0]['value'] ?? '0',
            'description' => $metrics_details[1]['items'][0]['description'] ?? ''
        ];
    }
    
    if (isset($metrics_details[2])) {
        $kpi3_data = [
            'name' => $metrics_details[2]['name'],
            'value' => $metrics_details[2]['items'][0]['value'] ?? '0',
            'description' => $metrics_details[2]['items'][0]['description'] ?? ''
        ];
    }
}

if ($metrics_result->num_rows > 0) {
    // Process each metric
    while ($metric = $metrics_result->fetch_assoc()) {
        $data = json_decode($metric['data_json'], true);
        
        // Determine if it's a chart or a KPI based on the data structure
        if (isset($data['type'])) {
            if ($data['type'] === 'chart') {
                $charts_data[$data['key']] = $data;
            } elseif ($data['type'] === 'kpi') {
                $kpis_data[$data['key']] = $data;
            }
        }
    }
}

// Set the chart titles and values based on the sector
switch ($sector_id) {
    case 1: // Forestry
        $main_chart_title = "Timber Export Value (RM)";
        $secondary_chart_title = "Total Degraded Area Restored (Ha)";
        $kpi1_data = ['name' => 'TPA Protection & Biodiversity Conserved', 'value' => '32', 'description' => 'On-going conservation programs'];
        $kpi2_data = ['name' => 'Forest Management Unit (FMU)', 'value' => '78%', 'description' => '2,327,221 ha'];
        $kpi3_data = ['name' => 'Forest Plantation Management Unit', 'value' => '69%', 'description' => '122,800 ha'];
        break;
            
    case 2: // Land
        $main_chart_title = "Land Development (Ha)";
        $secondary_chart_title = "Land Title Applications Processed";
        $kpi1_data = ['name' => 'New Native Land Titles', 'value' => '1.2K', 'description' => 'New titles issued this year'];
        $kpi2_data = ['name' => 'Land Survey Completion', 'value' => '65%', 'description' => 'Of annual target'];
        $kpi3_data = ['name' => 'Digital Registry Progress', 'value' => '80%', 'description' => 'Implementation completed'];
        break;
            
    case 3: // Environment
        $main_chart_title = "Air Quality Index";
        $secondary_chart_title = "Waste Management (Tons)";
        $kpi1_data = ['name' => 'Environmental Compliance', 'value' => '84%', 'description' => 'Industries in compliance'];
        $kpi2_data = ['name' => 'Water Quality Index', 'value' => '72%', 'description' => 'Clean water bodies'];
        $kpi3_data = ['name' => 'Recycling Rate', 'value' => '45%', 'description' => 'Of total waste'];
        break;
            
    case 4: // Natural Resources
        $main_chart_title = "Resource Extraction (Units)";
        $secondary_chart_title = "Sustainable Resource Management (%)";
        $kpi1_data = ['name' => 'Resource Inventory Completion', 'value' => '66%', 'description' => 'Statewide mapping'];
        $kpi2_data = ['name' => 'Sustainable Yield', 'value' => '87%', 'description' => 'Within sustainable limits'];
        $kpi3_data = ['name' => 'Conservation Areas', 'value' => '28%', 'description' => 'Of total resource lands'];
        break;
            
    case 5: // Urban Development
        $main_chart_title = "Urban Growth (km²)";
        $secondary_chart_title = "Housing Development (Units)";
        $kpi1_data = ['name' => 'Affordable Housing', 'value' => '3.4K', 'description' => 'New units completed'];
        $kpi2_data = ['name' => 'Green Space Ratio', 'value' => '22%', 'description' => 'Of urban area'];
        $kpi3_data = ['name' => 'Infrastructure Development', 'value' => '76%', 'description' => 'Of annual plan completed'];
        break;
}

// Store default charts and KPIs in the arrays
$charts_data['main_chart'] = [
    'type' => 'chart',
    'key' => 'main_chart',
    'title' => $main_chart_title,
    'data' => $main_chart_data
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
    'charts' => $charts_data,
    'kpis' => $kpis_data,
    'draftDate' => $draft_date,
    'sector_id' => $sector_id,  // Include sector_id for client-side use
    'metrics_details' => $metrics_details,  // Include the new metrics details for KPIs
    
    // Include individual KPI data for easier access in the JS code
    'kpi1' => $kpi1_data,
    'kpi2' => $kpi2_data, 
    'kpi3' => $kpi3_data
];

// Clear all previous output
ob_end_clean();

// Set content type header and output JSON
header('Content-Type: application/json');
echo json_encode($report_data, JSON_PRETTY_PRINT);
exit;
?>