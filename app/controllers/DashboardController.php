<?php
/**
 * Dashboard Controller
 * 
 * Handles dashboard data filtering operations.
 */

// Load database configuration
$config = include __DIR__ . '/../config/db_names.php';
if (!$config || !isset($config['tables']['programs'])) {
    die('Config not loaded or missing programs table definition.');
}

// Extract table and column names
$programsTable = $config['tables']['programs'];
$programSubmissionsTable = $config['tables']['program_submissions'];
$usersTable = $config['tables']['users'];
$agencyTable = $config['tables']['agency'];

// Program columns
$programIdCol = $config['columns']['programs']['id'];
$programNameCol = $config['columns']['programs']['name'];
$programAgencyIdCol = $config['columns']['programs']['agency_id'];
$programCreatedAtCol = $config['columns']['programs']['created_at'];

// Program submissions columns
$submissionIdCol = $config['columns']['program_submissions']['id'];
$submissionProgramIdCol = $config['columns']['program_submissions']['program_id'];
$submissionPeriodIdCol = $config['columns']['program_submissions']['period_id'];
$submissionIsDraftCol = $config['columns']['program_submissions']['is_draft'];

// User columns
$userIdCol = $config['columns']['users']['id'];
$userAgencyIdCol = $config['columns']['users']['agency_id'];

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
     * @param array|int $period_ids Array of reporting period IDs (or single ID for backward compatibility)
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Dashboard data
     */
    public function getDashboardData($agency_id, $period_ids, $include_assigned = true, $initiative_id = null) {
        // Support both single and multiple period IDs
        if (!is_array($period_ids)) {
            $period_ids = [$period_ids];
        }
        $data = [
            'stats' => $this->getStatsData($agency_id, $period_ids, $include_assigned, $initiative_id),
            'chart_data' => $this->getChartData($agency_id, $period_ids, $include_assigned, $initiative_id),
            'recent_updates' => $this->getRecentUpdates($agency_id, $period_ids, $initiative_id)
        ];
        
        return $data;
    }
    
    /**
     * Get stats card data (filtered)
     * 
     * @param int $agency_id Current agency ID
     * @param array $period_ids Array of reporting period IDs
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Stats data
     */
    private function getStatsData($agency_id, $period_ids, $include_assigned, $initiative_id = null) {
        global $programsTable, $programSubmissionsTable;
        global $programIdCol, $programNameCol, $programAgencyIdCol, $programCreatedAtCol;
        global $submissionIdCol, $submissionProgramIdCol, $submissionPeriodIdCol, $submissionIsDraftCol;

        $in_clause = implode(',', array_fill(0, count($period_ids), '?'));
        $query = "SELECT 
                    p.{$programIdCol},
                    p.{$programNameCol},
                    p.{$programCreatedAtCol},
                    COALESCE(ps.rating, 'not-started') as rating,
                    CASE 
                        WHEN ps.{$submissionIdCol} IS NULL THEN 1
                        ELSE ps.{$submissionIsDraftCol} 
                    END as is_draft
                  FROM {$programsTable} p
                  LEFT JOIN (
                    SELECT ps1.*
                    FROM {$programSubmissionsTable} ps1
                    INNER JOIN (
                        SELECT {$submissionProgramIdCol}, MAX({$submissionIdCol}) as max_id
                        FROM {$programSubmissionsTable}
                        WHERE {$submissionPeriodIdCol} IN ($in_clause)
                        GROUP BY {$submissionProgramIdCol}
                    ) ps2 ON ps1.{$submissionProgramIdCol} = ps2.{$submissionProgramIdCol} AND ps1.{$submissionIdCol} = ps2.max_id
                  ) ps ON p.{$programIdCol} = ps.{$submissionProgramIdCol}
                  WHERE (p.{$programAgencyIdCol} = ? OR 
                    EXISTS (SELECT 1 FROM program_user_assignments pua WHERE pua.program_id = p.{$programIdCol} AND pua.user_id = ?))";
        $params = array_merge($period_ids, [$agency_id, $agency_id]);
        $types = str_repeat('i', count($period_ids)) . 'ii';
        if ($initiative_id !== null) {
            $query .= " AND p.initiative_id = ?";
            $params[] = $initiative_id;
            $types .= "i";
        }
        $query .= " AND (ps.{$submissionIsDraftCol} = 0 OR ps.{$submissionIdCol} IS NULL)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = [
            'total' => 0,
            'on-track' => 0,
            'delayed' => 0,
            'completed' => 0,
            'not-started' => 0
        ];
        while ($program = $result->fetch_assoc()) {
            if ($program['is_draft'] == 1) continue;
            $stats['total']++;
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
     * @param array $period_ids Array of reporting period IDs
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Chart data formatted for Chart.js
     */
    private function getChartData($agency_id, $period_ids, $include_assigned, $initiative_id = null) {
        // Reuse stats data for chart
        $stats = $this->getStatsData($agency_id, $period_ids, $include_assigned, $initiative_id);
        
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
     * @param array $period_ids Array of reporting period IDs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Recent program updates
     */
    private function getRecentUpdates($agency_id, $period_ids, $initiative_id = null) {
        global $programsTable, $programSubmissionsTable;
        global $programIdCol, $programNameCol, $programAgencyIdCol, $programCreatedAtCol;
        global $submissionIdCol, $submissionProgramIdCol, $submissionPeriodIdCol, $submissionIsDraftCol;
        $in_clause = implode(',', array_fill(0, count($period_ids), '?'));
        $query = "SELECT 
                    p.{$programIdCol}, 
                    p.{$programNameCol},
                    p.{$programCreatedAtCol},
                    p.updated_at as program_updated_at,
                    COALESCE(ps.rating, 'not-started') as rating,
                    ps.{$submissionIsDraftCol},
                    ps.submitted_at as updated_at
                  FROM {$programsTable} p
                  LEFT JOIN (
                    SELECT ps1.*
                    FROM {$programSubmissionsTable} ps1
                    INNER JOIN (
                        SELECT {$submissionProgramIdCol}, MAX({$submissionIdCol}) as max_id
                        FROM {$programSubmissionsTable}
                        WHERE {$submissionPeriodIdCol} IN ($in_clause)
                        GROUP BY {$submissionProgramIdCol}
                    ) ps2 ON ps1.{$submissionProgramIdCol} = ps2.{$submissionProgramIdCol} AND ps1.{$submissionIdCol} = ps2.max_id
                  ) ps ON p.{$programIdCol} = ps.{$submissionProgramIdCol}
                  WHERE (p.{$programAgencyIdCol} = ? OR EXISTS (SELECT 1 FROM program_user_assignments pua WHERE pua.program_id = p.{$programIdCol} AND pua.user_id = ?))";
        $params = array_merge($period_ids, [$agency_id, $agency_id]);
        $types = str_repeat('i', count($period_ids)) . 'ii';
        if ($initiative_id !== null) {
            $query .= " AND p.initiative_id = ?";
            $params[] = $initiative_id;
            $types .= "i";
        }
        $query .= " ORDER BY COALESCE(ps.submitted_at, p.updated_at, p.{$programCreatedAtCol}) DESC LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $row['updated_at'] = $row['updated_at'] ?? $row['program_updated_at'] ?? $row[$programCreatedAtCol];
            $programs[] = $row;
        }
        return $programs;
    }
}
?>