<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Simple Gantt Chart Data API
 * 
 * Returns initiative, programs, and targets data for the simple custom Gantt chart
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Ensure database connection is available (PDO)
global $conn, $pdo;

// If $pdo is not defined, create it from the existing configuration
if (!isset($pdo)) {
    try {
        $db_host = DB_HOST;
        $db_name = DB_NAME;
        
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]);
        exit;
    }
}

try {
    // Get initiative ID from request
    $initiative_id = isset($_GET['initiative_id']) ? intval($_GET['initiative_id']) : 0;
    
    if (!$initiative_id) {
        throw new Exception('Initiative ID is required');
    }
    
    // Get initiative details
    $initiative_sql = "SELECT initiative_id, initiative_name, start_date, end_date 
                      FROM initiatives 
                      WHERE initiative_id = ?";
    $stmt = $pdo->prepare($initiative_sql);
    $stmt->execute([$initiative_id]);
    $initiative = $stmt->fetch();
    
    if (!$initiative) {
        throw new Exception('Initiative not found');
    }
    
    // Get programs for this initiative
    $programs_sql = "SELECT program_id, program_name, program_number 
                     FROM programs 
                     WHERE initiative_id = ? 
                     ORDER BY program_number, program_name";
    $stmt = $pdo->prepare($programs_sql);
    $stmt->execute([$initiative_id]);
    $programs = $stmt->fetchAll();
    
    // Get reporting periods for timeline
    $periods_sql = "SELECT period_id, year, period_type, period_number, start_date, end_date 
                    FROM reporting_periods 
                    ORDER BY year, period_number";
    $stmt = $pdo->prepare($periods_sql);
    $stmt->execute();
    $periods = $stmt->fetchAll();
    
    // Build periods lookup
    $periods_lookup = [];
    foreach ($periods as $period) {
        $periods_lookup[$period['period_id']] = $period;
    }
    
    // Get all program submissions with targets for this initiative's programs
    $program_ids = array_column($programs, 'program_id');
    if (!empty($program_ids)) {
        $placeholders = str_repeat('?,', count($program_ids) - 1) . '?';
        $submissions_sql = "SELECT ps.program_id, ps.period_id, ps.content_json, ps.submission_date
                           FROM program_submissions ps
                           WHERE ps.program_id IN ($placeholders)
                           ORDER BY ps.program_id, ps.period_id";
        $stmt = $pdo->prepare($submissions_sql);
        $stmt->execute($program_ids);
        $submissions = $stmt->fetchAll();
    } else {
        $submissions = [];
    }
    
    // Transform data for Gantt chart
    $gantt_data = transform_for_simple_gantt($initiative, $programs, $submissions, $periods_lookup);
    
    echo json_encode([
        'success' => true,
        'data' => $gantt_data,
        'debug' => [
            'initiative_id' => $initiative_id,
            'programs_count' => count($programs),
            'submissions_count' => count($submissions),
            'periods_count' => count($periods)
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Simple Gantt data API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load gantt data',
        'message' => $e->getMessage()
    ]);
}

/**
 * Transform data for simple Gantt chart display
 */
function transform_for_simple_gantt($initiative, $programs, $submissions, $periods_lookup) {
    $result = [
        'initiative' => $initiative,
        'timeline' => generate_timeline($initiative['start_date'], $initiative['end_date'], $periods_lookup),
        'programs' => []
    ];
    
    // Group submissions by program_id and period_id
    $submissions_by_program = [];
    foreach ($submissions as $submission) {
        $program_id = $submission['program_id'];
        $period_id = $submission['period_id'];
        
        if (!isset($submissions_by_program[$program_id])) {
            $submissions_by_program[$program_id] = [];
        }
        
        $submissions_by_program[$program_id][$period_id] = $submission;
    }
    
    // Build program data with targets
    foreach ($programs as $program) {
        $program_id = $program['program_id'];
        $program_data = [
            'program_id' => $program_id,
            'program_name' => $program['program_name'],
            'program_number' => $program['program_number'],
            'targets' => []
        ];
        
        // Extract targets from all submissions for this program
        $targets_by_number = [];
        
        if (isset($submissions_by_program[$program_id])) {
            foreach ($submissions_by_program[$program_id] as $period_id => $submission) {
                $content = json_decode($submission['content_json'], true);
                
                if (isset($content['targets']) && is_array($content['targets'])) {
                    foreach ($content['targets'] as $target) {
                        $target_number = $target['target_number'] ?? '';
                        $target_text = $target['target_text'] ?? '';
                        
                        // Handle both 'target_status' and 'status_description' fields (both optional)
                        $target_status = $target['target_status'] ?? $target['status_description'] ?? null;
                        
                        // Use target_text as key if target_number is empty
                        $target_key = !empty($target_number) ? $target_number : $target_text;
                        
                        if (!isset($targets_by_number[$target_key])) {
                            $targets_by_number[$target_key] = [
                                'target_number' => $target_number,
                                'target_text' => $target_text,
                                'status_by_period' => []
                            ];
                        }
                        
                        // Only store status if it exists (target_status is optional)
                        if ($target_status !== null) {
                            $targets_by_number[$target_key]['status_by_period'][$period_id] = $target_status;
                        }
                    }
                }
            }
        }
        
        // Convert to indexed array
        $program_data['targets'] = array_values($targets_by_number);
        
        // Debug: Log target data for first program
        if (count($result['programs']) === 0) {
            error_log("Debug - Program {$program_id} targets: " . json_encode($program_data['targets']));
        }
        
        $result['programs'][] = $program_data;
    }
    
    return $result;
}

/**
 * Generate timeline structure from initiative dates
 */
function generate_timeline($start_date, $end_date, $periods_lookup) {
    if (!$start_date || !$end_date) {
        // Default timeline if no dates
        return [
            'years' => [2023, 2024, 2025],
            'quarters' => [
                2023 => ['Q1', 'Q2', 'Q3', 'Q4'],
                2024 => ['Q1', 'Q2', 'Q3', 'Q4'],
                2025 => ['Q1', 'Q2', 'Q3', 'Q4']
            ],
            'periods_map' => []
        ];
    }
    
    $start_year = (int) date('Y', strtotime($start_date));
    $end_year = (int) date('Y', strtotime($end_date));
    
    $years = [];
    $quarters = [];
    $periods_map = [];
    
    // Build year range
    for ($year = $start_year; $year <= $end_year; $year++) {
        $years[] = $year;
        $quarters[$year] = ['Q1', 'Q2', 'Q3', 'Q4'];
    }
    
    // Map periods to year/quarter combinations
    foreach ($periods_lookup as $period_id => $period) {
        $year = (int) $period['year'];
        $period_type = $period['period_type'];
        $period_number = (int) $period['period_number'];
        
        // Only process quarter periods for the timeline
        if ($period_type === 'quarter' && $year >= $start_year && $year <= $end_year && $period_number >= 1 && $period_number <= 4) {
            $periods_map[$period_id] = [
                'year' => $year,
                'quarter' => "Q$period_number"
            ];
        }
    }
    
    return [
        'years' => $years,
        'quarters' => $quarters,
        'periods_map' => $periods_map
    ];
}
?>
