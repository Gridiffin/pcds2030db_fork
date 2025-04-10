<?php
/**
 * Admin dashboard stats helper functions
 */

/**
 * Get reporting period statistics for dashboard
 * 
 * @return array Statistics about reporting periods
 */
function get_reporting_period_stats() {
    global $conn;
    
    // Get total number of periods
    $query = "SELECT 
                COUNT(*) as total_periods,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_periods,
                MAX(CASE WHEN status = 'open' THEN CONCAT('Q', quarter, '-', year) ELSE NULL END) as current_period
              FROM reporting_periods";
              
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'total_periods' => 0,
        'open_periods' => 0,
        'current_period' => 'None'
    ];
}
?>
