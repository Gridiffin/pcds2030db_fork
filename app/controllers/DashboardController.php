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
     * @param int|null $period_id Reporting period ID (now always current period)
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Dashboard data
     */
    public function getDashboardData($agency_id, $period_id = null, $include_assigned = true, $initiative_id = null) {
        // Always use the current period if not provided
        if (!$period_id) {
            $current_period = get_current_reporting_period();
            $period_id = $current_period['period_id'] ?? null;
        }
        $data = [
            'stats' => $this->getStatsData($agency_id, $period_id, $include_assigned, $initiative_id),
            'chart_data' => $this->getChartData($agency_id, $period_id, $include_assigned, $initiative_id),
            'recent_updates' => $this->getRecentUpdates($agency_id, $period_id, $initiative_id)
        ];
        return $data;
    }
    
    /**
     * Get stats card data (filtered)
     * 
     * @param int $agency_id Current agency ID
     * @param int $period_id Reporting period ID
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Stats data
     */
    private function getStatsData($agency_id, $period_id, $include_assigned, $initiative_id = null) {
        global $programsTable, $programSubmissionsTable;
        global $programIdCol, $programNameCol, $programAgencyIdCol, $programCreatedAtCol;
        global $submissionIdCol, $submissionProgramIdCol, $submissionPeriodIdCol, $submissionIsDraftCol;

        // Only count programs that have submitted data (not drafts) for the current period
        $query = "SELECT 
                    p.{$programIdCol},
                    p.{$programNameCol},
                    p.{$programCreatedAtCol},
                    p.status,
                    p.rating
                  FROM {$programsTable} p
                  INNER JOIN (
                    SELECT DISTINCT {$submissionProgramIdCol}
                    FROM {$programSubmissionsTable}
                    WHERE {$submissionPeriodIdCol} = ? AND {$submissionIsDraftCol} = 0
                  ) ps ON p.{$programIdCol} = ps.{$submissionProgramIdCol}
                  WHERE (p.{$programAgencyIdCol} = ? OR 
                    EXISTS (SELECT 1 FROM program_user_assignments pua WHERE pua.program_id = p.{$programIdCol} AND pua.user_id = ?))
                  AND p.is_deleted = 0";
        
        $params = [$period_id, $agency_id, $agency_id];
        $types = "iii";
        
        if ($initiative_id !== null) {
            $query .= " AND p.initiative_id = ?";
            $params[] = $initiative_id;
            $types .= "i";
        }
        
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
            $stats['total']++;
            
            // Map status values from programs table
            $status = $program['status'] ?? 'active';
            
            switch ($status) {
                case 'active':
                    $stats['on-track']++;
                    break;
                case 'delayed':
                    $stats['delayed']++;
                    break;
                case 'completed':
                    $stats['completed']++;
                    break;
                case 'on_hold':
                case 'cancelled':
                default:
                    $stats['not-started']++;
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get chart data (filtered)
     *
     * @param int $agency_id Current agency ID
     * @param int $period_id Reporting period ID
     * @param bool $include_assigned Whether to include assigned programs
     * @param int|null $initiative_id Optional initiative filter
     * @return array Chart data formatted for Chart.js
     */
    private function getChartData($agency_id, $period_id, $include_assigned, $initiative_id = null) {
        // Reuse stats data for chart
        $stats = $this->getStatsData($agency_id, $period_id, $include_assigned, $initiative_id);
        
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
     * @param int $period_id Reporting period ID
     * @param int|null $initiative_id Optional initiative filter
     * @return array Recent program updates
     */
    private function getRecentUpdates($agency_id, $period_id, $initiative_id = null) {
        global $programsTable, $programSubmissionsTable;
        global $programIdCol, $programNameCol, $programAgencyIdCol, $programCreatedAtCol;
        global $submissionIdCol, $submissionProgramIdCol, $submissionPeriodIdCol, $submissionIsDraftCol;
        $in_clause = '?';
        $query = "SELECT 
                    p.{$programIdCol}, 
                    p.{$programNameCol},
                    p.{$programCreatedAtCol},
                    p.updated_at as program_updated_at,
                    p.rating,
                    ps.{$submissionIsDraftCol},
                    ps.submitted_at as updated_at
                  FROM {$programsTable} p
                  LEFT JOIN (
                    SELECT ps1.*
                    FROM {$programSubmissionsTable} ps1
                    INNER JOIN (
                        SELECT {$submissionProgramIdCol}, MAX({$submissionIdCol}) as max_id
                        FROM {$programSubmissionsTable}
                        WHERE {$submissionPeriodIdCol} = $in_clause
                        GROUP BY {$submissionProgramIdCol}
                    ) ps2 ON ps1.{$submissionProgramIdCol} = ps2.{$submissionProgramIdCol} AND ps1.{$submissionIdCol} = ps2.max_id
                  ) ps ON p.{$programIdCol} = ps.{$submissionProgramIdCol}
                  WHERE (p.{$programAgencyIdCol} = ? OR EXISTS (SELECT 1 FROM program_user_assignments pua WHERE pua.program_id = p.{$programIdCol} AND pua.user_id = ?))";
        $params = [$period_id, $agency_id, $agency_id];
        $types = 'iii';
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

    /**
     * Get all programs for the agency and selected period, including period-specific submission and targets
     * Used for dashboard carousel
     *
     * @param int $agency_id
     * @param int $period_id
     * @return array
     */
    public function getProgramsForPeriod($agency_id, $period_id) {
        global $programsTable, $programSubmissionsTable;
        global $programIdCol, $programNameCol, $programNumberCol, $programAgencyIdCol, $programCreatedAtCol;
        
        $query = "SELECT p.$programIdCol, p.$programNameCol, p.$programNumberCol, p.$programAgencyIdCol, p.created_at, p.updated_at,
                        ps.submission_id, ps.is_draft, ps.updated_at as submission_updated_at
                  FROM $programsTable p
                  LEFT JOIN $programSubmissionsTable ps ON p.$programIdCol = ps.program_id AND ps.period_id = ? AND ps.is_draft = 0
                  WHERE p.$programAgencyIdCol = ? AND p.is_deleted = 0
                  ORDER BY p.$programNameCol ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $period_id, $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            // No content_json, so set submission fields to null/empty
            $row['submission'] = [
                'status_indicator' => null,
                'is_submitted' => $row['is_draft'] == 0 && $row['submission_id'] ? true : false,
                'updated_at' => $row['submission_updated_at'],
                'targets' => []
            ];
            $programs[] = $row;
        }
        $stmt->close();
        return $programs;
    }
}
?>