<?php
/**
 * Agency Initiative Functions
 * 
 * Functions for agency users to view initiatives they're involved with
 */

// Load database configuration
$config = include dirname(__DIR__) . '/../config/db_names.php';
if (!$config || !isset($config['tables']['initiatives'])) {
    die('Config not loaded or missing initiatives table definition.');
}

// Extract table and column names
$initiativesTable = $config['tables']['initiatives'];
$programsTable = $config['tables']['programs'];
$usersTable = $config['tables']['users'];
$agencyTable = $config['tables']['agency'];
$programSubmissionsTable = $config['tables']['program_submissions'];

// Initiative columns
$initiativeIdCol = $config['columns']['initiatives']['id'];
$initiativeNameCol = $config['columns']['initiatives']['name'];
$initiativeNumberCol = $config['columns']['initiatives']['number'];
$initiativeDescriptionCol = $config['columns']['initiatives']['description'];
$initiativeIsActiveCol = $config['columns']['initiatives']['is_active'];

// Program columns
$programIdCol = $config['columns']['programs']['id'];
$programNameCol = $config['columns']['programs']['name'];
$programInitiativeIdCol = $config['columns']['programs']['initiative_id'];
$programAgencyIdCol = $config['columns']['programs']['agency_id'];

// User columns
$userIdCol = $config['columns']['users']['id'];
$userAgencyIdCol = $config['columns']['users']['agency_id'];

// Agency columns
$agencyIdCol = $config['columns']['agency']['id'];
$agencyNameCol = $config['columns']['agency']['name'];

// Program submission columns
$submissionIdCol = $config['columns']['program_submissions']['id'];
$submissionProgramIdCol = $config['columns']['program_submissions']['program_id'];
$submissionIsDraftCol = $config['columns']['program_submissions']['is_draft'];

/**
 * Get initiatives that have programs assigned to the current agency
 */
function get_agency_initiatives($agency_id = null, $filters = []) {
    global $conn, $initiativesTable, $programsTable;
    global $initiativeIdCol, $initiativeNameCol, $initiativeNumberCol, $initiativeDescriptionCol, $initiativeIsActiveCol;
    global $programIdCol, $programInitiativeIdCol, $programAgencyIdCol;
    
    if ($agency_id === null) {
        $agency_id = $_SESSION['agency_id'];
    }

    $where = ["p.{$programAgencyIdCol} = ?"];
    $params = [$agency_id];
    $types = 'i';

    if (isset($filters['is_active'])) {
        $where[] = "i.{$initiativeIsActiveCol} = ?";
        $params[] = $filters['is_active'];
        $types .= 'i';
    }
    if (isset($filters['search']) && $filters['search'] !== '') {
        $where[] = "(i.{$initiativeNameCol} LIKE ? OR i.{$initiativeNumberCol} LIKE ? OR i.{$initiativeDescriptionCol} LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= 'sss';
    }
    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT i.{$initiativeIdCol}, i.{$initiativeNameCol}, i.{$initiativeNumberCol}, i.{$initiativeDescriptionCol}, i.start_date, i.end_date, i.{$initiativeIsActiveCol},
                   COUNT(DISTINCT p.{$programIdCol}) as agency_program_count,
                   (
                       SELECT COUNT(DISTINCT all_p.{$programIdCol})
                       FROM {$programsTable} all_p
                       WHERE all_p.{$programInitiativeIdCol} = i.{$initiativeIdCol}
                   ) as total_program_count
            FROM {$initiativesTable} i
            JOIN {$programsTable} p ON i.{$initiativeIdCol} = p.{$programInitiativeIdCol}
            $where_sql
            GROUP BY i.{$initiativeIdCol}
            ORDER BY i.{$initiativeNumberCol} ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $initiatives = [];
    while ($row = $result->fetch_assoc()) {
        $initiatives[] = $row;
    }
    return $initiatives;
}

/**
 * Get detailed initiative information for agency view
 */
function get_agency_initiative_details($initiative_id, $agency_id = null) {
    global $conn, $initiativesTable, $programsTable;
    global $initiativeIdCol, $programIdCol, $programInitiativeIdCol, $programAgencyIdCol;
    
    // Use current session agency if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['agency_id'];
    }
    
    // First check if agency has programs in this initiative
    $check_sql = "SELECT COUNT(*) as count FROM {$programsTable} WHERE {$programInitiativeIdCol} = ? AND {$programAgencyIdCol} = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('ii', $initiative_id, $agency_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $access_check = $check_result->fetch_assoc();
    
    if ($access_check['count'] == 0) {
        return null; // Agency doesn't have access to this initiative
    }
    
    // Get initiative details
    $sql = "SELECT i.*, 
                   COUNT(DISTINCT p.{$programIdCol}) as total_program_count,
                   COUNT(DISTINCT agency_p.{$programIdCol}) as agency_program_count
            FROM {$initiativesTable} i
            LEFT JOIN {$programsTable} p ON i.{$initiativeIdCol} = p.{$programInitiativeIdCol}
            LEFT JOIN {$programsTable} agency_p ON i.{$initiativeIdCol} = agency_p.{$programInitiativeIdCol} AND agency_p.{$programAgencyIdCol} = ?
            WHERE i.{$initiativeIdCol} = ?
            GROUP BY i.{$initiativeIdCol}";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $agency_id, $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get programs under an initiative with agency context
 */
function get_initiative_programs_for_agency($initiative_id, $agency_id = null) {
    global $conn, $programsTable, $usersTable, $agencyTable, $programSubmissionsTable;
    global $programIdCol, $programNameCol, $programInitiativeIdCol, $programAgencyIdCol;
    global $userIdCol, $userAgencyIdCol, $agencyIdCol, $agencyNameCol;
    global $submissionIdCol, $submissionProgramIdCol, $submissionIsDraftCol;
    
    // Use current session agency if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['agency_id'];
    }
    
    $sql = "SELECT p.*, a.{$agencyNameCol},
                   COALESCE(latest_sub.{$submissionIsDraftCol}, 1) as is_draft
            FROM {$programsTable} p
            LEFT JOIN {$agencyTable} a ON p.{$programAgencyIdCol} = a.{$agencyIdCol}
            LEFT JOIN (
                SELECT ps1.*
                FROM {$programSubmissionsTable} ps1
                INNER JOIN (
                    SELECT {$submissionProgramIdCol}, MAX({$submissionIdCol}) as max_submission_id
                    FROM {$programSubmissionsTable}
                    GROUP BY {$submissionProgramIdCol}
                ) ps2 ON ps1.{$submissionProgramIdCol} = ps2.{$submissionProgramIdCol} AND ps1.{$submissionIdCol} = ps2.max_submission_id
            ) latest_sub ON p.{$programIdCol} = latest_sub.{$submissionProgramIdCol}
            WHERE p.{$programInitiativeIdCol} = ?
            ORDER BY p.{$programNameCol} ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}
?>
