<?php
/**
 * Get All Agencies Helper
 *
 * Returns an array of all agencies and focal agencies (user_id, agency_name) for use in filters.
 */
function get_all_agencies(mysqli $conn): array {
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
