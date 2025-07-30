<?php
/**
 * Common functions used throughout the application
 */

// Include rating helpers
require_once 'rating_helpers.php';
// Include status helpers
require_once 'program_status_helpers.php';
// Include asset helpers
require_once 'asset_helpers.php';


/**
 * Get a display name for a reporting period.
 * Example: Q1-2023, H1-2023, Yearly-2023
 * @param array $period Associative array of the period (must contain 'period_type', 'period_number', and 'year')
 * @return string Formatted period name
 */
function get_period_display_name($period) {
    if (!isset($period['period_type']) || !isset($period['period_number']) || !isset($period['year'])) {
        return 'Invalid Period';
    }
    $year = $period['year'];
    $type = $period['period_type'];
    $num = $period['period_number'];
    if ($type === 'quarter') {
        return "Q{$num}-{$year}";
    } elseif ($type === 'half') {
        return "Half Yearly {$num}-{$year}";
    } elseif ($type === 'yearly') {
        return "Yearly-{$year}";
    } else {
        return "Unknown-{$year}";
    }
}

/**
 * Get all metrics
 */
function get_all_metrics(){
    global $conn;
    
    $query = "SELECT * FROM sector_metrics_data";
    $result = $conn->query($query);
    
    if (!$result) {
        // Log error or handle it as needed
        error_log("Database query failed in get_all_metrics: " . $conn->error);
        return [];
    }
    
    $metrics = [];
    while ($row = $result->fetch_assoc()) {
        $metrics[] = $row;
    }
    
    return $metrics;

}
/**
 * Sanitize user input
 * @param string $data Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($data) {
    // Sanitization logic would go here
    return htmlspecialchars(trim($data));
}

/**
 * Generate random string
 * @param int $length Length of string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    // Random string generation logic would go here
    return ''; // Placeholder
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function format_date($date, $format = 'Y-m-d') {
    // Date formatting logic would go here
    return ''; // Placeholder
}

/**
 * Auto-manage reporting periods
 * 
 * 1. Creates missing periods for current year
 * 2. Sets correct open/closed status based on current date or admin selection
 * 
 * @param bool $respect_admin_open Respect manually opened periods by admin
 * @return bool True if successfully managed periods
 */
