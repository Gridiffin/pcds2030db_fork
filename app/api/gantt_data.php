<?php
/**
 * API endpoint for Gantt Chart data
 * 
 * Returns initiatives and their programs data formatted for dhtmlxGantt
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/initiatives.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Verify user is logged in and is an agency
if (!is_agency()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get current agency ID
    $agency_id = $_SESSION['user_id'];

    // Support filtering by initiative_id for details view
    $initiative_id = isset($_GET['initiative_id']) ? intval($_GET['initiative_id']) : 0;

    // Get filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

    // Build filters array
    $filters = [];
    if (!empty($search)) {
        $filters['search'] = $search;
    }
    if ($status_filter !== '') {
        $filters['is_active'] = $status_filter === 'active' ? 1 : 0;
    }

    // If initiative_id is provided, only fetch that initiative
    if ($initiative_id) {
        $initiatives = get_agency_initiatives_with_programs($agency_id, $filters, $initiative_id);
    } else {
        $initiatives = get_agency_initiatives_with_programs($agency_id, $filters);
    }

    // Transform data for gantt chart
    $gantt_data = transform_for_gantt($initiatives);

    // Return successful response
    echo json_encode([
        'success' => true,
        'data' => $gantt_data,
        'count' => count($initiatives)
    ]);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Gantt data API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load gantt data',
        'message' => $e->getMessage()
    ]);
}

/**
 * Get initiatives with their associated programs for gantt display
 */
function get_agency_initiatives_with_programs($agency_id, $filters = [], $initiative_id = 0) {
    global $pdo;
    
    try {
        // Base query to get initiatives that have programs assigned to the current agency
        $sql = "
            SELECT DISTINCT 
                i.initiative_id,
                i.initiative_name,
                i.initiative_number,
                i.initiative_description,
                i.start_date as initiative_start_date,
                i.end_date as initiative_end_date,
                i.is_active as initiative_is_active,
                i.created_at as initiative_created_at
            FROM initiatives i
            INNER JOIN programs p ON p.initiative_id = i.initiative_id
            WHERE p.owner_agency_id = :agency_id
        ";

        $params = ['agency_id' => $agency_id];

        // If initiative_id is provided, filter for it
        if ($initiative_id) {
            $sql .= " AND i.initiative_id = :initiative_id";
            $params['initiative_id'] = $initiative_id;
        }

        // Add filters
        if (!empty($filters['search'])) {
            $sql .= " AND (
                i.initiative_name LIKE :search 
                OR i.initiative_number LIKE :search 
                OR i.initiative_description LIKE :search
            )";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND i.is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        $sql .= " ORDER BY i.initiative_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $initiatives = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get programs for each initiative
        foreach ($initiatives as &$initiative) {
            $initiative['programs'] = get_initiative_programs($initiative['initiative_id'], $agency_id);
        }
        
        return $initiatives;
        
    } catch (PDOException $e) {
        throw new Exception("Database error: " . $e->getMessage());
    }
}

/**
 * Get programs for a specific initiative and agency
 */
function get_initiative_programs($initiative_id, $agency_id) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                p.program_id,
                p.program_name,
                p.program_number,
                p.start_date,
                p.end_date,
                p.created_at,
                p.updated_at,
                p.is_assigned,
                p.status_indicator,
                u.agency_name,
                COALESCE(ps.submission_count, 0) as submission_count,
                CASE 
                    WHEN p.end_date < CURDATE() AND COALESCE(ps.submission_count, 0) = 0 THEN 'Delayed'
                    WHEN p.start_date > CURDATE() THEN 'Planning'
                    WHEN COALESCE(ps.submission_count, 0) > 0 THEN 'Active'
                    ELSE 'On Track'
                END as calculated_status
            FROM programs p
            LEFT JOIN users u ON u.user_id = p.owner_agency_id
            LEFT JOIN (
                SELECT 
                    program_id, 
                    COUNT(*) as submission_count 
                FROM program_submissions 
                WHERE is_draft = 0 
                GROUP BY program_id
            ) ps ON ps.program_id = p.program_id
            WHERE p.initiative_id = :initiative_id 
            AND p.owner_agency_id = :agency_id
            ORDER BY p.program_number ASC, p.program_name ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'initiative_id' => $initiative_id,
            'agency_id' => $agency_id
        ]);
        
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get targets (from program_submissions) for each program
        foreach ($programs as &$program) {
            $program['targets'] = get_program_targets($program['program_id']);
        }
        
        return $programs;
        
    } catch (PDOException $e) {
        throw new Exception("Database error fetching programs: " . $e->getMessage());
    }
}

