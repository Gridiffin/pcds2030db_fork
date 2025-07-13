<?php
/**
 * Get All Agency Users Helper
 *
 * Returns an array of all agency and focal users (user_id, agency_name) for use in filters.
 * This is different from get_all_agencies() which gets agency groups from the agency table.
 */
function get_all_agency_users(mysqli $conn): array {
    $agencies = [];
    $sql = "SELECT user_id, agency_name FROM users WHERE role IN ('agency', 'focal') AND is_active = 1 ORDER BY agency_name ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $agencies[] = $row;
        }
    }
    return $agencies;
}