function auto_manage_reporting_periods($respect_admin_open = true) {
    global $conn;
    
    // First check if updated_at column exists, if not add it
    $check_column = "SHOW COLUMNS FROM reporting_periods LIKE 'updated_at'";
    $column_result = $conn->query($check_column);
    
    if ($column_result->num_rows === 0) {
        // Column doesn't exist, add it
        $alter_query = "ALTER TABLE reporting_periods 
                        ADD COLUMN updated_at TIMESTAMP NOT NULL 
                        DEFAULT CURRENT_TIMESTAMP 
                        ON UPDATE CURRENT_TIMESTAMP";
        $conn->query($alter_query);
    }
    
    // Define the quarters with their start and end dates
    $current_year = date('Y');
    $periods_definition = [
        // Quarters
        ['type' => 'quarterly', 'q_num' => 1, 'start_month' => 1, 'start_day' => 1, 'end_month' => 3, 'end_day' => 31],
        ['type' => 'quarterly', 'q_num' => 2, 'start_month' => 4, 'start_day' => 1, 'end_month' => 6, 'end_day' => 30],
        ['type' => 'quarterly', 'q_num' => 3, 'start_month' => 7, 'start_day' => 1, 'end_month' => 9, 'end_day' => 30],
        ['type' => 'quarterly', 'q_num' => 4, 'start_month' => 10, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31],
        // Half-yearly
        ['type' => 'half_yearly', 'q_num' => 1, 'start_month' => 1, 'start_day' => 1, 'end_month' => 6, 'end_day' => 30], // H1
        ['type' => 'half_yearly', 'q_num' => 2, 'start_month' => 7, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31], // H2
    ];
    
    $managed_periods = []; // To store details for creation/update

    // Current Year Periods
    foreach ($periods_definition as $def) {
        $managed_periods[] = [
            'year' => $current_year,
            'period_type' => ($def['type'] === 'quarterly' ? 'quarter' : ($def['type'] === 'half_yearly' ? 'half' : $def['type'])),
            'period_number' => $def['q_num'],
            'start' => sprintf("%d-%02d-%02d", $current_year, $def['start_month'], $def['start_day']),
            'end' => sprintf("%d-%02d-%02d", $current_year, $def['end_month'], $def['end_day']),
        ];
    }

    // Create next year's Q1 if we're in Q4 (October onwards)
    $current_month = date('n');
    if ($current_month >= 10) {
        $next_year = $current_year + 1;
        $managed_periods[] = [
            'year' => $next_year,
            'period_type' => 'quarter',
            'period_number' => 1, // Q1 of next year
            'start' => "$next_year-01-01",
            'end' => "$next_year-03-31",
        ];
    }
    
    // Get current date for comparison
    $today = date('Y-m-d');
    $current_quarter = ceil($current_month / 3);
    
    // Check if admin has manually set a period to open
    $admin_open_period = null;
    if ($respect_admin_open) {
        // Use safe query that doesn't rely on updated_at column ordering
        $manual_query = "SELECT period_id, year, period_type, period_number FROM reporting_periods WHERE status = 'open' LIMIT 1";
        $manual_result = $conn->query($manual_query);
        
        if ($manual_result && $manual_result->num_rows > 0) {
            $admin_open_period = $manual_result->fetch_assoc();
        }
    }
    
    // Check and create missing periods
    foreach ($managed_periods as $p_data) {
        $y = $p_data['year'];
        $ptype = $p_data['period_type'];
        $pnum = $p_data['period_number'];
        $start = $p_data['start'];
        $end = $p_data['end'];
        
        // Check if this period exists
        $query = "SELECT period_id, status FROM reporting_periods WHERE year = ? AND period_type = ? AND period_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $y, $ptype, $pnum);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Period doesn't exist, create it
            // Determine if this should be open
            // For quarterly:
            $is_current_q_for_year = ($ptype == 'quarter' && $y == $current_year && $pnum == $current_quarter);
            // For half-yearly H1 (q=1): open if current month is Jan-Jun
            $is_current_h1_for_year = ($ptype == 'half' && $y == $current_year && $pnum == 1 && $current_month >= 1 && $current_month <= 6);
            // For half-yearly H2 (q=2): open if current month is Jul-Dec
            $is_current_h2_for_year = ($ptype == 'half' && $y == $current_year && $pnum == 2 && $current_month >= 7 && $current_month <= 12);

            $status = ($is_current_q_for_year || $is_current_h1_for_year || $is_current_h2_for_year) ? 'open' : 'closed';
            
            // Create the period
            $insert = "INSERT INTO reporting_periods (year, period_type, period_number, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("isisss", $y, $ptype, $pnum, $start, $end, $status);
            $stmt->execute();
        } else {
            // Period exists, update its status based on current date or admin selection
            $period = $result->fetch_assoc();
            $period_id = $period['period_id'];
            
            // If admin has manually set an open period, respect that
            if ($respect_admin_open && $admin_open_period && 
                $admin_open_period['period_id'] == $period_id) {
                // Skip automatic status change for admin-selected open period
                continue;
            }
            
            // Current quarter/half should be open, others closed
            $is_current_q_for_year = ($ptype == 'quarter' && $y == $current_year && $pnum == $current_quarter);
            $is_current_h1_for_year = ($ptype == 'half' && $y == $current_year && $pnum == 1 && $current_month >= 1 && $current_month <= 6);
            $is_current_h2_for_year = ($ptype == 'half' && $y == $current_year && $pnum == 2 && $current_month >= 7 && $current_month <= 12);
            
            $should_be_open = ($is_current_q_for_year || $is_current_h1_for_year || $is_current_h2_for_year);
            
            // Only auto-update if no admin selection or this isn't the admin-selected period
            $current_status = $period['status'];
            
            if (($should_be_open && $current_status != 'open') || (!$should_be_open && $current_status != 'closed')) {
                $new_status = $should_be_open ? 'open' : 'closed';
                $update = "UPDATE reporting_periods SET status = ? WHERE period_id = ?";
                $stmt = $conn->prepare($update);
                $stmt->bind_param("si", $new_status, $period_id);
                $stmt->execute();
            }
        }
    }
    
    return true;
}

/**
 * Add derived fields to period data for backward compatibility
 * @param array $period The period data from database
 * @return array Period data with derived fields added
 */
function add_derived_period_fields($period) {
    if (!$period) {
        return null;
    }
    
    // Add quarter field for backward compatibility
    if (isset($period['period_type']) && isset($period['period_number'])) {
        if ($period['period_type'] === 'quarter') {
            $period['quarter'] = $period['period_number'];
        } elseif ($period['period_type'] === 'half') {
            // For half-yearly periods, use period_number as quarter (5 or 6)
            $period['quarter'] = $period['period_number'];
        } elseif ($period['period_type'] === 'yearly') {
            // For yearly periods, use 1 as quarter
            $period['quarter'] = 1;
        }
    }
    
    // Add half field for half-yearly periods
    if (isset($period['period_type']) && $period['period_type'] === 'half') {
        $period['half'] = $period['period_number'];
    }
    
    return $period;
}

