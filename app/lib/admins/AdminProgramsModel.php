<?php
/**
 * Admin Programs Model
 * Handles data operations for admin program management
 */

class AdminProgramsModel {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get all finalized programs across all agencies for admin view
     * @return array Array of programs with submission details
     */
    public function getFinalizedPrograms() {
        $query = "SELECT DISTINCT p.*, 
                         i.initiative_name,
                         i.initiative_number,
                         i.initiative_id,
                         latest_sub.is_draft,
                         latest_sub.period_id,
                         latest_sub.submission_id as latest_submission_id,
                         latest_sub.submitted_at,
                         latest_sub.submitted_by,
                         rp.period_type,
                         rp.period_number,
                         rp.year as period_year,
                         a.agency_name,
                         su.fullname as submitted_by_name,
                         p.rating,
                         COALESCE(latest_sub.submitted_at, p.created_at) as updated_at
                  FROM programs p 
                  LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                  LEFT JOIN agency a ON p.agency_id = a.agency_id
                  LEFT JOIN (
                      SELECT ps1.*
                      FROM program_submissions ps1
                      INNER JOIN (
                          SELECT program_id, MAX(submission_id) as max_submission_id
                          FROM program_submissions
                          WHERE is_deleted = 0 AND is_draft = 0
                          GROUP BY program_id
                      ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
                      WHERE ps1.is_draft = 0
                  ) latest_sub ON p.program_id = latest_sub.program_id
                  LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
                  LEFT JOIN users su ON latest_sub.submitted_by = su.user_id
                  WHERE p.is_deleted = 0 
                  AND latest_sub.submission_id IS NOT NULL
                  ORDER BY a.agency_name, p.program_name";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $programs = [];
            while ($row = $result->fetch_assoc()) {
                $programs[] = $row;
            }
            
            return $programs;
            
        } catch (Exception $e) {
            error_log("Error fetching finalized programs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all agencies for filtering dropdown
     * @return array Array of agencies
     */
    public function getAllAgencies() {
        $query = "SELECT agency_id, agency_name FROM agency ORDER BY agency_name";
        
        try {
            $result = $this->conn->query($query);
            $agencies = [];
            
            while ($agency = $result->fetch_assoc()) {
                $agencies[] = $agency;
            }
            
            return $agencies;
            
        } catch (Exception $e) {
            error_log("Error fetching agencies: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all active initiatives for filtering dropdown
     * @return array Array of initiatives
     */
    public function getActiveInitiatives() {
        $query = "SELECT initiative_id, initiative_name, initiative_number 
                  FROM initiatives 
                  WHERE is_active = 1 
                  ORDER BY initiative_name";
        
        try {
            $result = $this->conn->query($query);
            $initiatives = [];
            
            while ($initiative = $result->fetch_assoc()) {
                $initiatives[] = $initiative;
            }
            
            return $initiatives;
            
        } catch (Exception $e) {
            error_log("Error fetching initiatives: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get program statistics for admin dashboard
     * @return array Program statistics
     */
    public function getProgramStatistics() {
        $stats = [
            'total_programs' => 0,
            'finalized_programs' => 0,
            'by_rating' => [
                'monthly_target_achieved' => 0,
                'on_track_for_year' => 0,
                'severe_delay' => 0,
                'not_started' => 0
            ],
            'by_agency' => []
        ];
        
        try {
            // Get total finalized programs
            $programs = $this->getFinalizedPrograms();
            $stats['finalized_programs'] = count($programs);
            
            // Count by rating
            foreach ($programs as $program) {
                $rating = $program['rating'] ?? 'not_started';
                if (isset($stats['by_rating'][$rating])) {
                    $stats['by_rating'][$rating]++;
                }
                
                // Count by agency
                $agency = $program['agency_name'] ?? 'Unknown';
                if (!isset($stats['by_agency'][$agency])) {
                    $stats['by_agency'][$agency] = 0;
                }
                $stats['by_agency'][$agency]++;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error fetching program statistics: " . $e->getMessage());
            return $stats;
        }
    }
}
?>
