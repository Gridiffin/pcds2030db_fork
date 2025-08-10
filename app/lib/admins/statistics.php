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
        'agency_programs' => []
    ];
    
    // Get current period
    $current_period = get_current_reporting_period();
    $period_id = $current_period['period_id'] ?? null;
      // Get counts (including both regular agencies and focal agencies)
    $query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role IN ('agency', 'focal')) AS total_agencies,
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
                    (SELECT COUNT(*) FROM programs p WHERE p.agency_id = u.agency_id) AS agency_programs,
                    (SELECT COUNT(*) FROM program_submissions ps 
                     JOIN programs p ON ps.program_id = p.program_id 
                     WHERE p.agency_id = u.agency_id AND ps.period_id = ?) AS submitted_programs
                  FROM users u
                  WHERE u.role IN ('agency', 'focal')";
        
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
        
        // Removed status distribution query and processing as status column is deleted
        $stats['program_status'] = [
            'labels' => [],
            'data' => [],
            'backgroundColor' => []
        ];
    }
    
    // Get programs by agency
    $query = "SELECT a.agency_name, COUNT(p.program_id) as program_count
              FROM agency a
              LEFT JOIN programs p ON a.agency_id = p.agency_id
              GROUP BY a.agency_id
              ORDER BY program_count DESC";
    $result = $conn->query($query);
    $agency_data = [
        'labels' => [],
        'data' => [],
        'backgroundColor' => [
            '#8591a4', '#A49885', '#607b9b', '#b3a996', '#4f616f'
        ]
    ];
    while ($row = $result->fetch_assoc()) {
        $agency_data['labels'][] = $row['agency_name'];
        $agency_data['data'][] = $row['program_count'];
    }
    $stats['agency_programs'] = $agency_data;
    unset($stats['sector_programs']);
    
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
        'monthly_target_achieved_programs' => 0,
        'total_programs' => 0,
        'completion_percentage' => 0
    ];
    // Derive period window (start/end) for user activity metric
    $period_start = null;
    $period_end = null;
    if ($period_id) {
        $period_stmt = $conn->prepare("SELECT start_date, end_date FROM reporting_periods WHERE period_id = ?");
        if ($period_stmt) {
            $period_stmt->bind_param('i', $period_id);
            $period_stmt->execute();
            $period_res = $period_stmt->get_result();
            if ($period_res && $p = $period_res->fetch_assoc()) {
                $period_start = $p['start_date'] . ' 00:00:00';
                $period_end = $p['end_date'] . ' 23:59:59';
            }
            $period_stmt->close();
        }
    }

    // Denominator: active agency/focal users whose agency has programs
    $den_sql = "SELECT COUNT(*) AS total
                FROM users u
                WHERE u.role IN ('agency','focal')
                  AND u.is_active = 1
                  AND EXISTS (SELECT 1 FROM programs p WHERE p.agency_id = u.agency_id)";
    $den_result = $conn->query($den_sql);
    if ($den_result && $den_row = $den_result->fetch_assoc()) {
        $stats['total_agencies'] = (int)$den_row['total'];
    }

    // Numerator: distinct active users who created/updated/finalized submissions within period window
    // Support environments that may have only one of the audit tables
    if ($period_start && $period_end) {
        $has_audit_logs = false; // plural
        $has_audit_log = false;  // singular
        try {
            $r1 = $conn->query("SHOW TABLES LIKE 'audit_logs'");
            $has_audit_logs = $r1 && $r1->num_rows > 0;
        } catch (Exception $e) {}
        try {
            $r2 = $conn->query("SHOW TABLES LIKE 'audit_log'");
            $has_audit_log = $r2 && $r2->num_rows > 0;
        } catch (Exception $e) {}

        $inner_parts = [];
        $param_values = [];
        $param_types = '';

        if ($has_audit_logs) {
            $inner_parts[] = "SELECT al.user_id
                              FROM audit_logs al
                              JOIN users u ON u.user_id = al.user_id
                              WHERE al.action IN ('create_submission','update_submission')
                                AND al.created_at BETWEEN ? AND ?
                                AND u.role IN ('agency','focal') AND u.is_active = 1";
            $param_values[] = $period_start; $param_values[] = $period_end;
            $param_types .= 'ss';
        }
        if ($has_audit_log) {
            $inner_parts[] = "SELECT al2.user_id
                              FROM audit_log al2
                              JOIN users u2 ON u2.user_id = al2.user_id
                              WHERE al2.action = 'finalize_submission'
                                AND al2.created_at BETWEEN ? AND ?
                                AND u2.role IN ('agency','focal') AND u2.is_active = 1";
            $param_values[] = $period_start; $param_values[] = $period_end;
            $param_types .= 'ss';
        }

        if (!empty($inner_parts)) {
            $num_sql = "SELECT COUNT(DISTINCT t.user_id) AS total_users FROM (" . implode("\nUNION\n", $inner_parts) . ") t";
            $num_stmt = $conn->prepare($num_sql);
            if ($num_stmt) {
                if (!empty($param_values)) {
                    $num_stmt->bind_param($param_types, ...$param_values);
                }
                $num_stmt->execute();
                $num_res = $num_stmt->get_result();
                if ($num_res && $num_row = $num_res->fetch_assoc()) {
                    $stats['agencies_reported'] = (int)$num_row['total_users'];
                }
                $num_stmt->close();
            }
        }
    }
    
    // Programs On Track: count by programs.rating column (enum 'on_track_for_year')
    $on_track_value = 'on_track_for_year';
    $onTrackStmt = $conn->prepare("SELECT COUNT(*) AS total FROM programs WHERE rating = ?");
    if ($onTrackStmt) {
        $onTrackStmt->bind_param('s', $on_track_value);
        $onTrackStmt->execute();
        $onTrackResult = $onTrackStmt->get_result();
        if ($onTrackResult && $onTrackRow = $onTrackResult->fetch_assoc()) {
            $stats['on_track_programs'] = (int)$onTrackRow['total'];
        }
        $onTrackStmt->close();
    }
    // Delayed programs: count by programs.rating column (enum 'severe_delay')
    $delayed_value = 'severe_delay';
    $delayedStmt = $conn->prepare("SELECT COUNT(*) AS total FROM programs WHERE rating = ?");
    if ($delayedStmt) {
        $delayedStmt->bind_param('s', $delayed_value);
        $delayedStmt->execute();
        $delayedResult = $delayedStmt->get_result();
        if ($delayedResult && $delayedRow = $delayedResult->fetch_assoc()) {
            $stats['delayed_programs'] = (int)$delayedRow['total'];
        }
        $delayedStmt->close();
    }
    
    // Get total programs
    $query = "SELECT COUNT(*) as total FROM programs";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_programs'] = $row['total'];
    }
    
    // Count programs with "monthly target achieved" rating
    $target_achieved_value = 'monthly_target_achieved';
    $targetAchievedStmt = $conn->prepare("SELECT COUNT(*) AS total FROM programs WHERE rating = ?");
    if ($targetAchievedStmt) {
        $targetAchievedStmt->bind_param('s', $target_achieved_value);
        $targetAchievedStmt->execute();
        $targetAchievedResult = $targetAchievedStmt->get_result();
        if ($targetAchievedResult && $targetAchievedRow = $targetAchievedResult->fetch_assoc()) {
            $stats['monthly_target_achieved_programs'] = (int)$targetAchievedRow['total'];
        }
        $targetAchievedStmt->close();
    }
    
    // Calculate completion percentage (for backward compatibility if needed elsewhere)
    if ($stats['total_programs'] > 0) {
        $stats['completion_percentage'] = round(($stats['monthly_target_achieved_programs'] / $stats['total_programs']) * 100);
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
        $period_result = $period_stmt->get_result();        if ($period_result->num_rows > 0) {
            $period_info = $period_result->fetch_assoc();
        }
    }

    // Construct the main query with subquery to get latest submission per program
    $sql = "SELECT 
                p.program_id, 
                ANY_VALUE(p.program_name) as program_name, 
                ANY_VALUE(p.program_number) as program_number, 
                ANY_VALUE(p.agency_id) as agency_id, 
                ANY_VALUE(p.created_at) as created_at,
                ANY_VALUE(p.initiative_id) as initiative_id, 
                ANY_VALUE(i.initiative_name) as initiative_name, 
                ANY_VALUE(i.initiative_number) as initiative_number,
                ANY_VALUE(a.agency_name) as agency_name, 
                ANY_VALUE(creator.fullname) as creator_name,
                ANY_VALUE(latest_sub.submission_id) as submission_id, 
                ANY_VALUE(latest_sub.is_draft) as is_draft, 
                ANY_VALUE(latest_sub.submitted_at) as submitted_at, 
                ANY_VALUE(latest_sub.updated_at) as updated_at, 
                ANY_VALUE(latest_sub.period_id) AS submission_period_id,
                COALESCE(
                  MAX(CASE WHEN pt.status_indicator = 'delayed' THEN 'delayed' END),
                  MAX(CASE WHEN pt.status_indicator = 'in_progress' THEN 'in_progress' END),
                  MAX(CASE WHEN pt.status_indicator = 'completed' THEN 'completed' END),
                  MAX(CASE WHEN pt.status_indicator = 'not_started' THEN 'not_started' END),
                  'not_started'
                ) as rating
            FROM programs p
            LEFT JOIN agency a ON p.agency_id = a.agency_id
            LEFT JOIN users creator ON p.created_by = creator.user_id
            LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    WHERE period_id = ?
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id
            LEFT JOIN program_targets pt ON latest_sub.submission_id = pt.submission_id AND pt.is_deleted = 0";
    
    $params = [$period_id];
    $param_types = 'i';
    
    // Apply filters
    if (!empty($filters)) {
        // Remove any reference to ps.status (column deleted)
        // Use rating from JSON content instead
        if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
            $conditions[] = "COALESCE(latest_sub.status_indicator, 'not-started') = ?";
            $params[] = $filters['status'];
            $param_types .= 's';
        }
        
        if (isset($filters['agency_id']) && $filters['agency_id'] !== 'all' && $filters['agency_id'] !== 0 && $filters['agency_id'] !== '') {
            $conditions[] = "p.agency_id = ?";
            $params[] = $filters['agency_id'];
            $param_types .= "i";
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search_term = '%' . $filters['search'] . '%';
            $conditions[] = "p.program_name LIKE ?";
            $params[] = $search_term;
            $param_types .= "s";
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
    
    // After building $sql and appending WHERE clause (if any), append GROUP BY p.program_id, then ORDER BY, then LIMIT/OFFSET.
    // Remove GROUP BY from the main query string if it was already there.
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
                p.program_id, p.program_name, p.program_number, p.agency_id, p.created_at,
                p.initiative_id, i.initiative_name, i.initiative_number,
                a.agency_name,
                latest_sub.submission_id, latest_sub.is_draft, latest_sub.submitted_at, latest_sub.updated_at, latest_sub.period_id AS submission_period_id,
                COALESCE(
                  MAX(CASE WHEN pt.status_indicator = 'delayed' THEN 'delayed' END),
                  MAX(CASE WHEN pt.status_indicator = 'in_progress' THEN 'in_progress' END),
                  MAX(CASE WHEN pt.status_indicator = 'completed' THEN 'completed' END),
                  MAX(CASE WHEN pt.status_indicator = 'not_started' THEN 'not_started' END),
                  'not_started'
                ) as rating
            FROM programs p
            JOIN users u ON p.agency_id = u.agency_id
            LEFT JOIN agency a ON u.agency_id = a.agency_id
            LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    WHERE period_id = ?
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id
            LEFT JOIN program_targets pt ON latest_sub.submission_id = pt.submission_id AND pt.is_deleted = 0";


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
    }    // Add other filters as before
    if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
        $where_clauses[] = "COALESCE(latest_sub.status_indicator, 'not-started') = ?";
        $params[] = $filters['status'];
        $param_types .= "s";
    }
    if (isset($filters['agency_id']) && $filters['agency_id'] !== 'all' && $filters['agency_id'] !== 0 && $filters['agency_id'] !== '') {
        $where_clauses[] = "p.agency_id = ?";
        $params[] = $filters['agency_id'];
        $param_types .= "i";
    }
    if (isset($filters['search']) && !empty($filters['search'])) {
        $search_term = '%' . $filters['search'] . '%';
        $where_clauses[] = "(p.program_name LIKE ?)";
        $params[] = $search_term;
        $param_types .= "s";
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
    $sql .= " GROUP BY p.program_id ORDER BY $order_by_column $order_by_direction";

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
 * Get agency data for a specific reporting period
 *
 * @param int $period_id The reporting period ID
 * @return array Agency data including name, user count, program count, and submission percentage
 */
function get_agency_data_for_period($period_id) {
    global $conn;
    $agency_data = array();
    $sql = "SELECT 
                a.agency_id,
                a.agency_name,
                COUNT(DISTINCT u.user_id) as user_count,
                COUNT(DISTINCT p.program_id) as program_count,
                IFNULL(ROUND((COUNT(DISTINCT CASE WHEN ps.submission_id IS NOT NULL THEN ps.program_id END) / 
                    NULLIF(COUNT(DISTINCT p.program_id), 0)) * 100, 0), 0) as submission_pct
            FROM 
                agency a
                LEFT JOIN users u ON a.agency_id = u.agency_id AND u.role IN ('agency', 'focal')
                LEFT JOIN programs p ON a.agency_id = p.agency_id
                LEFT JOIN (
                    SELECT ps1.program_id, ps1.submission_id
                    FROM program_submissions ps1
                    INNER JOIN (
                        SELECT program_id, MAX(submission_id) as max_submission_id
                        FROM program_submissions 
                        WHERE period_id = ?
                        GROUP BY program_id
                    ) latest ON ps1.program_id = latest.program_id AND ps1.submission_id = latest.max_submission_id
                    WHERE ps1.period_id = ?
                ) ps ON p.program_id = ps.program_id
            GROUP BY 
                a.agency_id, a.agency_name
            ORDER BY 
                a.agency_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $period_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $agency_data[] = $row;
    }
    return $agency_data;
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
              a.agency_name, 
              p.program_name 
              FROM program_submissions ps
              JOIN users u ON ps.submitted_by = u.user_id
              LEFT JOIN agency a ON u.agency_id = a.agency_id
              JOIN programs p ON ps.program_id = p.program_id
              WHERE 1=1";
    
    if ($period_id) {
        $query .= " AND ps.period_id = ?";
        $params = [$period_id];
    } else {
        $params = [];
    }
    
    $query .= " ORDER BY ps.updated_at DESC LIMIT ?";
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
        // Remove status field from each submission as status column is deleted
        if (isset($row['status'])) {
            unset($row['status']);
        }
        $submissions[] = $row;
    }
    
    return $submissions;
}

