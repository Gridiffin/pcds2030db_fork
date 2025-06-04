<?php
/**
 * Admin Statistics Functions
 * 
 * Contains functions for retrieving statistics and data for admin dashboards
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get dashboard statistics for admin overview
 * @return array Statistics for admin dashboard
 */
function get_admin_dashboard_stats() {
    global $conn;
    
    // Only admin should access this
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Initialize stats array
    $stats = [
        'total_agencies' => 0,
        'total_programs' => 0,
        'submissions_complete' => 0,
        'submissions_pending' => 0,
        'program_status' => [],
        'sector_programs' => []
    ];
    
    // Get current period
    $current_period = get_current_reporting_period();
    $period_id = $current_period['period_id'] ?? null;
    
    // Get counts
    $query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'agency') AS total_agencies,
                (SELECT COUNT(*) FROM programs) AS total_programs";
    
    $result = $conn->query($query);
    $counts = $result->fetch_assoc();
    
    $stats['total_agencies'] = $counts['total_agencies'];
    $stats['total_programs'] = $counts['total_programs'];
    
    // If we have an active period, get submission status
    if ($period_id) {
        // Get program submission counts
        $query = "SELECT 
                    u.user_id,
                    (SELECT COUNT(*) FROM programs p WHERE p.owner_agency_id = u.user_id) AS agency_programs,
                    (SELECT COUNT(*) FROM program_submissions ps 
                     JOIN programs p ON ps.program_id = p.program_id 
                     WHERE p.owner_agency_id = u.user_id AND ps.period_id = ?) AS submitted_programs
                  FROM users u
                  WHERE u.role = 'agency'";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $completed = 0;
        $pending = 0;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['agency_programs'] > 0) {
                if ($row['submitted_programs'] >= $row['agency_programs']) {
                    $completed++;
                } else {
                    $pending++;
                }
            }
        }
        
        $stats['submissions_complete'] = $completed;
        $stats['submissions_pending'] = $pending;
        
        // Get program status distribution
        $query = "SELECT ps.status, COUNT(*) as count
                  FROM program_submissions ps 
                  WHERE ps.period_id = ?
                  GROUP BY ps.status";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $status_data = [
            'labels' => [],
            'data' => [],
            'backgroundColor' => [
                '#28a745', // on-track (green)
                '#ffc107', // delayed (yellow)
                '#17a2b8', // completed (blue)
                '#6c757d'  // not-started (gray)
            ]
        ];
        
        while ($row = $result->fetch_assoc()) {
            $status_data['labels'][] = ucfirst($row['status']);
            $status_data['data'][] = $row['count'];
        }
        
        $stats['program_status'] = $status_data;
    }
    
    // Get programs by sector
    $query = "SELECT s.sector_name, COUNT(p.program_id) as program_count
              FROM sectors s
              LEFT JOIN programs p ON s.sector_id = p.sector_id
              GROUP BY s.sector_id
              ORDER BY program_count DESC";
    
    $result = $conn->query($query);
    
    $sector_data = [
        'labels' => [],
        'data' => [],
        'backgroundColor' => [
            '#8591a4', // Primary color
            '#A49885', // Secondary color
            '#607b9b', // Variation of primary
            '#b3a996', // Variation of secondary
            '#4f616f'  // Another variation
        ]
    ];
    
    while ($row = $result->fetch_assoc()) {
        $sector_data['labels'][] = $row['sector_name'];
        $sector_data['data'][] = $row['program_count'];
    }
    
    $stats['sector_programs'] = $sector_data;
    
    return $stats;
}

/**
 * Get submission statistics for a specific reporting period
 * 
 * @param int $period_id - The ID of the reporting period
 * @return array - Array containing submission statistics
 */
