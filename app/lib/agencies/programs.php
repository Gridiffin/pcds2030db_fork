<?php
/**
 * Agency Program Management Functions
 * 
 * Contains functions for managing agency programs (add, update, delete, submit)
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once dirname(__DIR__) . '/numbering_helpers.php';
require_once dirname(__DIR__) . '/functions.php';

if (file_exists(dirname(__FILE__) . '/core.php')) {
    require_once 'core.php';
} else {
    require_once dirname(__DIR__) . '/session.php';
    require_once dirname(__DIR__) . '/functions.php';
}

// Include audit logging
require_once dirname(__DIR__) . '/audit_log.php';
require_once dirname(__DIR__) . '/admins/users.php'; // Add this to use get_user_by_id
require_once dirname(__DIR__) . '/notifications_core.php';

// Include numbering helpers for hierarchical program numbering
require_once dirname(__DIR__) . '/numbering_helpers.php';

// Load centralized database configuration
$config = include __DIR__ . '/../../config/db_names.php';
if (!$config || !isset($config['tables']['programs'])) {
    die('Config not loaded or missing programs table definition.');
}

// Map table names
$programsTable = $config['tables']['programs'];
$initiativesTable = $config['tables']['initiatives'];
$agencyTable = $config['tables']['agency'];
$usersTable = $config['tables']['users'];
$programSubmissionsTable = $config['tables']['program_submissions'];
$programTargetsTable = $config['tables']['program_targets'];
$programUserAssignmentsTable = 'program_user_assignments'; // Not in config yet

// Map column names for programs table
$programIdCol = $config['columns']['programs']['id'];
$programNameCol = $config['columns']['programs']['name'];
$programNumberCol = $config['columns']['programs']['number'];
$programDescriptionCol = $config['columns']['programs']['description'];
$programInitiativeIdCol = $config['columns']['programs']['initiative_id'];
$programAgencyIdCol = $config['columns']['programs']['agency_id'];
$programIsDeletedCol = $config['columns']['programs']['is_deleted'];
$programCreatedByCol = $config['columns']['programs']['created_by'];
$programCreatedAtCol = $config['columns']['programs']['created_at'];
$programUpdatedAtCol = $config['columns']['programs']['updated_at'];

// Map column names for initiatives table
$initiativeIdCol = $config['columns']['initiatives']['id'];
$initiativeNameCol = $config['columns']['initiatives']['name'];
$initiativeNumberCol = $config['columns']['initiatives']['number'];

// Map column names for agency table
$agencyIdCol = $config['columns']['agency']['id'];
$agencyNameCol = $config['columns']['agency']['name'];

// Map column names for users table
$userIdCol = $config['columns']['users']['id'];
$userAgencyIdCol = $config['columns']['users']['agency_id'];

/**
 * Get programs owned by current agency, separated by type
 */
function get_agency_programs_by_type() {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];
    return [
        'assigned' => get_agency_programs_list($user_id, true),
        'created' => get_agency_programs_list($user_id, false)
    ];
}

/**
 * Get agency programs with specified assignment status
 */
function get_agency_programs_list($user_id, $is_assigned = false) {
    global $conn;
    
    $query = "SELECT p.*, 
                     ps.status_indicator, ps.description, ps.start_date, ps.end_date,
                     ps.is_draft, ps.submission_id,
                     a.agency_name
              FROM programs p
              INNER JOIN program_user_assignments pua ON p.program_id = pua.program_id
              LEFT JOIN (
                  SELECT ps1.*
                  FROM program_submissions ps1
                  INNER JOIN (
                      SELECT program_id, MAX(submission_id) as max_submission_id
                      FROM program_submissions
                      GROUP BY program_id
                  ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
              ) ps ON p.program_id = ps.program_id
              LEFT JOIN agency a ON p.agency_id = a.agency_id
              WHERE pua.user_id = ?
              ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = process_content_json($row);
    }
    return $programs;
}

/**
 * Create a new program for an agency
 */
function create_agency_program($data) {
    global $conn;
    if (!is_agency()) return format_error('Permission denied', 403);
    $validated = validate_agency_program_input($data, ['program_name', 'initiative_id']);
    if (isset($validated['error'])) return $validated;
    $program_name = $validated['program_name'];
    $initiative_id = intval($validated['initiative_id']);
    $program_number = $validated['program_number'] ?? null;
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return format_error(get_program_number_format_error(false), 400);
    }
    
    // Check for duplicate program number if provided
    if ($program_number) {
        $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_number = ? AND initiative_id = ? AND is_deleted = 0");
        $check_stmt->bind_param("si", $program_number, $initiative_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            return format_error('Duplicate program number. This number is already in use for the selected initiative.', 400);
        }
    }
    
    $start_date = validate_program_date($validated['start_date'] ?? '');
    $end_date = validate_program_date($validated['end_date'] ?? '');
    if ($start_date === false) return format_error('Start Date must be in YYYY-MM-DD format.', 400);
    if ($end_date === false) return format_error('End Date must be in YYYY-MM-DD format.', 400);
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($conn, $user_id);
    $agency_id = $user ? $user['agency_id'] : null;
    // Create the program first
    $query = "INSERT INTO programs (program_name, program_number, program_description, agency_id, initiative_id, start_date, end_date, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $program_description = $validated['description'] ?? '';
    $stmt->bind_param("sssiisss", $program_name, $program_number, $program_description, $agency_id, $initiative_id, $start_date, $end_date, $user_id);
    if ($stmt->execute()) {
        $program_id = $conn->insert_id;
        // Create user assignment for the creator
        $assignment_query = "INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)";
        $assignment_stmt = $conn->prepare($assignment_query);
        $assignment_stmt->bind_param("iii", $program_id, $user_id, $user_id);
        $assignment_stmt->execute();
        
        // Program ownership is now determined by agency_id in programs table
        // Create initial program submission
        $period_id = get_current_reporting_period()['period_id'] ?? 1;
        $sub_query = "INSERT INTO program_submissions 
                    (program_id, period_id, is_draft, status_indicator, description, start_date, end_date, submitted_by)
                    VALUES (?, ?, 1, 'not_started', ?, ?, ?, ?)";
        $sub_stmt = $conn->prepare($sub_query);
        $description = $validated['description'] ?? '';
        $sub_stmt->bind_param("iissss", $program_id, $period_id, $description, $start_date, $end_date, $user_id);
        $sub_stmt->execute();
        
        // Send notification for program creation
        $program_data = [
            'program_name' => $program_name,
            'program_number' => $program_number,
            'agency_id' => $agency_id,
            'initiative_id' => $initiative_id
        ];
        notify_program_created($program_id, $user_id, $program_data);
        
        return [
            'success' => true,
            'message' => 'Program created successfully',
            'program_id' => $program_id
        ];
    } else {
        return format_error('Failed to create program: ' . $stmt->error);
    }
}

