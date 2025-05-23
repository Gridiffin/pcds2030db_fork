<?php
/**
 * Agency Dashboard Data AJAX Endpoint
 * 
 * Provides dashboard data based on selected period_id
 * @version 2.0.0
 */

// Core dependencies only
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/db_connect.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/agencies/statistics.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Verify user is an agency
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
        throw new Exception("Permission denied", 403);
    }

    // Check if this is an AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        throw new Exception("Invalid request method", 400);
    }

    // Get and validate parameters
    $period_id = filter_input(INPUT_GET, 'period_id', FILTER_VALIDATE_INT) ?? 
                filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT);
                
    // Get submission status
    $submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);
    
    if (isset($submission_status['error'])) {
        throw new Exception($submission_status['error']);
    }

    // Prepare response data
    $response = [
        'success' => true,
        'stats' => [
            'total' => $submission_status['total_programs'],
            'on-track' => $submission_status['program_status']['on-track'],
            'delayed' => $submission_status['program_status']['delayed'],
            'completed' => $submission_status['program_status']['completed'],
            'not_started' => $submission_status['program_status']['not-started']
        ],
        'chart_data' => [
            'labels' => ['On Track', 'Delayed', 'Completed', 'Not Started'],
            'data' => [
                $submission_status['program_status']['on-track'],
                $submission_status['program_status']['delayed'],
                $submission_status['program_status']['completed'],
                $submission_status['program_status']['not-started']
            ]
        ],
        'submission_status' => [
            'total_programs' => $submission_status['total_programs'],
            'programs_submitted' => $submission_status['programs_submitted'],
            'draft_count' => $submission_status['draft_count'],
            'not_submitted' => $submission_status['not_submitted']
        ]
    ];

    // Add current period info if available
    if ($period_id) {
        $period_info = get_reporting_period($period_id);
        if ($period_info) {
            $response['period'] = [
                'id' => $period_info['period_id'],
                'quarter' => $period_info['quarter'],
                'year' => $period_info['year'],
                'status' => $period_info['status']
            ];
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    $status_code = $e->getCode();
    if (!in_array($status_code, [400, 401, 403, 404, 500])) {
        $status_code = 500;
    }
    
    http_response_code($status_code);
    
    error_log(sprintf(
        "Error in agency_dashboard_data.php: %s\nStack trace: %s", 
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