/**
 * Get targets from program submissions for a specific program
 */
function get_program_targets($program_id) {
    global $pdo;
    
    try {
        $sql = "
            SELECT 
                ps.submission_id,
                ps.period_id,
                ps.content_json,
                rp.year,
                rp.quarter,
                rp.start_date as period_start,
                rp.end_date as period_end
            FROM program_submissions ps
            JOIN reporting_periods rp ON ps.period_id = rp.period_id
            WHERE ps.program_id = :program_id 
            AND ps.is_draft = 0
            AND ps.content_json IS NOT NULL 
            AND ps.content_json != ''
            AND ps.content_json != '[]'
            ORDER BY rp.year ASC, rp.quarter ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['program_id' => $program_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $targets = [];
        $target_counter = 1;
        
        foreach ($submissions as $submission) {
            $content = json_decode($submission['content_json'], true);
            
            if (!$content) continue;
            
            // Handle different JSON formats
            if (isset($content['targets']) && is_array($content['targets'])) {
                // Multiple targets format
                foreach ($content['targets'] as $target_data) {
                    if (isset($target_data['target_text']) && !empty($target_data['target_text'])) {
                        $targets[] = [
                            'target_id' => $target_counter++,
                            'target_text' => $target_data['target_text'],
                            'status_description' => $target_data['status_description'] ?? '',
                            'submission_id' => $submission['submission_id'],
                            'period_id' => $submission['period_id'],
                            'year' => $submission['year'],
                            'quarter' => $submission['quarter'],
                            'period_start' => $submission['period_start'],
                            'period_end' => $submission['period_end']
                        ];
                    }
                }
            } elseif (isset($content['target']) && !empty($content['target'])) {
                // Single target format
                $targets[] = [
                    'target_id' => $target_counter++,
                    'target_text' => $content['target'],
                    'status_description' => $content['status_description'] ?? '',
                    'submission_id' => $submission['submission_id'],
                    'period_id' => $submission['period_id'],
                    'year' => $submission['year'],
                    'quarter' => $submission['quarter'],
                    'period_start' => $submission['period_start'],
                    'period_end' => $submission['period_end']
                ];
            }
        }
        
        return $targets;
        
    } catch (PDOException $e) {
        throw new Exception("Database error fetching targets: " . $e->getMessage());
    }
}

/**
 * Transform initiative and program data for dhtmlxGantt format
 */
function transform_for_gantt($initiatives) {
    $tasks = [];
    $links = [];
    $task_id = 1;
    
    foreach ($initiatives as $initiative) {
        // Calculate initiative date range from programs
        $initiative_start = $initiative['initiative_start_date'];
        $initiative_end = $initiative['initiative_end_date'];
        
        // If no initiative dates, calculate from programs
        if (!$initiative_start || !$initiative_end) {
            $program_dates = [];
            foreach ($initiative['programs'] as $program) {
                if ($program['start_date']) {
                    $program_dates[] = $program['start_date'];
                }
                if ($program['end_date']) {
                    $program_dates[] = $program['end_date'];
                }
                // Also consider target dates
                foreach ($program['targets'] ?? [] as $target) {
                    if ($target['period_start']) {
                        $program_dates[] = $target['period_start'];
                    }
                    if ($target['period_end']) {
                        $program_dates[] = $target['period_end'];
                    }
                }
            }
            
            if (!empty($program_dates)) {
                if (!$initiative_start) {
                    $initiative_start = min($program_dates);
                }
                if (!$initiative_end) {
                    $initiative_end = max($program_dates);
                }
            }
        }
        
        // Default dates if still empty
        if (!$initiative_start) $initiative_start = date('Y-m-d');
        if (!$initiative_end) $initiative_end = date('Y-m-d', strtotime('+1 year'));
        
        // Add initiative as parent task
        $initiative_task = [
            'id' => $task_id++,
            'text' => $initiative['initiative_name'],
            'start_date' => $initiative_start,
            'end_date' => $initiative_end,
            'type' => 'project',
            'open' => true,
            'readonly' => true,
            'initiative_id' => $initiative['initiative_id'],
            'initiative_number' => $initiative['initiative_number'],
            'description' => $initiative['initiative_description'],
            'progress' => calculate_initiative_progress($initiative['programs'])
        ];
        
        $tasks[] = $initiative_task;
        $parent_id = $initiative_task['id'];
        
        // Add programs as child tasks
        foreach ($initiative['programs'] as $program) {
            $program_start = $program['start_date'] ?: $initiative_start;
            $program_end = $program['end_date'] ?: $initiative_end;
            
            // Calculate program date range from targets if available
            if (!empty($program['targets'])) {
                $target_dates = [];
                foreach ($program['targets'] as $target) {
                    if ($target['period_start']) {
                        $target_dates[] = $target['period_start'];
                    }
                    if ($target['period_end']) {
                        $target_dates[] = $target['period_end'];
                    }
                }
                if (!empty($target_dates)) {
                    $program_start = min($target_dates);
                    $program_end = max($target_dates);
                }
            }
            
            $program_task = [
                'id' => $task_id++,
                'text' => $program['program_name'],
                'start_date' => $program_start,
                'end_date' => $program_end,
                'parent' => $parent_id,
                'type' => 'task',
                'readonly' => true,
                'program_id' => $program['program_id'],
                'program_number' => $program['program_number'],
                'status' => $program['calculated_status'],
                'agency_name' => $program['agency_name'],
                'submission_count' => $program['submission_count'],
                'progress' => calculate_program_progress($program),
                'has_targets' => !empty($program['targets'])
            ];
            
            $tasks[] = $program_task;
            $program_task_id = $program_task['id'];
            
            // Add targets as grandchildren
            foreach ($program['targets'] ?? [] as $target) {
                $target_task = [
                    'id' => $task_id++,
                    'text' => $target['target_text'],
                    'start_date' => $target['period_start'],
                    'end_date' => $target['period_end'],
                    'parent' => $program_task_id,
                    'type' => 'milestone',
                    'readonly' => true,
                    'target_id' => $target['target_id'],
                    'target_number' => $target['target_id'], // Use target_id as number
                    'submission_id' => $target['submission_id'],
                    'period_id' => $target['period_id'],
                    'year' => $target['year'],
                    'quarter' => $target['quarter'],
                    'status_description' => $target['status_description'],
                    'progress' => calculate_target_progress($target)
                ];
                
                $tasks[] = $target_task;
            }
        }
    }
    
    return [
        'data' => $tasks,
        'links' => $links
    ];
}

/**
 * Calculate target progress based on status description
 */
function calculate_target_progress($target) {
    $status_desc = strtolower($target['status_description'] ?? '');
    
    if (strpos($status_desc, 'completed') !== false || strpos($status_desc, 'achieved') !== false) {
        return 1.0; // 100%
    } elseif (strpos($status_desc, 'in progress') !== false || strpos($status_desc, 'ongoing') !== false) {
        return 0.5; // 50%
    } elseif (strpos($status_desc, 'not started') !== false || empty($status_desc)) {
        return 0.0; // 0%
    } else {
        return 0.3; // Default for other statuses
    }
}

/**
 * Calculate initiative progress based on its programs
 */
function calculate_initiative_progress($programs) {
    if (empty($programs)) return 0;
    
    $total_progress = 0;
    foreach ($programs as $program) {
        $total_progress += calculate_program_progress($program);
    }
    
    return $total_progress / count($programs);
}

/**
 * Calculate program progress based on submissions and dates
 */
function calculate_program_progress($program) {
    // If program has submissions, it's considered active/progressing
    if ($program['submission_count'] > 0) {
        return 0.7; // 70% progress if has submissions
    }
    
    // If program has started but no submissions
    if ($program['start_date'] && strtotime($program['start_date']) <= time()) {
        return 0.3; // 30% progress if started
    }
    
    // If program hasn't started yet
    return 0;
}
?>
