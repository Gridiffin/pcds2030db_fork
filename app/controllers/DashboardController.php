<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard data filtering operations.
 */

class DashboardController {
    private $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get dashboard data with filter options
     * 
     * @param int $agency_id Current agency ID
     * @param int $period_id Current reporting period ID
     * @param bool $include_assigned Whether to include assigned programs
     * @return array Dashboard data
     */
    public function getDashboardData($agency_id, $period_id, $include_assigned = true) {
        $data = [
            'stats' => $this->getStatsData($agency_id, $period_id, $include_assigned),
            'chart_data' => $this->getChartData($agency_id, $period_id, $include_assigned),
            'recent_updates' => $this->getRecentUpdates($agency_id, $period_id)
        ];
        
        return $data;
    }
    
    /**
     * Get stats card data (filtered)
     * 
     * @param int $agency_id Current agency ID
     * @param int $period_id Current reporting period ID
     * @param bool $include_assigned Whether to include assigned programs
     * @return array Stats data
     */    private function getStatsData($agency_id, $period_id, $include_assigned) {
        // Build query with filters - this new query properly handles programs with no submissions
        $query = "SELECT 
                    p.program_id,
                    p.program_name,
                    p.is_assigned,
                    p.created_at,
                    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating,
                    CASE 
                        WHEN ps.submission_id IS NULL THEN 1
                        ELSE ps.is_draft 
                    END as is_draft
                  FROM programs p
                  LEFT JOIN (
                    SELECT ps1.*
                    FROM program_submissions ps1
                    INNER JOIN (
                        SELECT program_id, MAX(submission_id) as max_id
                        FROM program_submissions
                        WHERE period_id = ?
                        GROUP BY program_id
                    ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_id
                  ) ps ON p.program_id = ps.program_id
                  WHERE ((p.owner_agency_id = ? AND p.is_assigned = 0)";
        
        $params = [$period_id, $agency_id];
        $types = "ii";
        
        // Add assigned programs if include_assigned is true
        if ($include_assigned) {
            $query .= " OR (p.is_assigned = 1 AND p.owner_agency_id = ?)";
            $params[] = $agency_id;
            $types .= "i";
        }
        
        // Important: Only count finalized programs, NOT drafts
        $query .= ") AND (ps.is_draft = 0 OR ps.submission_id IS NULL)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Initialize stats counters
        $stats = [
            'total' => 0,
            'on-track' => 0,
            'delayed' => 0,
            'completed' => 0,
            'not-started' => 0
        ];
          while ($program = $result->fetch_assoc()) {
            // Skip draft programs and newly assigned programs (which are treated as drafts)
            if ($program['is_draft'] == 1 || ($program['is_assigned'] == 1 && !isset($program['rating']))) {
                continue;
            }
            
            $stats['total']++;
            
            // Map rating to categories
            $rating = $program['rating'] ?? 'not-started';
            
            if (in_array($rating, ['on-track', 'on-track-yearly'])) {
                $stats['on-track']++;
            } elseif (in_array($rating, ['delayed', 'severe-delay'])) {
                $stats['delayed']++;
            } elseif (in_array($rating, ['completed', 'target-achieved'])) {
                $stats['completed']++;
            } else {
                $stats['not-started']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get chart data (filtered)
     * 
     * @param int $agency_id Current agency ID
     * @param int $period_id Current reporting period ID
     * @param bool $include_assigned Whether to include assigned programs
     * @return array Chart data formatted for Chart.js
     */
    private function getChartData($agency_id, $period_id, $include_assigned) {
        // Reuse stats data for chart
        $stats = $this->getStatsData($agency_id, $period_id, $include_assigned);
        
        // Format data for Chart.js
        return [
            'labels' => ['On Track', 'Delayed', 'Target Achieved', 'Not Started'],
            'data' => [
                $stats['on-track'],
                $stats['delayed'],
                $stats['completed'],
                $stats['not-started']
            ]
        ];
    }
    
    /**
     * Get recent program updates (unfiltered for recent updates section)
     * Always include both assigned and agency-created programs
     * Show draft and newly assigned programs in Recent Updates section
     * 
     * @param int $agency_id Current agency ID
     * @param int $period_id Current reporting period ID
     * @return array Recent program updates
     */    private function getRecentUpdates($agency_id, $period_id) {
        // This query gets both finalized and draft submissions for the Recent Updates section
        $query = "SELECT 
                    p.program_id, 
                    p.program_name,
                    p.is_assigned,
                    p.created_at,
                    p.updated_at as program_updated_at,
                    COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating,
                    ps.is_draft,
                    ps.submission_date as updated_at
                  FROM programs p
                  LEFT JOIN (
                    SELECT ps1.*
                    FROM program_submissions ps1
                    INNER JOIN (
                        SELECT program_id, MAX(submission_id) as max_id
                        FROM program_submissions
                        WHERE period_id = ?
                        GROUP BY program_id
                    ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_id
                  ) ps ON p.program_id = ps.program_id
                  WHERE (p.owner_agency_id = ? OR (p.is_assigned = 1 AND p.owner_agency_id = ?))
                  ORDER BY COALESCE(ps.submission_date, p.updated_at, p.created_at) DESC
                  LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $period_id, $agency_id, $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
          $programs = [];
        while ($row = $result->fetch_assoc()) {
            // Mark newly assigned programs with no submissions as drafts
            if ($row['is_assigned'] == 1 && $row['rating'] === null) {
                $row['is_draft'] = 1;
                $row['rating'] = 'not-started';
            }
            
            // Set updated_at timestamp with fallback to program creation date
            $row['updated_at'] = $row['updated_at'] ?? $row['program_updated_at'] ?? $row['created_at'];
            
            $programs[] = $row;
        }
        
        return $programs;
    }
}
?>