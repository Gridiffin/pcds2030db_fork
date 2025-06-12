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
 * Get sector name by ID
 * 
 * @param int $sector_id The sector ID
 * @return string The sector name or 'Unknown Sector' if not found
 */
function get_sector_name($sector_id) {
    global $conn;
    
    $sector_id = intval($sector_id);
    if (!$sector_id) {
        return 'Unknown Sector';
    }
    
    try {
        $query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['sector_name'];
        } else {
            return 'Unknown Sector';
        }
    } catch (Exception $e) {
        error_log("Error in get_sector_name: " . $e->getMessage());
        return 'Unknown Sector';
    }
}

/**
 * Get all sectors
 * 
 * @return array List of all sectors
 */
function get_all_sectors() {
    global $conn;
    
    $sectors = [];
    
    try {
        $query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sectors[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error in get_all_sectors: " . $e->getMessage());
    }
    
    return $sectors;
}

/**
 * Get all programs from all sectors, optionally filtered by period
 * 
 * This function retrieves programs from all sectors, for the agency view
 * 
 * @param int $period_id Optional period ID to filter by specific reporting period
 * @param array $filters Optional filters to apply
 * @return array List of programs from all sectors
 */
function get_all_sectors_programs($period_id = null, $filters = []) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Get current agency's sector for highlighting
    $agency_id = $_SESSION['user_id'];
    $current_sector_id = $_SESSION['sector_id'] ?? 0;
    
    // Initialize query parts
    $has_content_json = has_content_json_schema();
    $filterConditions = [];
    $filterParams = [];
    $filterTypes = "";
      // Base query
    $query = "SELECT 
                p.program_id, 
                p.program_name, 
                p.start_date, 
                p.end_date,
                p.created_at,
                p.updated_at,
                p.sector_id,
                p.owner_agency_id AS agency_id,
                s.sector_name,
                u.agency_name";
    
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
              JOIN sectors s ON p.sector_id = s.sector_id
              JOIN users u ON p.owner_agency_id = u.user_id
              LEFT JOIN (";
    
    // Changed to LEFT JOIN to include programs with no submissions
      // Subquery to get latest submission for each program 
    $subquery = "SELECT ps1.program_id, ps1.is_draft";
    
    if ($has_content_json) {
        $subquery .= ", ps1.content_json";
    } else {
        $subquery .= ", ps1.target, ps1.achievement, ps1.status_date, ps1.status_text";
    }
    
    $subquery .= " FROM program_submissions ps1
                   LEFT JOIN program_submissions ps2 
                   ON ps1.program_id = ps2.program_id 
                   AND (ps1.submission_id < ps2.submission_id OR (ps1.submission_id = ps2.submission_id AND ps1.period_id < ps2.period_id))
                   WHERE ps2.submission_id IS NULL";
    
    // Add period filter if specified
    if ($period_id) {
        $subquery .= " AND ps1.period_id = " . intval($period_id);
    }
    
    $query .= $subquery . ") ps ON p.program_id = ps.program_id";
    
    // Start with base condition: only show finalized submissions or no submissions
    $filterConditions[] = "(ps.is_draft = 0 OR ps.is_draft IS NULL)";
    
    // Apply additional filters
    if (!empty($filters)) {
        // Filter by sector_id
        if (isset($filters['sector_id']) && $filters['sector_id']) {
            $filterConditions[] = "p.sector_id = ?";
            $filterParams[] = $filters['sector_id'];
            $filterTypes .= "i";
        }
        
        // Filter by agency_id
        if (isset($filters['agency_id']) && $filters['agency_id']) {
            $filterConditions[] = "p.owner_agency_id = ?";
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
                p.created_at, p.updated_at, p.sector_id, p.owner_agency_id, s.sector_name, u.agency_name";
    
    // Add additional GROUP BY fields based on schema
    if ($has_content_json) {
        $query .= ", ps.content_json";
    } else {
        $query .= ", ps.target, ps.achievement, ps.status_date, ps.status_text";
    }
    
    $query .= ", ps.is_draft ORDER BY (p.sector_id = ?) DESC, p.created_at DESC";
    $filterParams[] = $current_sector_id;
    $filterTypes .= "i";
    
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
        ];

        // Get total programs for agency (filtered by period if provided)
        if ($period_id) {
            $query = "SELECT COUNT(DISTINCT p.program_id) as total
                      FROM programs p
                      INNER JOIN program_submissions ps ON p.program_id = ps.program_id
                      WHERE p.owner_agency_id = ? AND ps.period_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $agency_id, $period_id);
        } else {
            $query = "SELECT COUNT(*) as total FROM programs WHERE owner_agency_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $agency_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_programs'] = $result->fetch_assoc()['total'];

        if ($stats['total_programs'] === 0) {
            return $stats;
        }        // Get submission status counts with proper rating extraction
        $status_query = "SELECT 
            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating,
            COUNT(*) as count,
            SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
            SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
            FROM programs p 
            LEFT JOIN (
                SELECT program_id, is_draft, content_json
                FROM program_submissions ps1
                WHERE (period_id = ? OR ? IS NULL)
                AND NOT EXISTS (
                    SELECT 1 FROM program_submissions ps2
                    WHERE ps2.program_id = ps1.program_id
                    AND ps2.submission_id > ps1.submission_id
                )
            ) ps ON p.program_id = ps.program_id
            WHERE p.owner_agency_id = ?
            GROUP BY COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started')";

        $stmt = $conn->prepare($status_query);
        $stmt->bind_param("iii", $period_id, $period_id, $agency_id);
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