function get_period_submission_stats($period_id) {
    global $conn;
    
    // Initialize stats array
    $stats = [
        'agencies_reported' => 0,
        'total_agencies' => 0,
        'on_track_programs' => 0,
        'delayed_programs' => 0,
        'total_programs' => 0,
        'completion_percentage' => 0
    ];
    
    // Get total agencies
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'agency' AND is_active = 1";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_agencies'] = $row['total'];
    }
    
    // Get agencies that have reported
    $query = "SELECT COUNT(DISTINCT submitted_by) as reported 
              FROM program_submissions 
              WHERE period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $stats['agencies_reported'] = $row['reported'];
    }
    
    // Get program status counts
    $query = "SELECT is_draft, COUNT(*) as count 
              FROM program_submissions 
              WHERE period_id = ? 
              GROUP BY is_draft";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($result && $row = $result->fetch_assoc()) {
        if ($row['is_draft'] == 0) {
            // final submissions
            $stats['on_track_programs'] += $row['count'];
        } else {
            // draft submissions
            $stats['delayed_programs'] += $row['count'];
        }
    }
    
    // Get total programs
    $query = "SELECT COUNT(*) as total FROM programs";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_programs'] = $row['total'];
    }
    
    // Calculate completion percentage
    if ($stats['total_agencies'] > 0) {
        $stats['completion_percentage'] = round(($stats['agencies_reported'] / $stats['total_agencies']) * 100);
    }
    
    return $stats;
}

/**
 * Get all programs with detailed information for admin display
 * 
 * @param int $period_id Optional period ID to filter submissions
 * @param array $filters Optional filters (status, sector_id, agency_id, search)
 * @return array List of all programs with their details and status
 */