/**
 * Get current reporting period
 * @return array|null Current active reporting period or null if none
 */
function get_current_reporting_period() {
    global $conn;
    
    // Prefer open quarterly period first if available and current
    $current_month = date('n');
    $current_year = date('Y');
    $current_q_val = ceil($current_month / 3);

    $query = "SELECT * FROM reporting_periods WHERE status = 'open' AND year = ? AND period_type = 'quarter' AND period_number = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $current_year, $current_q_val);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $period = $result->fetch_assoc();
        return add_derived_period_fields($period);
    }

    // Then prefer open half-yearly period if available and current
    $current_h_val = ($current_month <= 6) ? 1 : 2;
    $query_h = "SELECT * FROM reporting_periods WHERE status = 'open' AND year = ? AND period_type = 'half' AND period_number = ? LIMIT 1";
    $stmt_h = $conn->prepare($query_h);
    $stmt_h->bind_param("ii", $current_year, $current_h_val);
    $stmt_h->execute();
    $result_h = $stmt_h->get_result();

    if ($result_h->num_rows > 0) {
        $period = $result_h->fetch_assoc();
        return add_derived_period_fields($period);
    }
    
    // Fallback: any other open period (could be manually opened by admin)
    $query_any_open = "SELECT * FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, period_type ASC, period_number DESC LIMIT 1";
    $result_any_open = $conn->query($query_any_open);
    
    if ($result_any_open->num_rows > 0) {
        $period = $result_any_open->fetch_assoc();
        return add_derived_period_fields($period);
    }
    
    // If no open period, get the next upcoming one (quarterly or half-yearly)
    $query = "SELECT * FROM reporting_periods 
              WHERE start_date > NOW() 
              ORDER BY start_date ASC 
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $period = $result->fetch_assoc();
        return add_derived_period_fields($period);
    }
    
    // If no upcoming period, get the most recent one (quarterly or half-yearly)
    $query = "SELECT * FROM reporting_periods 
              ORDER BY year DESC, period_type ASC, period_number DESC 
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $period = $result->fetch_assoc();
        return add_derived_period_fields($period);
    }
    
    return null;
}

/**
 * Get all reporting periods
 * @return array List of all reporting periods
 */
function get_all_reporting_periods() {
    global $conn;
    
    $query = "SELECT * FROM reporting_periods ORDER BY year DESC, period_type ASC, period_number DESC";
    $result = $conn->query($query);
    
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        $periods[] = add_derived_period_fields($row);
    }
    
    return $periods;
}

/**
 * Get available reporting periods for the period dropdown in forms
 * This function retrieves all reporting periods suitable for dropdown selection in forms
 * 
 * @param bool $include_inactive Whether to include inactive (closed) periods in the result
 * @return array List of reporting periods with essential fields
 */
function get_reporting_periods_for_dropdown($include_inactive = false) {
    global $conn;
    $where_clause = $include_inactive ? "" : "WHERE status = 'open'";
    $query = "SELECT period_id, year, period_type, period_number, status FROM reporting_periods
              $where_clause
              ORDER BY year DESC, period_type ASC, period_number DESC";
    $result = $conn->query($query);
    if (!$result) {
        error_log("Error fetching reporting periods: " . $conn->error);
        return [];
    }
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        // Add derived fields for backward compatibility
        $row = add_derived_period_fields($row);
        // Format the period name for display in the dropdown
        $row['display_name'] = get_period_display_name($row);
        $periods[] = $row;
    }
    return $periods;
}

/**
 * Get a specific reporting period by ID
 * 
 * @param int $period_id The ID of the reporting period to retrieve
 * @return array|null The reporting period data or null if not found
 */
function get_reporting_period($period_id) {
    global $conn;
    
    // Check if connection is valid, if not reconnect
    if (!$conn || $conn->connect_error) {
        // Make sure db_connect.php path is correct if this file is moved
        $db_connect_path = dirname(__FILE__) . '/db_connect.php'; // Assumes db_connect.php is in the same directory
        if (!file_exists($db_connect_path)) {
             // Try one level up if functions.php is in includes/ and db_connect.php is in root
            $db_connect_path = dirname(__DIR__) . '/db_connect.php';
        }
        require_once $db_connect_path;
    }
    
    $query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $period = $result->fetch_assoc();
        return add_derived_period_fields($period);
    }
    
    return null;
}

