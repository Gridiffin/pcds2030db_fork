<?php
/**
 * Get All Agency Users Helper
 *
 * Returns an array of all agency and focal users (user_id, agency_name) for use in filters.
 * This is different from get_all_agencies() which gets agency groups from the agency table.
 */
function get_all_agency_users(mysqli $conn): array {
    $agencies = [];
    $sql = "SELECT u.user_id, a.agency_name 
            FROM users u 
            JOIN agency a ON u.agency_id = a.agency_id 
            WHERE u.role IN ('agency', 'focal') AND u.is_active = 1 
            ORDER BY a.agency_name ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $agencies[] = $row;
        }
    }
    return $agencies;
}