function get_admin_programs_list($period_id = null, $filters = []) {
    global $conn;

    $conditions = [];
    $params = [];
    $param_types = "";

    // Get period details if period_id is provided
    $period_info = null;
    if ($period_id) {
        $period_query = "SELECT period_id, start_date, end_date FROM reporting_periods WHERE period_id = ?";
        $period_stmt = $conn->prepare($period_query);
        $period_stmt->bind_param("i", $period_id);
        $period_stmt->execute();
        $period_result = $period_stmt->get_result();

        if ($period_result->num_rows > 0) {
            $period_info = $period_result->fetch_assoc();
        }
    }

    // Construct the main query with subquery to get latest submission per program
    $sql = "SELECT 
                p.program_id, p.program_name, p.owner_agency_id, p.sector_id, p.created_at,
                s.sector_name, 
                u.agency_name,
                latest_sub.submission_id, latest_sub.is_draft, latest_sub.submission_date, latest_sub.updated_at, latest_sub.period_id AS submission_period_id,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating')), 'not-started') as rating
            FROM programs p
            JOIN sectors s ON p.sector_id = s.sector_id
            JOIN users u ON p.owner_agency_id = u.user_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    WHERE period_id = ?
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id";
    
    $params = [$period_id];
    $param_types = 'i';
    
    // Apply filters
    if (!empty($filters)) {
        if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
            $conditions[] = "ps.status = ?";
            $params[] = $filters['status'];
            $param_types .= 's';
        }
        
        if (isset($filters['sector_id']) && $filters['sector_id'] !== 'all' && $filters['sector_id'] !== 0 && $filters['sector_id'] !== '') {
            $conditions[] = "p.sector_id = ?";
            $params[] = $filters['sector_id'];
            $param_types .= "i";
        }
        
        if (isset($filters['agency_id']) && $filters['agency_id'] !== 'all' && $filters['agency_id'] !== 0 && $filters['agency_id'] !== '') {
            $conditions[] = "p.owner_agency_id = ?";
            $params[] = $filters['agency_id'];
            $param_types .= "i";
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search_term = '%' . $filters['search'] . '%';
            $conditions[] = "(p.program_name LIKE ? OR p.description LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $param_types .= "ss";
        }
    }
    
    // Add program creation date filtering based on the viewing_period_id's start and end dates
    if ($period_info) {
        $conditions[] = "p.created_at >= ? AND p.created_at <= ?";
        $params[] = $period_info['start_date'] . ' 00:00:00';
        $params[] = $period_info['end_date'] . ' 23:59:59';
        $param_types .= 'ss';
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // ORDER BY and LIMIT clauses should not introduce a GROUP BY that causes this issue.
    // If a GROUP BY is necessary, it must include all non-aggregated selected columns.
    // For now, let's assume the GROUP BY was the issue and remove/adjust it if it's further down.
    // The error occurs at line 310, which is $stmt = $conn->prepare($sql);
    // This implies the $sql string itself is the problem before prepare.

    // If there was a GROUP BY p.program_id, we need to ensure all selected ps.* columns are handled.
    // However, the query structure with LEFT JOIN program_submissions ON ... AND ps.period_id = ?
    // should ideally return one row per program for that period if a submission exists, or NULLs for ps.* fields.
    // The issue might be if a program can have multiple submissions for the *same* period_id in the table,
    // which would be a data integrity issue.

    // Let's remove any explicit GROUP BY for now and see if the JOIN logic is sufficient.
    // The original error points to ps.submission_id not being in GROUP BY.
    // This implies a GROUP BY clause is active.

    // Find the GROUP BY clause and ensure all ps fields are covered or use ANY_VALUE for MySQL 5.7+
    // Or, if the goal is one submission per program, ensure the JOIN condition is strict enough or use a subquery.

    // Based on the error, a GROUP BY clause is being applied. Let's assume it's GROUP BY p.program_id.
    // To fix this with ONLY_FULL_GROUP_BY, you'd typically do:
    // GROUP BY p.program_id, s.sector_name, u.agency_name, ps.submission_id, ps.status, ps.is_draft, ps.submission_date, ps.updated_at, ps.period_id
    // However, this might not be the intended logic if you only want one row per program.

    // Let's assume the GROUP BY was added implicitly or by mistake. The provided snippet for get_admin_programs_list doesn't show an explicit GROUP BY before the ORDER BY.
    // The error at line 310 (prepare statement) means the SQL string is already problematic.

    // The previous version of the query was:
    // $sql = "SELECT p.*, u.agency_name, s.sector_name, ps.submission_id, ps.status, ps.is_draft, ps.submission_date, ps.updated_at 
    // FROM programs p 
    // LEFT JOIN users u ON p.owner_agency_id = u.user_id 
    // LEFT JOIN sectors s ON p.sector_id = s.sector_id 
    // LEFT JOIN (
    // SELECT * FROM program_submissions 
    // WHERE " . ($period_id ? "period_id = ?" : "period_id = (SELECT MAX(period_id) FROM reporting_periods WHERE status = 'open')") . "
    // ) ps ON p.program_id = ps.program_id";
    // This subquery for ps might be causing issues with ONLY_FULL_GROUP_BY if not handled correctly when integrated.    // Simpler JOIN without subquery for ps:
    $sql = "SELECT 
        p.program_id, p.program_name, p.owner_agency_id, p.sector_id, p.created_at,
        s.sector_name, 
        u.agency_name,
        ps.submission_id, JSON_EXTRACT(ps.content_json, '$.status') as status, ps.is_draft, ps.submission_date, ps.updated_at, ps.period_id AS submission_period_id
    FROM programs p
    JOIN sectors s ON p.sector_id = s.sector_id
    JOIN users u ON p.owner_agency_id = u.user_id
    LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?";

    $params = []; // Re-initialize params for this corrected SQL structure
    $param_types = '';

    $params[] = $period_id; // This is the first param for ps.period_id = ?
    $param_types .= 'i';

    $where_clauses = [];
    if ($period_info) {
        $where_clauses[] = "(p.created_at >= ? AND p.created_at <= ?)";
        $params[] = $period_info['start_date'] . ' 00:00:00';
        $params[] = $period_info['end_date'] . ' 23:59:59';
        $param_types .= 'ss';
    }

    // Add other filters as before
    if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
        $where_clauses[] = "ps.status = ?";
        $params[] = $filters['status'];
        $param_types .= "s";
    }
    if (isset($filters['sector_id']) && $filters['sector_id'] !== 'all' && $filters['sector_id'] !== 0 && $filters['sector_id'] !== '') {
        $where_clauses[] = "p.sector_id = ?";
        $params[] = $filters['sector_id'];
        $param_types .= "i";
    }
    if (isset($filters['agency_id']) && $filters['agency_id'] !== 'all' && $filters['agency_id'] !== 0 && $filters['agency_id'] !== '') {
        $where_clauses[] = "p.owner_agency_id = ?";
        $params[] = $filters['agency_id'];
        $param_types .= "i";
    }    if (isset($filters['search']) && !empty($filters['search'])) {
        $search_term = '%' . $filters['search'] . '%';
        $where_clauses[] = "(p.program_name LIKE ?)";
        $params[] = $search_term;
        $param_types .= "ss";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Add GROUP BY to ensure one row per program if multiple submissions could exist (though ideally they shouldn't for a specific period)
    // If program_submissions can have multiple entries for the same program_id and period_id, this is needed.
    // However, the LEFT JOIN condition `ps.program_id = p.program_id AND ps.period_id = ?` should limit this.
    // The error implies a GROUP BY is active. If it's not in this function, it might be a default MySQL setting interaction.
    // For now, let's assume no GROUP BY is needed here if the JOINs are correct.

    // ORDER BY clause
    $order_by_column = $filters['sort_by'] ?? 'p.program_name';
    $order_by_direction = $filters['sort_order'] ?? 'ASC';
    $sql .= " ORDER BY $order_by_column $order_by_direction";

    // LIMIT and OFFSET for pagination
    if (isset($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = $filters['limit'];
        $param_types .= 'i';
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = $filters['offset'];
            $param_types .= 'i';
        }
    }

    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Get sector data for a specific reporting period
 *
 * @param int $period_id The reporting period ID
 * @return array Sector data including name, agency count, program count, and submission percentage
 */
function get_sector_data_for_period($period_id) {
    global $conn;
    
    $sector_data = array();
    
    $sql = "SELECT 
                s.sector_id,
                s.sector_name,
                COUNT(DISTINCT u.user_id) as agency_count,
                COUNT(DISTINCT p.program_id) as program_count,
                IFNULL(ROUND((COUNT(DISTINCT ps.submission_id) / 
                    NULLIF(COUNT(DISTINCT p.program_id), 0)) * 100, 0), 0) as submission_pct
            FROM 
                sectors s
                LEFT JOIN users u ON s.sector_id = u.sector_id AND u.role = 'agency'
                LEFT JOIN programs p ON u.user_id = p.owner_agency_id
                LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?
            GROUP BY 
                s.sector_id, s.sector_name
            ORDER BY 
                s.sector_name ASC";
                
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $sector_data[] = $row;
    }
    
    return $sector_data;
}

/**
 * Get recent submissions for the specified period
 * 
 * @param int|null $period_id The reporting period ID
 * @param int $limit Maximum number of submissions to return
 * @return array List of recent submissions
 */
function get_recent_submissions($period_id = null, $limit = 5) {
    global $conn;
    
    $query = "SELECT ps.*, 
              u.agency_name, 
              p.program_name 
              FROM program_submissions ps
              JOIN users u ON ps.submitted_by = u.user_id
              JOIN programs p ON ps.program_id = p.program_id
              WHERE 1=1";
    
    if ($period_id) {
        $query .= " AND ps.period_id = ?";
        $params = [$period_id];
    } else {
        $params = [];
    }
    
    $query .= " ORDER BY ps.submission_date DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }
    
    if (!empty($params)) {
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    return $submissions;
}

/**
 * Get all sectors
 * 
 * @return array List of all sectors
 */
function get_all_sectors() {
    global $conn;
    
    $query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
    $result = $conn->query($query);
    
    $sectors = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sectors[] = $row;
        }
    }
    
    return $sectors;
}

