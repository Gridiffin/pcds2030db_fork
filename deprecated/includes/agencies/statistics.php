<?php
/**
 * Agency Statistics and Reporting Functions
 * 
 * Contains functions for getting agency statistics and all-sector data
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get submission status for an agency
 * 
 * This function retrieves statistics about program submissions for a specific agency
 * 
 * @param int $agency_id The ID of the agency
 * @param int $period_id Optional period ID to filter by specific reporting period
 * @return array Statistics about program submissions
 */
function get_agency_submission_status($agency_id, $period_id = null) {
    global $conn;
    
    // Ensure we have valid input
    $agency_id = intval($agency_id);
    if (!$agency_id) {
        return ['error' => 'Invalid agency ID'];
    }
    
    // Initialize return data structure
    $result = [
        'total_programs' => 0,
        'submitted_count' => 0,
        'draft_count' => 0,
        'not_submitted' => 0,
        'program_status' => [
            'on-track' => 0,
            'delayed' => 0,
            'completed' => 0,
            'not-started' => 0
        ]
    ];
    
    try {
        // Get total programs for this agency
        $programs_query = "SELECT COUNT(*) as total FROM programs WHERE owner_agency_id = ?";
        $stmt = $conn->prepare($programs_query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $programs_result = $stmt->get_result();
        $result['total_programs'] = $programs_result->fetch_assoc()['total'];
        
        // If no programs, return early
        if ($result['total_programs'] == 0) {
            return $result;
        }
        
        // Get submission status counts
        $status_query = "SELECT 
                            ps.status,
                            COUNT(*) as count,
                            SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
                            SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
                        FROM programs p
                        LEFT JOIN (
                            SELECT program_id, status, is_draft, 
                                   ROW_NUMBER() OVER (PARTITION BY program_id ORDER BY submission_id DESC) as rn
                            FROM program_submissions";
        
        // Add period filter if specified
        if ($period_id) {
            $status_query .= " WHERE period_id = " . intval($period_id);
        }
        
        $status_query .= ") ps ON p.program_id = ps.program_id AND ps.rn = 1
                          WHERE p.owner_agency_id = ?
                          GROUP BY ps.status";
        
        $stmt = $conn->prepare($status_query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $status_result = $stmt->get_result();
        
        // Initialize counters
        $submitted_total = 0;
        $draft_total = 0;
        
        // Process each status group
        while ($status_row = $status_result->fetch_assoc()) {
            $status = $status_row['status'] ?? 'not-started';
            $count = $status_row['count'] ?? 0;
            $draft_count = $status_row['draft_count'] ?? 0;
            $submitted_count = $status_row['submitted_count'] ?? 0;
            
            // Map status to our categories and increment counters
            switch (strtolower($status)) {
                case 'on-track':
                case 'on-track-yearly':
                    $result['program_status']['on-track'] += $submitted_count;
                    break;
                case 'delayed':
                case 'severe-delay':
                    $result['program_status']['delayed'] += $submitted_count;
                    break;
                case 'completed':
                case 'target-achieved':
                    $result['program_status']['completed'] += $submitted_count;
                    break;
                case 'not-started':
                default:
                    $result['program_status']['not-started'] += $submitted_count;
                    break;
            }
            
            // Update totals
            $submitted_total += $submitted_count;
            $draft_total += $draft_count;
        }
        
        // Update summary stats
        $result['submitted_count'] = $submitted_total;
        $result['draft_count'] = $draft_total;
        $result['not_submitted'] = $result['total_programs'] - ($submitted_total + $draft_total);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error in get_agency_submission_status: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
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
                p.description, 
                p.start_date, 
                p.end_date,
                p.created_at,
                p.updated_at,
                p.sector_id,
                s.sector_name,
                u.agency_name";
    
    // Add content json field or target/achievement fields based on schema
    if ($has_content_json) {
        $query .= ", ps.content_json";
    } else {
        $query .= ", ps.target, ps.achievement, ps.status_date, ps.status_text";
    }
    
    // Always add status
    $query .= ", ps.status, ps.is_draft
              FROM programs p
              JOIN sectors s ON p.sector_id = s.sector_id
              JOIN users u ON p.owner_agency_id = u.user_id
              LEFT JOIN (";
    
    // Changed to LEFT JOIN to include programs with no submissions
    
    // Subquery to get latest submission for each program 
    $subquery = "SELECT ps1.program_id, ps1.status, ps1.is_draft";
    
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
        
        // Filter by status
        if (isset($filters['status']) && $filters['status']) {
            $filterConditions[] = "ps.status = ?";
            $filterParams[] = $filters['status'];
            $filterTypes .= "s";
        }
        
        // Filter by search term
        if (isset($filters['search']) && $filters['search']) {
            $searchTerm = '%' . $filters['search'] . '%';
            $filterConditions[] = "(p.program_name LIKE ? OR p.description LIKE ?)";
            $filterParams[] = $searchTerm;
            $filterParams[] = $searchTerm;
            $filterTypes .= "ss";
        }
    }
    
    // Add filter conditions to query
    if (!empty($filterConditions)) {
        $query .= " WHERE " . implode(" AND ", $filterConditions);
    }
    
    // Finalize query
    $query .= " GROUP BY p.program_id 
                ORDER BY (p.sector_id = ?) DESC, p.created_at DESC";
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
 * Get the name of a sector by its ID
 * 
 * @param int $sector_id The ID of the sector
 * @return string The name of the sector or 'Unknown Sector' if not found
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
?>