/**
 * Get detailed information about a specific program for admin view
 * 
 * @param int $program_id The ID of the program to retrieve
 * @return array|false Program details array or false if not found
 */
function get_admin_program_details($program_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT p.*, a.agency_name, u.user_id as assigned_user_id,
                                  i.initiative_id, i.initiative_name, i.initiative_number, 
                                  i.initiative_description, i.start_date as initiative_start_date, 
                                  i.end_date as initiative_end_date
                          FROM programs p
                          LEFT JOIN users u ON p.agency_id = u.agency_id
                          LEFT JOIN agency a ON u.agency_id = a.agency_id
                          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                          WHERE p.program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    
    // Set is_assigned field based on program data
    // A program is considered "assigned" if it has admin-specific settings or was created through assignment
    $program['is_assigned'] = !empty($program['edit_permissions']) ? 1 : 0;
    
    // Get submissions for this program with reporting period details
    $stmt = $conn->prepare("SELECT ps.*, rp.year, rp.period_type, rp.period_number
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
            WHERE program_id = ? AND period_id = ? AND is_deleted = 0
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

    // Fetch the current content_json
    $sql_select = "SELECT submission_id, content_json FROM program_submissions WHERE program_id = ? AND period_id = ? AND is_deleted = 0 ORDER BY submission_id DESC LIMIT 1";
    $stmt = $conn->prepare($sql_select);
    if (!$stmt) {
        error_log("Database error in unsubmit_program (select): " . $conn->error);
        return false;
    }
    $stmt->bind_param('ii', $program_id, $period_id);
    if (!$stmt->execute()) {
        error_log("Execution error in unsubmit_program (select): " . $stmt->error);
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    $row = $result->fetch_assoc();
    $submission_id = $row['submission_id'];
    $content_json = $row['content_json'];
    $stmt->close();

    // Update the rating/status in content_json
    $content = json_decode($content_json, true) ?: [];
    $content['rating'] = 'not-started';
    $new_content_json = json_encode($content);

    // Update is_draft and content_json
    $sql_update = "UPDATE program_submissions SET is_draft = 1, content_json = ? WHERE submission_id = ?";
    $stmt = $conn->prepare($sql_update);
    if (!$stmt) {
        error_log("Database error in unsubmit_program (update): " . $conn->error);
        return false;
    }
    $stmt->bind_param('si', $new_content_json, $submission_id);
    if (!$stmt->execute()) {
        error_log("Execution error in unsubmit_program (update): " . $stmt->error);
        $stmt->close();
        return false;
    }
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

/**
 * Enhanced unsubmit program with cascading logic awareness
 * This function reverts the latest submission to draft and handles multi-period scenarios
 * 
 * @param int $program_id The ID of the program
 * @param int $period_id The ID of the reporting period
 * @param bool $cascade_revert Whether to revert all submissions after this period to draft (default: false)
 * @return array Result array with success status and details
 */
function enhanced_unsubmit_program($program_id, $period_id, $cascade_revert = false) {
    global $conn;
    $program_id = intval($program_id);
    $period_id = intval($period_id);
    $affected_periods = [];
    
    // Start transaction for consistency
    $conn->begin_transaction();
    
    try {
        // First, get the submission for the specified period
        $sql_select = "SELECT submission_id, content_json, is_draft FROM program_submissions 
                      WHERE program_id = ? AND period_id = ? AND is_deleted = 0
                      ORDER BY submission_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql_select);
        if (!$stmt) {
            throw new Exception("Database error in enhanced_unsubmit_program (select): " . $conn->error);
        }
        
        $stmt->bind_param('ii', $program_id, $period_id);
        if (!$stmt->execute()) {
            throw new Exception("Execution error in enhanced_unsubmit_program (select): " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("No submission found for Program ID: {$program_id}, Period ID: {$period_id}");
        }
        
        $row = $result->fetch_assoc();
        $submission_id = $row['submission_id'];
        $content_json = $row['content_json'];
        $is_already_draft = $row['is_draft'];
        $stmt->close();
        
        // If already draft, no need to proceed unless cascading
        if ($is_already_draft && !$cascade_revert) {
            $conn->rollback();
            return [
                'success' => true,
                'message' => 'Submission is already in draft status',
                'affected_periods' => []
            ];
        }
        
        // Update the rating/status in content_json to 'not-started'
        $content = json_decode($content_json, true) ?: [];
        $content['rating'] = 'not-started';
        $new_content_json = json_encode($content);
        
        // Revert the specified period to draft
        $sql_update = "UPDATE program_submissions 
                      SET is_draft = 1, content_json = ? 
                      WHERE submission_id = ?";
        $stmt = $conn->prepare($sql_update);
        if (!$stmt) {
            throw new Exception("Database error in enhanced_unsubmit_program (update): " . $conn->error);
        }
        
        $stmt->bind_param('si', $new_content_json, $submission_id);
        if (!$stmt->execute()) {
            throw new Exception("Execution error in enhanced_unsubmit_program (update): " . $stmt->error);
        }
        
        if ($stmt->affected_rows > 0) {
            $affected_periods[] = $period_id;
        }
        $stmt->close();
        
        // If cascade_revert is true, also revert all other submissions for this program
        if ($cascade_revert) {
            $cascade_sql = "UPDATE program_submissions 
                           SET is_draft = 1 
                           WHERE program_id = ? 
                           AND period_id != ? 
                           AND is_draft = 0";
            $cascade_stmt = $conn->prepare($cascade_sql);
            if (!$cascade_stmt) {
                throw new Exception("Database error in enhanced_unsubmit_program (cascade): " . $conn->error);
            }
            
            $cascade_stmt->bind_param('ii', $program_id, $period_id);
            if (!$cascade_stmt->execute()) {
                throw new Exception("Execution error in enhanced_unsubmit_program (cascade): " . $cascade_stmt->error);
            }
            
            // Get the periods that were affected by cascade
            if ($cascade_stmt->affected_rows > 0) {
                $affected_periods_sql = "SELECT DISTINCT period_id FROM program_submissions 
                                        WHERE program_id = ? AND period_id != ? AND is_draft = 1";
                $affected_stmt = $conn->prepare($affected_periods_sql);
                $affected_stmt->bind_param('ii', $program_id, $period_id);
                $affected_stmt->execute();
                $affected_result = $affected_stmt->get_result();
                
                while ($affected_row = $affected_result->fetch_assoc()) {
                    if (!in_array($affected_row['period_id'], $affected_periods)) {
                        $affected_periods[] = $affected_row['period_id'];
                    }
                }
                $affected_stmt->close();
            }
            $cascade_stmt->close();
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => count($affected_periods) > 1 ? 
                        'Program unsubmitted with cascading effect on ' . count($affected_periods) . ' periods' : 
                        'Program successfully unsubmitted',
            'affected_periods' => $affected_periods
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Enhanced unsubmit error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'affected_periods' => []
        ];
    }
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