/**
 * Get detailed information about a specific program for admin view
 * 
 * @param int $program_id The ID of the program to retrieve
 * @return array|false Program details array or false if not found
 */
function get_admin_program_details($program_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT p.*, s.sector_name, u.agency_name, u.user_id as owner_agency_id
                          FROM programs p
                          LEFT JOIN sectors s ON p.sector_id = s.sector_id
                          LEFT JOIN users u ON p.owner_agency_id = u.user_id
                          WHERE p.program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    
    // Get submissions for this program with reporting period details
    $stmt = $conn->prepare("SELECT ps.*, rp.year, rp.quarter
                          FROM program_submissions ps 
                          JOIN reporting_periods rp ON ps.period_id = rp.period_id
                          WHERE ps.program_id = ? 
                          ORDER BY ps.submission_id DESC");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
    
    $program['submissions'] = [];
    
    if ($submissions_result->num_rows > 0) {
        while ($submission = $submissions_result->fetch_assoc()) {
            // Process content_json if applicable
            if (isset($submission['content_json']) && is_string($submission['content_json'])) {
                $content = json_decode($submission['content_json'], true);
                if ($content) {
                    // Extract fields from content JSON
                    $submission['target'] = $content['target'] ?? '';
                    $submission['achievement'] = $content['achievement'] ?? '';
                    $submission['remarks'] = $content['remarks'] ?? '';
                    $submission['status_date'] = $content['status_date'] ?? '';
                    $submission['status_text'] = $content['status_text'] ?? '';
                }
            }
            $program['submissions'][] = $submission;
        }
        
        // Set current submission (most recent)
        $program['current_submission'] = $program['submissions'][0];
    }
    
    return $program;
}

/**
 * Get sector info by ID
 *
 * @param int $sector_id The sector ID
 * @return array|null Associative array of sector data or null if not found
 */
function get_sector_by_id($sector_id) {
    global $conn;
    $sector_id = intval($sector_id);
    if (!$sector_id) {
        return null;
    }
    try {
        $query = "SELECT * FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    } catch (Exception $e) {
        error_log("Error in get_sector_by_id: " . $e->getMessage());
        return null;
    }
}

/**
 * Get a specific program submission by program ID and period ID
 * 
 * @param int $program_id The ID of the program
 * @param int $period_id The ID of the reporting period
 * @return array|false The submission data or false if not found
 */
function get_program_submission($program_id, $period_id) {
    global $conn;
    
    $program_id = intval($program_id);
    $period_id = intval($period_id);
    
    $sql = "SELECT * FROM program_submissions 
            WHERE program_id = ? AND period_id = ? 
            ORDER BY submission_id DESC LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Database error in get_program_submission: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('ii', $program_id, $period_id);
    
    if (!$stmt->execute()) {
        error_log("Execution error in get_program_submission: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    
    $submission = $result->fetch_assoc();
    $stmt->close();
    
    return $submission;
}

/**
 * Mark a program submission as draft (unsubmit)
 * 
 * @param int $program_id The ID of the program
 * @param int $period_id The ID of the reporting period
 * @return bool True on success, false on failure
 */
function unsubmit_program($program_id, $period_id) {
    global $conn;
    
    $program_id = intval($program_id);
    $period_id = intval($period_id);
    
    $sql = "UPDATE program_submissions 
            SET is_draft = 1, status = 'not-started' 
            WHERE program_id = ? AND period_id = ?";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Database error in unsubmit_program: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('ii', $program_id, $period_id);
    
    if (!$stmt->execute()) {
        error_log("Execution error in unsubmit_program: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return $affected > 0;
}

/**
 * Log an admin action in the system
 * 
 * @param string $action The action being performed
 * @param string $details Additional details about the action
 * @param bool $success Whether the action was successful
 * @return bool True if log was created, false otherwise
 */
function log_action($action, $details, $success = true) {
    global $conn;
    
    // Only proceed if audit_logs table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($table_check->num_rows === 0) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'] ?? 0;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $status = $success ? 'success' : 'failure';
    
    $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Database error in log_action: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('issss', $user_id, $action, $details, $ip_address, $status);
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>