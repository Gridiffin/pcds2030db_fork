<?php
/**
 * Agency Statistics Functions
 * 
 * Contains functions for retrieving and calculating agency statistics
 * @version 2.0.0
 */

// Core dependencies - using absolute paths for reliability
require_once dirname(__DIR__) . '/db_connect.php';
require_once dirname(__DIR__) . '/session.php';
require_once dirname(__DIR__) . '/functions.php';

/**
 * Check if system uses content_json schema for program submissions
 * 
 * @return bool True if content_json schema is used, false otherwise
 */
function has_content_json_schema() {
    global $conn;
    
    $result = $conn->query("SHOW COLUMNS FROM program_submissions LIKE 'content_json'");
    return ($result && $result->num_rows > 0);
}

/**
 * Get all programs, optionally filtered by period
 * 
 * This function retrieves programs for the agency view
 * 
 * @param int $period_id Optional period ID to filter by specific reporting period
 * @param array $filters Optional filters to apply
 * @return array List of programs
 */
function get_all_sectors_programs($period_id = null, $filters = []) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Get current agency
    $agency_id = $_SESSION['user_id'];
    
    // Initialize query parts
    $has_content_json = has_content_json_schema();
    $filterConditions = [];
    $filterParams = [];
    $filterTypes = "";
    
    // First, get program_ids that have submissions in the requested period
    if ($period_id) {
        $programIdsQuery = "SELECT DISTINCT program_id FROM program_submissions WHERE is_draft = 0 AND ";
        
        // Handle comma-separated period IDs for half-yearly mode
        if (strpos($period_id, ',') !== false) {
            $period_ids = array_map('intval', explode(',', $period_id));
            $programIdsQuery .= "period_id IN (" . implode(',', $period_ids) . ")";
        } else {
            $programIdsQuery .= "period_id = " . intval($period_id);
        }
        
        $programIdsResult = $conn->query($programIdsQuery);
        
        $programIds = [];
        if ($programIdsResult && $programIdsResult->num_rows > 0) {
            while ($row = $programIdsResult->fetch_assoc()) {
                $programIds[] = $row['program_id'];
            }
        }
        
        // If no programs found for this period, return empty array
        if (empty($programIds)) {
            return [];
        }
    }
    
    // Base query
    $query = "SELECT 
                p.program_id, 
                p.program_name, 
                p.start_date, 
                p.end_date,
                p.created_at,
                p.updated_at,
                p.users_assigned AS agency_id,
                a.agency_name";
    
    // Add content json field or target/achievement fields based on schema
    if ($has_content_json) {
        $query .= ", ps.content_json";
    } else {
        $query .= ", JSON_EXTRACT(ps.content_json, '$.target') as target, " .
                  "JSON_EXTRACT(ps.content_json, '$.achievement') as achievement, " .
                  "JSON_EXTRACT(ps.content_json, '$.status_date') as status_date, " .
                  "JSON_EXTRACT(ps.content_json, '$.status_text') as status_text";
    }
    
    // Only add is_draft since status has been removed
    $query .= ", ps.is_draft
              FROM programs p
              JOIN users u ON p.users_assigned = u.user_id
              JOIN agency a ON u.agency_id = a.agency_id
              JOIN (";
    
    // Use INNER JOIN to only include programs with submissions in the selected period
    $subquery = "SELECT ps1.program_id, ps1.is_draft";
    
    if ($has_content_json) {
        $subquery .= ", ps1.content_json";
    } else {
        $subquery .= ", ps1.target, ps1.achievement, ps1.status_date, ps1.status_text";
    }
    
    $subquery .= " FROM program_submissions ps1
                   LEFT JOIN program_submissions ps2 
                   ON ps1.program_id = ps2.program_id";
    
    // Filter by the specific period
    if ($period_id) {
        // Handle comma-separated period IDs for half-yearly mode
        if (strpos($period_id, ',') !== false) {
            $period_ids = array_map('intval', explode(',', $period_id));
            $subquery .= " AND ps1.period_id IN (" . implode(',', $period_ids) . ")";
            $subquery .= " AND (ps2.period_id IN (" . implode(',', $period_ids) . ")";
        } else {
            $period_int = intval($period_id);
            $subquery .= " AND ps1.period_id = " . $period_int;
            $subquery .= " AND (ps2.period_id = " . $period_int;
        }
        
        // Get the latest submission for each program in this period
        $subquery .= " AND ps1.submission_id < ps2.submission_id)";
        $subquery .= " WHERE ps2.submission_id IS NULL";
        
        // Only include programs with submissions in this period
        $subquery .= " AND ps1.program_id IN (" . implode(',', $programIds) . ")";
    } else {
        // If no period specified, use original logic to find latest submission overall
        $subquery .= " AND (ps1.submission_id < ps2.submission_id OR (ps1.submission_id = ps2.submission_id AND ps1.period_id < ps2.period_id))
                       WHERE ps2.submission_id IS NULL";
    }
    
    $query .= $subquery . ") ps ON p.program_id = ps.program_id";
    
    // Only show finalized submissions
    $filterConditions[] = "ps.is_draft = 0";
    
    // Apply additional filters
    if (!empty($filters)) {
        // Filter by agency_id
        if (isset($filters['agency_id']) && $filters['agency_id']) {
            $filterConditions[] = "p.users_assigned = ?";
            $filterParams[] = $filters['agency_id'];
            $filterTypes .= "i";
        }
          // Filter by status - using JSON content instead since status column has been removed
        if (isset($filters['status']) && $filters['status']) {
            $filterConditions[] = "JSON_EXTRACT(ps.content_json, '$.status') = ?";
            $filterParams[] = $filters['status'];
            $filterTypes .= "s";
        }
          // Filter by search term
        if (isset($filters['search']) && $filters['search']) {
            $searchTerm = '%' . $filters['search'] . '%';
            $filterConditions[] = "(p.program_name LIKE ?)";
            $filterParams[] = $searchTerm;
            $filterTypes .= "s";
        }
    }
    
    // Add filter conditions to query
    if (!empty($filterConditions)) {
        $query .= " WHERE " . implode(" AND ", $filterConditions);
    }
      // Finalize query
    $query .= " GROUP BY p.program_id, p.program_name, p.start_date, p.end_date, 
                p.created_at, p.updated_at, p.users_assigned, a.agency_name";
    
    // Add additional GROUP BY fields based on schema
    if ($has_content_json) {
        $query .= ", ps.content_json";
    } else {
        $query .= ", ps.target, ps.achievement, ps.status_date, ps.status_text";
    }
    
    $query .= ", ps.is_draft ORDER BY p.created_at DESC";
    
    // Execute query
    try {
        $stmt = $conn->prepare($query);
        
        // Bind parameters if there are any
        if (!empty($filterParams)) {
            // Combine parameters and types
            $stmt->bind_param($filterTypes, ...$filterParams);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            // Process content_json field if needed
            $programs[] = process_content_json($row);
        }
        
        return $programs;
    } catch (Exception $e) {
        error_log("Error in get_all_sectors_programs: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}



/**
 * Get submission status for an agency
 * 
 * Retrieves and calculates submission statistics for agency programs
 * 
 * @param int $agency_id The agency ID
 * @param int|null $period_id Optional period ID to filter by
 * @return array Array containing submission statistics and status counts
 */
function get_agency_submission_status($agency_id, $period_id = null) {
    global $conn;
    
    try {
        // Initialize return structure
        $stats = [
            'total_programs' => 0,
            'programs_submitted' => 0,
            'draft_count' => 0,
            'not_submitted' => 0,
            'program_status' => [
                'on-track' => 0,
                'delayed' => 0,
                'completed' => 0,
                'not-started' => 0
            ]
        ];        // Get total programs for agency (including those with and without submissions for the period)
        if ($period_id) {
            // Count all programs owned by agency, regardless of submission status for the period
            $query = "SELECT COUNT(*) as total FROM programs WHERE users_assigned = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $agency_id);
        } else {
            $query = "SELECT COUNT(*) as total FROM programs WHERE users_assigned = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $agency_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_programs'] = $result->fetch_assoc()['total'];

        if ($stats['total_programs'] === 0) {
            return $stats;
        }        // Get submission status counts with proper rating extraction - Fixed to avoid duplicates
        $status_query = "SELECT 
            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p.rating, '$.rating')), 'not-started') as rating,
            COUNT(DISTINCT p.program_id) as count,
            SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
            SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
            FROM programs p 
            LEFT JOIN (
                SELECT ps1.program_id, ps1.is_draft, ps1.content_json
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    WHERE (period_id = ? OR ? IS NULL)
                    GROUP BY program_id
                ) latest ON ps1.program_id = latest.program_id AND ps1.submission_id = latest.max_submission_id
                WHERE (ps1.period_id = ? OR ? IS NULL)
            ) ps ON p.program_id = ps.program_id
            WHERE p.users_assigned = ?
            GROUP BY COALESCE(JSON_UNQUOTE(JSON_EXTRACT(p.rating, '$.rating')), 'not-started')";

        $stmt = $conn->prepare($status_query);
        $stmt->bind_param("iiiii", $period_id, $period_id, $period_id, $period_id, $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $submitted_total = 0;
        $draft_total = 0;        while ($row = $result->fetch_assoc()) {
            $rating = strtolower($row['rating'] ?? 'not-started');
            $submitted = $row['submitted_count'] ?? 0;
            $draft = $row['draft_count'] ?? 0;

            // Map rating to our categories (only count submitted/finalized programs)
            if (in_array($rating, ['on-track', 'on-track-yearly'])) {
                $stats['program_status']['on-track'] += $submitted;
            } elseif (in_array($rating, ['delayed', 'severe-delay'])) {
                $stats['program_status']['delayed'] += $submitted;
            } elseif (in_array($rating, ['completed', 'target-achieved'])) {
                $stats['program_status']['completed'] += $submitted;
            } else {
                $stats['program_status']['not-started'] += $submitted;
            }

            $submitted_total += $submitted;
            $draft_total += $draft;
        }

        // Update summary statistics
        $stats['programs_submitted'] = $submitted_total;
        $stats['draft_count'] = $draft_total;
        $stats['not_submitted'] = $stats['total_programs'] - ($submitted_total + $draft_total);

        return $stats;
    } catch (Exception $e) {
        error_log("Error in get_agency_submission_status: " . $e->getMessage());
        throw new Exception("Failed to retrieve submission status: " . $e->getMessage());
    }
}
?>
