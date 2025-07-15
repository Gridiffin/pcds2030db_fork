<?php
/**
 * Get Program Statistics AJAX Handler
 * 
 * Provides comprehensive statistics for a program including:
 * - Total submissions
 * - Target completion rates
 * - Timeline data
 * - Performance metrics
 */

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is an agency
if (!is_agency()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Get program ID from request
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    echo json_encode(['error' => 'Invalid program ID']);
    exit;
}

try {
    // Get program details
    $program = get_program_details($program_id, true);
    
    if (!$program) {
        echo json_encode(['error' => 'Program not found']);
        exit;
    }
    
    // Initialize statistics
    $stats = [
        'total_submissions' => 0,
        'draft_submissions' => 0,
        'final_submissions' => 0,
        'total_targets' => 0,
        'completed_targets' => 0,
        'in_progress_targets' => 0,
        'delayed_targets' => 0,
        'not_started_targets' => 0,
        'completion_rate' => 0,
        'average_submission_frequency' => 0,
        'last_activity_date' => null,
        'timeline_data' => [],
        'performance_trend' => []
    ];
    
    // Get submission statistics
    if (!empty($program['submissions'])) {
        $stats['total_submissions'] = count($program['submissions']);
        
        foreach ($program['submissions'] as $submission) {
            if (isset($submission['is_draft']) && $submission['is_draft']) {
                $stats['draft_submissions']++;
            } else {
                $stats['final_submissions']++;
            }
        }
        
        // Calculate average submission frequency
        if ($stats['total_submissions'] > 1) {
            $first_submission = end($program['submissions']);
            $last_submission = $program['submissions'][0];
            
            if (isset($first_submission['submitted_at']) && isset($last_submission['submitted_at'])) {
                $first_date = new DateTime($first_submission['submitted_at']);
                $last_date = new DateTime($last_submission['submitted_at']);
                $interval = $first_date->diff($last_date);
                $days_between = $interval->days;
                
                if ($days_between > 0) {
                    $stats['average_submission_frequency'] = round($days_between / ($stats['total_submissions'] - 1), 1);
                }
            }
        }
        
        // Get last activity date
        if (isset($program['submissions'][0]['updated_at']) && $program['submissions'][0]['updated_at']) {
            $stats['last_activity_date'] = $program['submissions'][0]['updated_at'];
        }
    }
    
    // Get target statistics
    $target_stats = get_program_target_statistics($program_id);
    $stats = array_merge($stats, $target_stats);
    
    // Get timeline data
    $stats['timeline_data'] = get_program_timeline_data($program_id);
    
    // Get performance trend
    $stats['performance_trend'] = get_program_performance_trend($program_id);
    
    // Calculate overall completion rate
    if ($stats['total_targets'] > 0) {
        $stats['completion_rate'] = round(($stats['completed_targets'] / $stats['total_targets']) * 100, 1);
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_program_stats.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Get target statistics for a program
 */
function get_program_target_statistics($program_id) {
    global $conn;
    
    $stats = [
        'total_targets' => 0,
        'completed_targets' => 0,
        'in_progress_targets' => 0,
        'delayed_targets' => 0,
        'not_started_targets' => 0
    ];
    
    $query = "SELECT pt.status_indicator, COUNT(*) as count
              FROM program_targets pt
              JOIN program_submissions ps ON pt.submission_id = ps.submission_id
              WHERE ps.program_id = ? AND pt.is_deleted = 0
              GROUP BY pt.status_indicator";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $stats['total_targets'] += $row['count'];
        
        switch ($row['status_indicator']) {
            case 'completed':
                $stats['completed_targets'] = $row['count'];
                break;
            case 'in_progress':
                $stats['in_progress_targets'] = $row['count'];
                break;
            case 'delayed':
                $stats['delayed_targets'] = $row['count'];
                break;
            case 'not_started':
                $stats['not_started_targets'] = $row['count'];
                break;
        }
    }
    
    return $stats;
}

/**
 * Get timeline data for a program
 */
function get_program_timeline_data($program_id) {
    global $conn;
    
    $timeline_data = [];
    
    $query = "SELECT ps.submitted_at, ps.is_draft, rp.year, rp.period_type, rp.period_number
              FROM program_submissions ps
              JOIN reporting_periods rp ON ps.period_id = rp.period_id
              WHERE ps.program_id = ?
              ORDER BY ps.submitted_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $period_name = '';
        if ($row['period_type'] === 'quarter') {
            $period_name = "Q{$row['period_number']}-{$row['year']}";
        } elseif ($row['period_type'] === 'half') {
            $period_name = "H{$row['period_number']}-{$row['year']}";
        } elseif ($row['period_type'] === 'yearly') {
            $period_name = "Y{$row['period_number']}-{$row['year']}";
        }
        
        $timeline_data[] = [
            'date' => $row['submitted_at'],
            'period' => $period_name,
            'is_draft' => (bool)$row['is_draft'],
            'year' => $row['year'],
            'period_type' => $row['period_type'],
            'period_number' => $row['period_number']
        ];
    }
    
    return $timeline_data;
}

/**
 * Get performance trend for a program
 */
function get_program_performance_trend($program_id) {
    global $conn;
    $performance_trend = [];
    // Fetch all targets for all submissions
    $query = "SELECT ps.submission_id, ps.submitted_at, pt.status_indicator
              FROM program_submissions ps
              LEFT JOIN program_targets pt ON ps.submission_id = pt.submission_id AND pt.is_deleted = 0
              WHERE ps.program_id = ?
              ORDER BY ps.submitted_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Aggregate in PHP
    $trend_map = [];
    while ($row = $result->fetch_assoc()) {
        $sid = $row['submission_id'];
        if (!isset($trend_map[$sid])) {
            $trend_map[$sid] = [
                'date' => $row['submitted_at'],
                'completed' => 0,
                'in_progress' => 0,
                'delayed' => 0,
                'not_started' => 0,
                'total_targets' => 0
            ];
        }
        if ($row['status_indicator']) {
            $trend_map[$sid]['total_targets']++;
            switch ($row['status_indicator']) {
                case 'completed':
                    $trend_map[$sid]['completed']++;
                    break;
                case 'in_progress':
                    $trend_map[$sid]['in_progress']++;
                    break;
                case 'delayed':
                    $trend_map[$sid]['delayed']++;
                    break;
                case 'not_started':
                    $trend_map[$sid]['not_started']++;
                    break;
            }
        }
    }
    // Calculate completion rate and build final array
    foreach ($trend_map as $trend) {
        $completion_rate = $trend['total_targets'] > 0 ? round(($trend['completed'] / $trend['total_targets']) * 100, 1) : 0;
        $trend['completion_rate'] = $completion_rate;
        $performance_trend[] = $trend;
    }
    return $performance_trend;
}
?> 