/**
 * Validate user login credentials
 * @param string $username The username
 * @param string $password The password
 * @return array Result of login validation
 */
function validate_login($username, $password) {
    global $conn;
    
    // Include audit log functions
    require_once ROOT_PATH . 'app/lib/audit_log.php';
    
    // Sanitize inputs
    $username = $conn->real_escape_string(trim($username));
    
    // First check if username exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log failed login attempt - user not found
        log_login_attempt($username, false, 'User not found');
        return ['error' => 'Invalid username or password'];
    }
    
    $user = $result->fetch_assoc();
    
    // Check if account is active
    if (isset($user['is_active']) && $user['is_active'] == 0) {
        // Log failed login attempt - account deactivated
        log_login_attempt($username, false, 'Account deactivated', $user['user_id']);
        return ['error' => 'Your account has been deactivated. Please contact an administrator.'];
    }
    
    // Verify password - ensure hash exists and is not null
    if (!empty($user['pw']) && password_verify($password, $user['pw'])) {
        // Store user data in session, but don't include password
        unset($user['pw']);
        $_SESSION = $user;
        
        // Explicitly set agency_id in session to ensure consistency across the system
        // For regular users, user_id serves as agency_id based on the database schema
        $_SESSION['agency_id'] = $user['agency_id'];
        
        // Log successful login
        log_login_attempt($username, true, '', $user['user_id']);
        
        return ['success' => true, 'user' => $user];
    } else {
        // Log failed login attempt - wrong password
        log_login_attempt($username, false, 'Invalid password', $user['user_id']);
        return ['error' => 'Invalid username or password'];
    }
}

/**
 * Get current period ID
 * @param object $conn Database connection
 * @return int|null Current period ID or null if none
 */
function getCurrentPeriodId($conn) {
    $query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY end_date DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['period_id'];
    }
    
    return null;
}

/**
 * Get reporting period status color
 * @param string $status The status of the reporting period (e.g., 'open', 'closed')
 * @return string CSS class or color code representing the status
 */
function get_reporting_period_status_color($status) {
    switch ($status) {
        case 'open':
            return 'success'; // Or any color you prefer for open status
        case 'closed':
            return 'danger'; // Or any color you prefer for closed status
        default:
            return 'secondary'; // Default color
    }
}

/**
 * Log activity to audit log
 * 
 * @param int $user_id The ID of the user performing the action
 * @param string $action Description of the action performed
 * @return bool True on success, false on failure
 */
function log_activity($user_id, $action) {
    // TODO: Implement audit logging when audit_log table is created
    // For now, just log to error log for debugging
    error_log("Activity Log: User $user_id - $action");
    return true;
    
    /* 
    // Commented out until audit_log table is created
    global $conn;
    
    // Get username from session or database
    $username = $_SESSION['username'] ?? 'Unknown';
    
    // If username not in session, try to get it from database
    if ($username === 'Unknown' && $user_id > 0) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $username = $row['username'];
            }
            $stmt->close();
        }
    }
    
    // Insert into audit log
    $log_query = "INSERT INTO audit_log (user_id, username, action, action_date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($log_query);
    
    if (!$stmt) {
        error_log("Failed to prepare audit log statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iss", $user_id, $username, $action);
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("Failed to log activity: " . $stmt->error);
    }
    
    $stmt->close();
    return $success;
    */
}

/**
 * Safely set session message, preventing notification-related messages from being stored
 * @param string $message The message to set
 * @param string $type The message type (success, error, warning, info)
 * @return void
 */
function set_session_message($message, $type = 'info') {
    // Check if this is a notification-related message that should not be stored in session
    $notification_keywords = ['New program', 'created by', 'System Administrator', 'notification', 'unread'];
    $is_notification_message = false;
    
    foreach ($notification_keywords as $keyword) {
        if (stripos($message, $keyword) !== false) {
            $is_notification_message = true;
            break;
        }
    }
    
    // Only set session message if it's not a notification-related message
    if (!$is_notification_message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    } else {
        // Log that we prevented a notification message from being stored in session
        error_log("Prevented notification message from being stored in session: $message");
    }
}

/**
 * Format file size in human readable format
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function format_file_size($bytes) {
    if ($bytes == 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