/**
 * Create a comprehensive program draft with correct data architecture
 */
function create_wizard_program_draft($data) {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }
    $program_name = trim($data['program_name']);
    $program_number = isset($data['program_number']) ? trim($data['program_number']) : null;
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = validate_program_date($data['start_date'] ?? '');
    $end_date = validate_program_date($data['end_date'] ?? '');
    if ($start_date === false) return ['error' => 'Start Date must be in YYYY-MM-DD format.'];
    if ($end_date === false) return ['error' => 'End Date must be in YYYY-MM-DD format.'];
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    $period_id = isset($data['period_id']) && !empty($data['period_id']) ? intval($data['period_id']) : null;
    
    // Validate period_id if provided
    if (!$period_id) {
        return ['error' => 'Reporting quarter selection is required'];
    } else {
        // Check if period exists in the database
        $period_check_query = "SELECT period_id FROM reporting_periods WHERE period_id = ?";
        $check_stmt = $conn->prepare($period_check_query);
        $check_stmt->bind_param("i", $period_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'The selected reporting quarter is invalid'];
        }
    }
    
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return ['error' => get_program_number_format_error(false)];
    }
    // Additional validation for hierarchical format if initiative is linked
    if ($program_number && $initiative_id) {
        $format_validation = validate_program_number_format($program_number, $initiative_id);
        if (!$format_validation['valid']) {
            return ['error' => $format_validation['message']];
        }
        // Check if number is already in use
        if (!is_program_number_available($program_number)) {
            return ['error' => 'Program number is already in use'];
        }
    }
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($conn, $user_id);
    $agency_id = $user ? $user['agency_id'] : null;
    try {
        $conn->begin_transaction();
        
        // Create the program
        $stmt = $conn->prepare("INSERT INTO programs (program_name, program_number, program_description, agency_id, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssii", $program_name, $program_number, $brief_description, $agency_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to create program: ' . $stmt->error);
        $program_id = $conn->insert_id;
        
        // Create user assignment for the creator
        $assignment_stmt = $conn->prepare("INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)");
        $assignment_stmt->bind_param("iii", $program_id, $user_id, $user_id);
        if (!$assignment_stmt->execute()) throw new Exception('Failed to create user assignment: ' . $assignment_stmt->error);
        
        // Program ownership is now determined by agency_id in programs table
        
        // Create initial program submission
        $current_period_id = $period_id ?: 1;
        $submission_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, is_draft, status_indicator, description, start_date, end_date, submitted_by) VALUES (?, ?, 1, 'not_started', ?, ?, ?, ?)");
        $submission_stmt->bind_param("iissss", $program_id, $current_period_id, $brief_description, $start_date, $end_date, $user_id);
        if (!$submission_stmt->execute()) throw new Exception('Failed to create program submission: ' . $submission_stmt->error);
        
        // Create targets if provided
        if (!empty($targets)) {
            $submission_id = $conn->insert_id;
            foreach ($targets as $target) {
                $target_stmt = $conn->prepare("INSERT INTO program_targets (submission_id, target_description, status_indicator, status_description) VALUES (?, ?, 'not_started', ?)");
                $target_text = $target['target'] ?? $target['target_text'] ?? '';
                $target_status = $target['status_description'] ?? '';
                $target_stmt->bind_param("iss", $submission_id, $target_text, $target_status);
                $target_stmt->execute();
            }
        }
        $conn->commit();
        log_audit_action('create_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
        
        // Send notification for program creation
        $program_data = [
            'program_name' => $program_name,
            'program_number' => $program_number,
            'agency_id' => $agency_id,
            'initiative_id' => $initiative_id
        ];
        notify_program_created($program_id, $user_id, $program_data);
        
        return [
            'success' => true, 
            'message' => 'Program draft created successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        log_audit_action('create_program_failed', "Program Name: $program_name | Error: " . $e->getMessage(), 'failure', $user_id);
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create simple program template (no initial submission)
 */
function create_simple_program($data) {
    global $conn, $programsTable, $programIdCol, $programNameCol, $programDescriptionCol, $programNumberCol, 
           $programAgencyIdCol, $programInitiativeIdCol, $programIsDeletedCol, $programCreatedByCol, $programCreatedAtCol;
    
    if (!is_agency()) return ['error' => 'Permission denied'];
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }
    $program_name = trim($data['program_name']);
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    
    // DEBUG: Log actual date values
    error_log("DEBUG - Raw start_date: " . var_export($data['start_date'] ?? '', true));
    error_log("DEBUG - Raw end_date: " . var_export($data['end_date'] ?? '', true));
    
    $start_date = validate_program_date($data['start_date'] ?? '');
    $end_date = validate_program_date($data['end_date'] ?? '');
    
    // DEBUG: Log processed date values
    error_log("DEBUG - Processed start_date: " . var_export($start_date, true));
    error_log("DEBUG - Processed end_date: " . var_export($end_date, true));
    
    if ($start_date === false) return ['error' => 'Start Date must be in YYYY-MM-DD format.'];
    if ($end_date === false) return ['error' => 'End Date must be in YYYY-MM-DD format.'];
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    
    // Handle program number with new simplified logic
    $program_number = null;
    if ($initiative_id) {
        // If initiative is selected, handle program number
        if (!empty($data['program_number'])) {
            // User provided a program number - validate it
            $program_number = trim($data['program_number']);
            if (!is_valid_program_number_format($program_number, false)) {
                return ['error' => get_program_number_format_error(false)];
            }
            
            // Validate hierarchical format if initiative is linked
            $format_validation = validate_program_number_format($program_number, $initiative_id);
            if (!$format_validation['valid']) {
                return ['error' => $format_validation['message']];
            }
            
            // Check if number is already in use
            $check_stmt = $conn->prepare("SELECT $programIdCol FROM $programsTable WHERE $programNumberCol = ? AND $programInitiativeIdCol = ? AND $programIsDeletedCol = 0");
            $check_stmt->bind_param("si", $program_number, $initiative_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                return ['error' => 'Duplicate program number. This number is already in use for the selected initiative.'];
            }
        } else {
            // Auto-generate program number for the initiative
            $program_number = generate_next_program_number($initiative_id);
            if (!$program_number) {
                return ['error' => 'Failed to generate program number for this initiative'];
            }
        }
    }
    
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($conn, $user_id);
    $agency_id = $user ? $user['agency_id'] : null;
    
    try {
        $conn->begin_transaction();
        
        // Create the program template with program number if available  
        $sql = "INSERT INTO programs (program_name, program_description, program_number, agency_id, initiative_id, start_date, end_date, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        // DEBUG: Log the actual SQL being prepared
        error_log("DEBUG - SQL: " . $sql);
        
        $stmt = $conn->prepare($sql);
        
        // DEBUG: Log values being bound
        error_log("DEBUG - About to bind: start_date='" . var_export($start_date, true) . "', end_date='" . var_export($end_date, true) . "'");
        error_log("DEBUG - All params: " . var_export([$program_name, $brief_description, $program_number, $agency_id, $initiative_id, $start_date, $end_date, $user_id], true));
        
        $stmt->bind_param("sssiisss", $program_name, $brief_description, $program_number, $agency_id, $initiative_id, $start_date, $end_date, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to create program: ' . $stmt->error);
        $program_id = $conn->insert_id;
        
        // Create user assignment for the creator
        $assignment_stmt = $conn->prepare("INSERT INTO program_user_assignments (program_id, user_id, role, assigned_by) VALUES (?, ?, 'editor', ?)");
        $assignment_stmt->bind_param("iii", $program_id, $user_id, $user_id);
        if (!$assignment_stmt->execute()) throw new Exception('Failed to create user assignment: ' . $assignment_stmt->error);
        
        // Program ownership is now determined by agency_id in programs table
        
        // Note: No initial submission is created - programs exist as templates
        // Submissions will be created when users add progress reports for specific periods
        
        $conn->commit();
        log_audit_action('create_program', "Program Name: $program_name | Program ID: $program_id | Program Number: $program_number", 'success', $user_id);
        
        // Send notification for program creation
        $program_data = [
            'program_name' => $program_name,
            'program_number' => $program_number,
            'agency_id' => $agency_id,
            'initiative_id' => $initiative_id
        ];
        notify_program_created($program_id, $user_id, $program_data);
        
        return [
            'success' => true, 
            'message' => 'Program template created successfully. Add progress reports for specific periods when ready to report progress.',
            'program_id' => $program_id,
            'program_number' => $program_number
        ];
    } catch (Exception $e) {
        $conn->rollback();
        log_audit_action('create_program_failed', "Program Name: $program_name | Error: " . $e->getMessage(), 'failure', $user_id);
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update simple program information (basic info only, not submissions)
 */
function update_simple_program($data) {
    global $conn;
    
    // Include the new rating helpers
    require_once dirname(__DIR__) . '/rating_helpers.php';
    
    if (!is_agency()) return ['error' => 'Permission denied'];
    
    if (empty($data['program_id'])) {
        return ['error' => 'Program ID is required'];
    }
    
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }
    
    $program_id = intval($data['program_id']);
    $program_name = trim($data['program_name']);
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    $rating = isset($data['rating']) ? trim($data['rating']) : RATING_NOT_STARTED;
    
    // Validate rating using new helper function
    if (!is_valid_rating($rating)) {
        return ['error' => 'Invalid rating value provided'];
    }
    
    // Check if user has access to this program
    $user_id = $_SESSION['user_id'];
    $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND agency_id = (SELECT agency_id FROM users WHERE user_id = ?)");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        return ['error' => 'Program not found or access denied'];
    }
    
    // Handle program number
    $program_number = null;
    if ($initiative_id && !empty($data['program_number'])) {
        $program_number = trim($data['program_number']);
    }
    
    try {
        $conn->begin_transaction();
        
        // Simple update query
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_description = ?, program_number = ?, initiative_id = ?, rating = ?, updated_at = NOW() WHERE program_id = ?");
        $stmt->bind_param("sssssi", $program_name, $brief_description, $program_number, $initiative_id, $rating, $program_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update program: ' . $stmt->error);
        }
        
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Program information updated successfully.',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Auto-save program draft with minimal validation
 */
function auto_save_program_draft($data) {
    global $conn;
    if (!is_agency()) return ['success' => false, 'error' => 'Permission denied'];
    if (empty($data['program_name']) || trim($data['program_name']) === '' || strlen(trim($data['program_name'])) < 3) {
        return ['success' => false, 'error' => 'Program name must be at least 3 characters for auto-save'];
    }
    $program_id = isset($data['program_id']) ? intval($data['program_id']) : 0;
    if ($program_id > 0) {
        return update_program_draft_only($program_id, $data);
    } else {
        return create_wizard_program_draft($data);
    }
}

/**
 * Update existing program draft with correct data architecture - used for auto-save
 */
function update_program_draft_only($program_id, $data) {
    global $conn;
    if (!is_agency()) return ['success' => false, 'error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];

    // Allow focal users to update any program draft, bypass ownership check
    require_once dirname(__DIR__) . '/session.php';
    if (!is_focal_user()) {
        $check_stmt = $conn->prepare("SELECT p.program_id FROM programs p 
                                     INNER JOIN program_user_assignments pua ON p.program_id = pua.program_id 
                                     WHERE p.program_id = ? AND pua.user_id = ?");
        $check_stmt->bind_param("ii", $program_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Program not found or access denied'];
        }
    }

    $program_name = trim($data['program_name']);
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    
    // Program number functionality removed - current schema doesn't support it
    
    $start_date = validate_program_date($data['start_date'] ?? '');
    $end_date = validate_program_date($data['end_date'] ?? '');
    if ($start_date === false) return ['success' => false, 'error' => 'Start Date must be in YYYY-MM-DD format.'];
    if ($end_date === false) return ['success' => false, 'error' => 'End Date must be in YYYY-MM-DD format.'];
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    
    // Get the period_id from data if available
    $period_id = isset($data['period_id']) && !empty($data['period_id']) ? intval($data['period_id']) : null;
    
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_description = ?, updated_at = NOW() WHERE program_id = ?");
        $stmt->bind_param("ssi", $program_name, $brief_description, $program_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
        
        // Handle submission update for auto-save
        if (!empty($targets) || !empty($brief_description)) {
            $check_stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ? AND is_deleted = 0 ORDER BY submission_id DESC LIMIT 1");
            $check_stmt->bind_param("i", $program_id);
            $check_stmt->execute();
            $submission_result = $check_stmt->get_result();
            
            if ($submission_result->num_rows > 0) {
                $submission = $submission_result->fetch_assoc();
                $submission_id = $submission['submission_id'];
                
                // Update submission
                $update_stmt = $conn->prepare("UPDATE program_submissions SET description = ?, start_date = ?, end_date = ?, updated_at = NOW() WHERE submission_id = ?");
                $update_stmt->bind_param("sssi", $brief_description, $start_date, $end_date, $submission_id);
                if (!$update_stmt->execute()) throw new Exception('Failed to update program submission: ' . $update_stmt->error);
                
                // Update targets
                if (!empty($targets)) {
                    // Delete existing targets
                    $delete_targets_stmt = $conn->prepare("DELETE FROM program_targets WHERE submission_id = ?");
                    $delete_targets_stmt->bind_param("i", $submission_id);
                    $delete_targets_stmt->execute();
                    
                    // Insert new targets
                    foreach ($targets as $index => $target) {
                        $target_stmt = $conn->prepare("INSERT INTO program_targets (submission_id, target_number, target_description, status_indicator, status_description) VALUES (?, ?, ?, 'not_started', ?)");
                        $target_text = $target['target'] ?? $target['target_text'] ?? '';
                        $target_status = $target['status_description'] ?? '';
                        
                        // Generate target number if not provided
                        $target_number = $target['target_number'] ?? '';
                        if (empty($target_number)) {
                            // Get program number for auto-generation
                            $program_stmt = $conn->prepare("SELECT program_number FROM programs WHERE program_id = ?");
                            $program_stmt->bind_param("i", $program_id);
                            $program_stmt->execute();
                            $program_result = $program_stmt->get_result();
                            if ($program_row = $program_result->fetch_assoc()) {
                                $target_number = $program_row['program_number'] . '.' . ($index + 1);
                            }
                        }
                        
                        $target_stmt->bind_param("issi", $submission_id, $target_number, $target_text, $target_status);
                        $target_stmt->execute();
                    }
                }
            } else {
                // Create new submission
                $current_period_id = $period_id ?: 1;
                $insert_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, is_draft, status_indicator, description, start_date, end_date, submitted_by) VALUES (?, ?, 1, 'not_started', ?, ?, ?, ?)");
                $insert_stmt->bind_param("iissss", $program_id, $current_period_id, $brief_description, $start_date, $end_date, $user_id);
                if (!$insert_stmt->execute()) throw new Exception('Failed to create program submission: ' . $insert_stmt->error);
                
                // Create targets if provided
                if (!empty($targets)) {
                    $new_submission_id = $conn->insert_id;
                    foreach ($targets as $index => $target) {
                        $target_stmt = $conn->prepare("INSERT INTO program_targets (submission_id, target_number, target_description, status_indicator, status_description) VALUES (?, ?, ?, 'not_started', ?)");
                        $target_text = $target['target'] ?? $target['target_text'] ?? '';
                        $target_status = $target['status_description'] ?? '';
                        
                        // Generate target number if not provided
                        $target_number = $target['target_number'] ?? '';
                        if (empty($target_number)) {
                            // Get program number for auto-generation
                            $program_stmt = $conn->prepare("SELECT program_number FROM programs WHERE program_id = ?");
                            $program_stmt->bind_param("i", $program_id);
                            $program_stmt->execute();
                            $program_result = $program_stmt->get_result();
                            if ($program_row = $program_result->fetch_assoc()) {
                                $target_number = $program_row['program_number'] . '.' . ($index + 1);
                            }
                        }
                        
                        $target_stmt->bind_param("issi", $new_submission_id, $target_number, $target_text, $target_status);
                        $target_stmt->execute();
                    }
                }
            }
        }
        
        $conn->commit();
        return [
            'success' => true,
            'message' => 'Program draft updated successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update existing program draft with wizard data (for manual saves/edits)
 */
function update_wizard_program_draft($program_id, $data) {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];
    // Check if user has access to this program (using agency_id instead of users_assigned)
    $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND agency_id = (SELECT agency_id FROM users WHERE user_id = ?)");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        return ['error' => 'Program not found or access denied'];
    }
    
    $program_name = trim($data['program_name']);
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = validate_program_date($data['start_date'] ?? '');
    $end_date = validate_program_date($data['end_date'] ?? '');
    if ($start_date === false) return ['error' => 'Start Date must be in YYYY-MM-DD format.'];
    if ($end_date === false) return ['error' => 'End Date must be in YYYY-MM-DD format.'];
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    $period_id = isset($data['period_id']) && !empty($data['period_id']) ? intval($data['period_id']) : null;
    
    // Validate period_id if provided
    if (!$period_id) {
        return ['error' => 'Reporting quarter selection is required'];
    } else {
        // Check if period exists in the database
        $period_check_query = "SELECT period_id FROM reporting_periods WHERE period_id = ?";
        $check_stmt = $conn->prepare($period_check_query);
        $check_stmt->bind_param("i", $period_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'The selected reporting quarter is invalid'];
        }
    }
    
    // Program number functionality removed - current schema doesn't support it
    
    try {
        $conn->begin_transaction();
          // Get current program data to check if initiative changed
        $current_query = "SELECT initiative_id FROM programs WHERE program_id = ?";
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->bind_param("i", $program_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_program = $current_result->fetch_assoc();
        
        $old_initiative_id = $current_program['initiative_id'];
        
        // Program number functionality removed - current schema doesn't support it
        
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_description = ?, initiative_id = ?, updated_at = NOW() WHERE program_id = ? AND agency_id = (SELECT agency_id FROM users WHERE user_id = ?)");
        $stmt->bind_param("ssiii", $program_name, $brief_description, $initiative_id, $program_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
        if (!empty($targets) || !empty($brief_description)) {
            $check_stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ? AND is_deleted = 0");
            $check_stmt->bind_param("i", $program_id);
            $check_stmt->execute();
            $submission_result = $check_stmt->get_result();
            
            $content_json = [];
            if (!empty($targets)) $content_json['targets'] = $targets;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            
            if ($submission_result->num_rows > 0) {
                $submission = $submission_result->fetch_assoc();
                $submission_id = $submission['submission_id'];
                $update_stmt = $conn->prepare("UPDATE program_submissions SET content_json = ?, updated_at = NOW() WHERE submission_id = ?");
                $update_stmt->bind_param("si", $content_json_string, $submission_id);
                if (!$update_stmt->execute()) throw new Exception('Failed to update program submission: ' . $update_stmt->error);
            } else {
                // Use the selected period_id if available, otherwise fall back to default
                if ($period_id) {
                    $current_period_id = $period_id;
                } else {
                    // Fallback to any open period if no period is selected
                    $fallback_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, period_type ASC, period_number DESC LIMIT 1";
                    $fallback_result = $conn->query($fallback_query);
                    $current_period_id = $fallback_result && $fallback_result->num_rows > 0 ? $fallback_result->fetch_assoc()['period_id'] : 1;
                }
                
                $insert_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $insert_stmt->bind_param("iiis", $program_id, $current_period_id, $user_id, $content_json_string);
                if (!$insert_stmt->execute()) throw new Exception('Failed to create program submission: ' . $insert_stmt->error);
            }
        }
        $conn->commit();
        
        // Log successful program update
        log_audit_action('update_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
        
        return [
            'success' => true,
            'message' => 'Program draft updated successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        
        // Log failed program update
        log_audit_action('update_program_failed', "Program Name: $program_name | Program ID: $program_id | Error: " . $e->getMessage(), 'failure', $user_id);
        
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Process content_json field if it exists in program data
 */
function process_content_json($program) {
    if (isset($program['content_json']) && !empty($program['content_json'])) {
        $content = json_decode($program['content_json'], true) ?: [];
        foreach ($content as $key => $value) {
            if (!isset($program[$key]) || $program[$key] === null) {
                $program[$key] = $value;
            }
        }
    }
    if (isset($program['submission_json']) && !empty($program['submission_json'])) {
        $submission = json_decode($program['submission_json'], true) ?: [];
        $program['current_submission'] = $submission;
    }
    return $program;
}

/**
 * Validate form input for agency programs
 */
function validate_agency_program_input($data, $required = []) {
    $validated = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return [
                'error' => true,
                'message' => "Missing required field: $field"
            ];
        }
    }
    foreach ($data as $key => $value) {
        $validated[$key] = is_array($value) ? $value : trim($value);
    }
    return $validated;
}

/**
 * Get detailed information about a specific program for agency view
 * Based on get_admin_program_details but with agency-specific access controls
 * 
 * @param int $program_id The ID of the program to retrieve
 * @param bool $allow_cross_agency Whether to allow viewing programs from other agencies (default: false)
 * @return array|false Program details array or false if not found/unauthorized
 */
function get_program_details($program_id, $allow_cross_agency = true) {
    global $conn;
    
    // Validate input
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return false;
    }
    
    // Check if user is agency
    if (!is_agency()) {
        return false;
    }
    
    // Base query to get program details with initiative and agency information
    $stmt = $conn->prepare("SELECT p.*, a.agency_name,
                                   i.initiative_id, i.initiative_name, i.initiative_number,
                                   i.initiative_description, i.start_date as initiative_start_date,
                                   i.end_date as initiative_end_date, i.created_at as initiative_created_at
                          FROM programs p
                          LEFT JOIN agency a ON p.agency_id = a.agency_id
                          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                          WHERE p.program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    
    // Access control: Only block if not agency and not admin
    // (edit/submit permissions are handled elsewhere)
    // So, allow viewing for all agency users
    // Get assigned users for this program
    $assign_stmt = $conn->prepare("SELECT user_id, role FROM program_user_assignments WHERE program_id = ?");
    $assign_stmt->bind_param("i", $program_id);
    $assign_stmt->execute();
    $assign_result = $assign_stmt->get_result();
    $assigned_users = [];
    while ($row = $assign_result->fetch_assoc()) {
        $assigned_users[] = $row;
    }
    $program['assigned_users'] = $assigned_users;
    
    // Get submissions for this program with reporting period details
    $stmt = $conn->prepare("SELECT ps.*, rp.year, rp.period_type, rp.period_number
                          FROM program_submissions ps 
                          JOIN reporting_periods rp ON ps.period_id = rp.period_id
                          WHERE ps.program_id = ? 
                          ORDER BY ps.submission_id DESC");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    $program['submissions'] = $submissions;
    // Set 'current_submission' to the latest submission if available
    if (!empty($submissions)) {
        $program['current_submission'] = $submissions[0];
    } else {
        $program['current_submission'] = null;
    }
    return $program;
}

/**
 * Get program edit history for display in UI
 * Returns formatted submission history with dates and draft/final status
 */
function get_program_edit_history($program_id) {
    global $conn;    // Get all submissions for this program with period information
    $stmt = $conn->prepare("
        SELECT ps.*, rp.year, rp.period_type, rp.period_number,
               CASE 
                   WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, ' ', rp.year)
                   WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, ' ', rp.year)
                   WHEN rp.period_type = 'yearly' THEN CONCAT('Y', rp.period_number, ' ', rp.year)
                   ELSE CONCAT(rp.period_type, ' ', rp.period_number, ' ', rp.year)
               END as period_name,
               ps.submitted_at as effective_date,
               u.username as submitted_by_name
        FROM program_submissions ps 
        LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
        LEFT JOIN users u ON ps.submitted_by = u.user_id
        WHERE ps.program_id = ? AND ps.is_deleted = 0
        ORDER BY ps.submission_id DESC, ps.submitted_at DESC
    ");
    
    if (!$stmt) {
        error_log("Database error in get_program_edit_history: " . $conn->error);
        return ['submissions' => []];
    }
    
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        // Process content_json if it exists
        if (isset($row['content_json']) && is_string($row['content_json'])) {
            $content = json_decode($row['content_json'], true);
            if ($content) {
                // Extract fields from content JSON
                foreach ($content as $key => $value) {
                    if (!isset($row[$key])) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        
        // Format the date for display with both date and time
        if ($row['effective_date']) {
            $row['formatted_date'] = date('M j, Y g:i A', strtotime($row['effective_date']));
        } else {
            $row['formatted_date'] = 'Unknown date';
        }
        
        // Set draft/final label
        $row['is_draft_label'] = ($row['is_draft'] ?? 0) ? 'Draft' : 'Final';
        
        // Format period name if available
        if (isset($row['year']) && isset($row['period_type']) && isset($row['period_number'])) {
            if ($row['period_type'] === 'quarter') {
                $row['period_display'] = "Q{$row['period_number']}-{$row['year']}";
            } elseif ($row['period_type'] === 'half') {
                $row['period_display'] = "H{$row['period_number']}-{$row['year']}";
            } elseif ($row['period_type'] === 'yearly') {
                $row['period_display'] = "Y{$row['period_number']}-{$row['year']}";
            } else {
                $row['period_display'] = "{$row['period_type']} {$row['period_number']}-{$row['year']}";
            }
        } elseif (isset($row['period_name'])) {
            $row['period_display'] = $row['period_name'];
        } else {
            $row['period_display'] = 'Unknown period';
        }
        
        $submissions[] = $row;
    }
    
    $stmt->close();
    
    return ['submissions' => $submissions];
}

/**
 * Get program edit history with pagination support
 * Returns formatted submission history with pagination info
 */
function get_program_edit_history_paginated($program_id, $page = 1, $per_page = 10) {
    global $conn;
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM program_submissions WHERE program_id = ? AND is_deleted = 0");
    if (!$count_stmt) {
        error_log("Database error in get_program_edit_history_paginated count: " . $conn->error);
        return ['submissions' => [], 'pagination' => ['total' => 0, 'pages' => 0, 'current_page' => 1]];
    }
    
    $count_stmt->bind_param("i", $program_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_entries = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Calculate pagination info
    $total_pages = ceil($total_entries / $per_page);
    $current_page = max(1, min($page, $total_pages));
    
    // Get paginated submissions
    $stmt = $conn->prepare("
        SELECT ps.*, rp.year, rp.period_type, rp.period_number,
               CASE 
                   WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, ' ', rp.year)
                   WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, ' ', rp.year)
                   WHEN rp.period_type = 'yearly' THEN CONCAT('Y', rp.period_number, ' ', rp.year)
                   ELSE CONCAT(rp.period_type, ' ', rp.period_number, ' ', rp.year)
               END as period_name,
               ps.submitted_at as effective_date,
               u.username as submitted_by_name,
               a.agency_name as submitted_by_agency
        FROM program_submissions ps 
        LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
        LEFT JOIN users u ON ps.submitted_by = u.user_id
        LEFT JOIN agency a ON u.agency_id = a.agency_id
        WHERE ps.program_id = ? AND ps.is_deleted = 0
        ORDER BY ps.submission_id DESC, ps.submitted_at DESC
        LIMIT ? OFFSET ?
    ");
    
    if (!$stmt) {
        error_log("Database error in get_program_edit_history_paginated: " . $conn->error);
        return ['submissions' => [], 'pagination' => ['total' => 0, 'pages' => 0, 'current_page' => 1]];
    }
    
    $stmt->bind_param("iii", $program_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        // Process content_json if it exists
        if (isset($row['content_json']) && is_string($row['content_json'])) {
            $content = json_decode($row['content_json'], true);
            if ($content) {
                // Extract fields from content JSON
                foreach ($content as $key => $value) {
                    if (!isset($row[$key])) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        
        // Format the date for display with both date and time
        if ($row['effective_date']) {
            $row['formatted_date'] = date('M j, Y g:i A', strtotime($row['effective_date']));
        } else {
            $row['formatted_date'] = 'Unknown date';
        }
        
        // Set draft/final label
        $row['is_draft_label'] = ($row['is_draft'] ?? 0) ? 'Draft' : 'Final';
        
        // Format period name if available
        if (isset($row['year']) && isset($row['period_type']) && isset($row['period_number'])) {
            if ($row['period_type'] === 'quarter') {
                $row['period_display'] = "Q{$row['period_number']}-{$row['year']}";
            } elseif ($row['period_type'] === 'half') {
                $row['period_display'] = "H{$row['period_number']}-{$row['year']}";
            } elseif ($row['period_type'] === 'yearly') {
                $row['period_display'] = "Y{$row['period_number']}-{$row['year']}";
            } else {
                $row['period_display'] = "{$row['period_type']} {$row['period_number']}-{$row['year']}";
            }
        } elseif (isset($row['period_name'])) {
            $row['period_display'] = $row['period_name'];
        } else {
            $row['period_display'] = 'Unknown period';
        }
        
        $submissions[] = $row;
    }
    
    $stmt->close();
    
    // Calculate display range
    $start_entry = $offset + 1;
    $end_entry = min($offset + $per_page, $total_entries);
    
    return [
        'submissions' => $submissions,
        'pagination' => [
            'total' => $total_entries,
            'pages' => $total_pages,
            'current_page' => $current_page,
            'per_page' => $per_page,
            'start_entry' => $start_entry,
            'end_entry' => $end_entry,
            'has_previous' => $current_page > 1,
            'has_next' => $current_page < $total_pages
        ]
    ];
}

/**
 * Get field edit history for a specific field from program submissions
 * Used to show how specific fields have changed over time
 */
function get_field_edit_history($submissions, $field_name) {
    $history = [];
    $seen_values = [];

    if (!is_array($submissions)) {
        return $history;
    }

    foreach ($submissions as $submission) {
        $value = null;

        // Try to get the field from the top-level first
        if (array_key_exists($field_name, $submission)) {
            $value = $submission[$field_name];
        }

        // If not found, try to get it from content_json
        if (($value === null || $value === '' || (is_array($value) && empty($value))) && isset($submission['content_json'])) {
            $content = is_array($submission['content_json']) ? $submission['content_json'] : json_decode($submission['content_json'], true);
            if (is_array($content) && array_key_exists($field_name, $content)) {
                $value = $content[$field_name];
            }
        }

        // Special handling for targets (legacy and new)
        if ($field_name === 'targets') {
            if (isset($submission['targets']) && is_array($submission['targets'])) {
                $value = $submission['targets'];
            } elseif (isset($submission['target']) && !empty($submission['target'])) {
                $value = [['text' => $submission['target'], 'target_text' => $submission['target']]];
            }
        }

        // Skip if no value or empty value
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            continue;
        }

        // Create a hash of the value to detect duplicates
        $value_hash = is_array($value) ? md5(json_encode($value)) : md5((string)$value);
        if (in_array($value_hash, $seen_values)) {
            continue;
        }
        $seen_values[] = $value_hash;

        // Format timestamp with date and time
        $timestamp = 'Unknown date';
        if (isset($submission['formatted_date'])) {
            $timestamp = $submission['formatted_date'];
        } elseif (isset($submission['effective_date'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['effective_date']));
        } elseif (isset($submission['submission_date'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['submission_date']));
        } elseif (isset($submission['created_at'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['created_at']));
        }

        $history[] = [
            'value' => $value,
            'timestamp' => $timestamp,
            'is_draft' => $submission['is_draft'] ?? 0,
            'submission_id' => $submission['submission_id'] ?? 0,
            'period_name' => $submission['period_display'] ?? ''
        ];
    }

    return $history;
}

/**
 * Get related programs under the same initiative
 * Returns programs that share the same initiative_id, excluding the current program
 */
function get_related_programs_by_initiative($initiative_id, $current_program_id = null, $allow_cross_agency = false) {
    global $conn;
    
    if (!$initiative_id) {
        return [];
    }
    
    $current_user_id = $_SESSION['user_id'];
    
    // Base query for related programs
    $where_conditions = ["p.initiative_id = ?"];
    $params = [$initiative_id];
    $param_types = "i";
    
    // Exclude current program if specified
    if ($current_program_id) {
        $where_conditions[] = "p.program_id != ?";
        $params[] = $current_program_id;
        $param_types .= "i";
    }
    
    // Access control - only show programs from user's agency
    if (!$allow_cross_agency) {
        $where_conditions[] = "p.agency_id = (SELECT agency_id FROM users WHERE user_id = ?)";
        $params[] = $current_user_id;
        $param_types .= "i";
    }
    
    $sql = "SELECT p.program_id, p.program_name, p.program_number, p.agency_id,
                   a.agency_name, 
                   COALESCE(latest_sub.is_draft, 1) as is_draft
            FROM programs p
            LEFT JOIN agency a ON p.agency_id = a.agency_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id
            WHERE " . implode(' AND ', $where_conditions) . "
            ORDER BY p.program_name";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $related_programs = [];
    while ($row = $result->fetch_assoc()) {
        $related_programs[] = $row;
    }
    
    return $related_programs;
}

/**
 * Capture current program state before making changes
 * Returns complete state including program table data and latest submission content
 */
function get_current_program_state($program_id) {
    global $conn;
    
    // Get program table data
    $stmt = $conn->prepare("
        SELECT p.program_name, p.program_description, p.agency_id,
               ps.status_indicator, ps.description, ps.start_date, ps.end_date,
               a.agency_name
        FROM programs p
        LEFT JOIN (
            SELECT ps1.*
            FROM program_submissions ps1
            INNER JOIN (
                SELECT program_id, MAX(submission_id) as max_submission_id
                FROM program_submissions
                GROUP BY program_id
            ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
        ) ps ON p.program_id = ps.program_id
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE p.program_id = ?
    ");
    
    if (!$stmt) {
        error_log("Error preparing query in get_current_program_state: " . $conn->error);
        return array();
    }
    
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program_data = $result->fetch_assoc();
    if (!$program_data) {
        $program_data = array();
    }
    $stmt->close();
    
    // Get latest submission content and targets
    $submission_stmt = $conn->prepare("
        SELECT ps.submission_id, ps.description, ps.status_indicator, ps.rating,
               ps.start_date, ps.end_date, ps.is_draft
        FROM program_submissions ps
        WHERE ps.program_id = ? 
        ORDER BY ps.submission_id DESC 
        LIMIT 1
    ");
    
    if ($submission_stmt) {
        $submission_stmt->bind_param("i", $program_id);
        $submission_stmt->execute();
        $submission_result = $submission_stmt->get_result();
        $submission_data = $submission_result->fetch_assoc();
        $submission_stmt->close();
        
        if ($submission_data) {
            // Merge submission data with program data
            $program_data = array_merge($program_data, $submission_data);
            
            // Get targets for this submission
            $targets_stmt = $conn->prepare("
                SELECT target_description, status_indicator, status_description
                FROM program_targets
                WHERE submission_id = ?
                ORDER BY target_id
            ");
            $targets_stmt->bind_param("i", $submission_data['submission_id']);
            $targets_stmt->execute();
            $targets_result = $targets_stmt->get_result();
            
            $targets = array();
            while ($target = $targets_result->fetch_assoc()) {
                $targets[] = array(
                    'target' => $target['target_description'],
                    'status_description' => $target['status_description']
                );
            }
            $targets_stmt->close();
            $program_data['targets'] = $targets;
        }
    }
    
    // Get current outcome links
    $outcome_links_stmt = $conn->prepare("
        SELECT outcome_id 
        FROM program_outcome_links 
        WHERE program_id = ? 
        ORDER BY outcome_id
    ");
    
    if ($outcome_links_stmt) {
        $outcome_links_stmt->bind_param("i", $program_id);
        $outcome_links_stmt->execute();
        $outcome_result = $outcome_links_stmt->get_result();
        
        $linked_outcomes = array();
        while ($row = $outcome_result->fetch_assoc()) {
            $linked_outcomes[] = $row['outcome_id'];
        }
        $outcome_links_stmt->close();
        
        $program_data['linked_outcomes'] = $linked_outcomes;
    }

    return $program_data;
}

/**
 * Generate before/after changes by comparing current state with new state
 */
function generate_field_changes($before_state, $after_state) {
    $changes = array();
    
    // Define trackable fields with their labels
    $trackable_fields = array(
        'program_name' => 'Program Name',
        'program_description' => 'Program Description',
        'agency_name' => 'Owner Agency',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'status_indicator' => 'Status',
        'rating' => 'Rating/Status',
        'description' => 'Description'
    );
    
    // Check basic fields for changes
    foreach ($trackable_fields as $field => $label) {
        $before_value = trim(isset($before_state[$field]) ? $before_state[$field] : '');
        $after_value = trim(isset($after_state[$field]) ? $after_state[$field] : '');
        
        // Special handling for status
        if ($field === 'status') {
            $before_value = $before_value ? ucfirst(str_replace('-', ' ', $before_value)) : 'Not Set';
            $after_value = $after_value ? ucfirst(str_replace('-', ' ', $after_value)) : 'Not Set';
        }
        
        // Special handling for dates
        if (in_array($field, array('start_date', 'end_date'))) {
            if ($before_value) $before_value = date('Y-m-d', strtotime($before_value));
            if ($after_value) $after_value = date('Y-m-d', strtotime($after_value));
        }
        
        // Check if values are different
        if ($before_value !== $after_value) {
            $changes[] = array(
                'field' => $field,
                'field_label' => $label,
                'before' => $before_value ? $before_value : '(empty)',
                'after' => $after_value ? $after_value : '(empty)',
                'change_type' => 'modified'
            );
        }
    }
    
    // Check targets for changes
    $before_targets = isset($before_state['targets']) ? $before_state['targets'] : array();
    $after_targets = isset($after_state['targets']) ? $after_state['targets'] : array();
    
    if (!is_array($before_targets)) $before_targets = array();
    if (!is_array($after_targets)) $after_targets = array();
    
    // Check outcome links for changes
    $before_outcomes = isset($before_state['linked_outcomes']) ? $before_state['linked_outcomes'] : array();
    $after_outcomes = isset($after_state['linked_outcomes']) ? $after_state['linked_outcomes'] : array();
    
    if (!is_array($before_outcomes)) $before_outcomes = array();
    if (!is_array($after_outcomes)) $after_outcomes = array();
    
    // Compare outcome lists
    $added_outcomes = array_diff($after_outcomes, $before_outcomes);
    $removed_outcomes = array_diff($before_outcomes, $after_outcomes);
    
    // Track added outcomes
    foreach ($added_outcomes as $outcome_id) {
        $outcome_name = get_outcome_name_by_id($outcome_id);
        $changes[] = array(
            'field' => 'outcome_links',
            'field_label' => 'Linked Outcomes',
            'before' => null,
            'after' => $outcome_name,
            'change_type' => 'added'
        );
    }
    
    // Track removed outcomes
    foreach ($removed_outcomes as $outcome_id) {
        $outcome_name = get_outcome_name_by_id($outcome_id);
        $changes[] = array(
            'field' => 'outcome_links',
            'field_label' => 'Linked Outcomes',
            'before' => $outcome_name,
            'after' => null,
            'change_type' => 'removed'
        );
    }

    // Check for target changes
    $max_targets = max(count($before_targets), count($after_targets));
    
    for ($i = 0; $i < $max_targets; $i++) {
        $target_num = $i + 1;
        $before_target = isset($before_targets[$i]) ? $before_targets[$i] : array();
        $after_target = isset($after_targets[$i]) ? $after_targets[$i] : array();
        
        $before_text = trim(isset($before_target['target']) ? $before_target['target'] : (isset($before_target['target_text']) ? $before_target['target_text'] : ''));
        $after_text = trim(isset($after_target['target']) ? $after_target['target'] : (isset($after_target['target_text']) ? $after_target['target_text'] : ''));
        $before_status = trim(isset($before_target['status_description']) ? $before_target['status_description'] : '');
        $after_status = trim(isset($after_target['status_description']) ? $after_target['status_description'] : '');
        
        // Check target text changes
        if ($before_text !== $after_text) {
            if (empty($before_text) && !empty($after_text)) {
                // New target added
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => null,
                    'after' => $after_text,
                    'change_type' => 'added'
                );
            } elseif (!empty($before_text) && empty($after_text)) {
                // Target removed
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => $before_text,
                    'after' => null,
                    'change_type' => 'removed'
                );
            } else {
                // Target modified
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => $before_text ? $before_text : '(empty)',
                    'after' => $after_text ? $after_text : '(empty)',
                    'change_type' => 'modified'
                );
            }
        }
        
        // Check target status changes
        if ($before_status !== $after_status && (!empty($before_text) || !empty($after_text))) {
            $changes[] = array(
                'field' => "target_{$target_num}_status",
                'field_label' => "Target {$target_num} Status",
                'before' => $before_status ? $before_status : '(empty)',
                'after' => $after_status ? $after_status : '(empty)',
                'change_type' => 'modified'
            );
        }
    }
    
    return $changes;
}

/**
 * Display before/after changes in a readable format for the history table
 */
function display_before_after_changes($changes_made) {
    if (empty($changes_made) || !is_array($changes_made)) {
        return '<span class="text-muted">No changes recorded</span>';
    }
    
    $output = '<div class="changes-detail">';
    
    foreach ($changes_made as $change) {
        $field_label = htmlspecialchars(isset($change['field_label']) ? $change['field_label'] : 'Unknown Field');
        $change_type = isset($change['change_type']) ? $change['change_type'] : 'modified';
        $before = htmlspecialchars(isset($change['before']) ? $change['before'] : '');
        $after = htmlspecialchars(isset($change['after']) ? $change['after'] : '');
        
        $output .= '<div class="change-item mb-2">';
        
        if ($change_type === 'added') {
            $output .= '<strong class="text-success">' . $field_label . ':</strong><br>';
            $output .= '<span class="text-success">Added:</span> "' . $after . '"';
        } elseif ($change_type === 'removed') {
            $output .= '<strong class="text-danger">' . $field_label . ':</strong><br>';
            $output .= '<span class="text-danger">Removed:</span> "' . $before . '"';
        } else {
            $output .= '<strong>' . $field_label . ':</strong><br>';
            $output .= '<span class="text-muted">From:</span> "' . $before . '"<br>';
            $output .= '<span class="text-muted">To:</span> "' . $after . '"';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get outcome name by outcome ID
 * Helper function for change tracking
 */
function get_outcome_name_by_id($outcome_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT title FROM outcomes WHERE id = ?");
    $stmt->bind_param("i", $outcome_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['title'];
    }
    
    $stmt->close();
    return "Unknown Outcome (ID: $outcome_id)";
}

/**
 * Create a new program submission with targets
 * 
 * @param array $data Submission data including program_id, period_id, targets, etc.
 * @return array Result with success status and message/error
 */
function create_program_submission($data) {
    global $conn;
    
    try {
        // Validate required fields
        if (empty($data['program_id']) || empty($data['period_id'])) {
            return ['success' => false, 'error' => 'Program ID and Period ID are required.'];
        }
        
        // Check if program exists and user has access
        $program = get_program_details($data['program_id']);
        if (!$program) {
            return ['success' => false, 'error' => 'Program not found or access denied.'];
        }
        
        // Check if submission already exists for this program and period
        $existing_query = "SELECT submission_id FROM program_submissions 
                          WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($existing_query);
        $stmt->bind_param("ii", $data['program_id'], $data['period_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'error' => 'A submission already exists for this program and period.'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Create program submission
        $submission_query = "INSERT INTO program_submissions 
                            (program_id, period_id, is_draft, is_submitted, description, submitted_by, submitted_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $is_draft = isset($_POST['save_as_draft']) ? 1 : 0;
        $is_submitted = isset($_POST['submit']) ? 1 : 0;
        $submitted_by = $_SESSION['user_id'] ?? null;
        
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param("iiissss", 
            $data['program_id'],
            $data['period_id'],
            $is_draft,
            $is_submitted,
            $data['description'],
            $submitted_by,
            date('Y-m-d H:i:s')
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create program submission: " . $stmt->error);
        }
        
        $submission_id = $conn->insert_id;
        
        // Create targets if provided
        if (!empty($data['targets']) && is_array($data['targets'])) {
            $target_query = "INSERT INTO program_targets 
                            (submission_id, target_number, target_description, status_indicator, status_description, 
                             start_date, end_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($target_query);
            
            foreach ($data['targets'] as $index => $target) {
                // Generate target number if not provided
                $target_number = $target['target_number'] ?? '';
                if (empty($target_number) && !empty($data['program_number'])) {
                    // Auto-generate target number based on program number
                    $target_number = $data['program_number'] . '.' . ($index + 1);
                }
                
                $stmt->bind_param("issssss", 
                    $submission_id,
                    $target_number,
                    $target['target_text'],
                    $target['target_status'],
                    $target['status_description'],
                    $target['start_date'],
                    $target['end_date']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create target: " . $stmt->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $action = $is_submitted ? 'submitted' : 'saved as draft';
        return [
            'success' => true, 
            'message' => "Program submission successfully {$action}.",
            'submission_id' => $submission_id
        ];
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Update an existing program submission and its targets
 * @param array $data Submission data including program_id, period_id, description, targets, etc.
 * @return array Result with success status and message/error
 */
function update_program_submission($data) {
    global $conn;
    try {
        // Validate required fields
        if (empty($data['program_id']) || empty($data['period_id'])) {
            return ['success' => false, 'error' => 'Program ID and Period ID are required.'];
        }
        // Check if submission exists
        $submission_query = "SELECT submission_id FROM program_submissions WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param("ii", $data['program_id'], $data['period_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Submission not found.'];
        }
        $row = $result->fetch_assoc();
        $submission_id = $row['submission_id'];
        // Start transaction
        $conn->begin_transaction();
        // Update submission
        $update_query = "UPDATE program_submissions SET description = ?, updated_at = NOW() WHERE submission_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $data['description'], $submission_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update program submission: ' . $stmt->error);
        }
        // Update targets: delete old, insert new
        $delete_targets_query = "DELETE FROM program_targets WHERE submission_id = ?";
        $stmt = $conn->prepare($delete_targets_query);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        if (!empty($data['targets']) && is_array($data['targets'])) {
            $target_query = "INSERT INTO program_targets (submission_id, target_number, target_description, status_indicator, status_description) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($target_query);
            foreach ($data['targets'] as $index => $target) {
                $target_number = $target['target_number'] ?? '';
                $target_text = $target['target_text'] ?? '';
                $target_status = $target['target_status'] ?? 'not_started';
                $status_description = $target['status_description'] ?? '';
                $stmt->bind_param("issss", $submission_id, $target_number, $target_text, $target_status, $status_description);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update target: ' . $stmt->error);
                }
            }
        }
        $conn->commit();
        return ['success' => true, 'message' => 'Submission updated successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Strictly validate a date string as YYYY-MM-DD or empty/null.
 * Returns the date if valid, or null if empty, or false if invalid.
 */
function validate_program_date($date) {
    if (empty($date)) return null;
    $date = trim($date);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    return false;
}