<?php
/**
 * Hierarchical Program Numbering System
 * 
 * This module provides functions for managing hierarchical program numbers
 * where programs are numbered based on their parent initiative number.
 * Format: Initiative.Sequence (e.g., 30.1, 30.2, 30.3)
 */

/**
 * Generate the next available program number for an initiative
 * 
 * @param int $initiative_id The initiative ID
 * @return string The next program number (e.g., "30.1", "30.2")
 */
function generate_next_program_number($initiative_id) {
    global $conn;
    
    if (!$initiative_id) {
        return null;
    }
    
    // Get the initiative number
    $initiative_query = "SELECT initiative_number FROM initiatives WHERE initiative_id = ?";
    $stmt = $conn->prepare($initiative_query);
    $stmt->bind_param("i", $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $initiative = $result->fetch_assoc();
    
    if (!$initiative || !$initiative['initiative_number']) {
        return null;
    }
    
    $initiative_number = $initiative['initiative_number'];
    
    // Find the highest sequence number for this initiative
    $sequence_query = "SELECT program_number FROM programs 
                       WHERE initiative_id = ? AND program_number LIKE ? 
                       ORDER BY CAST(SUBSTRING_INDEX(program_number, '.', -1) AS UNSIGNED) DESC 
                       LIMIT 1";
    
    $pattern = $initiative_number . '.%';
    $stmt = $conn->prepare($sequence_query);
    $stmt->bind_param("is", $initiative_id, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $next_sequence = 1;
    if ($row = $result->fetch_assoc()) {
        $last_number = $row['program_number'];
        if (preg_match('/\.(\d+)$/', $last_number, $matches)) {
            $next_sequence = intval($matches[1]) + 1;
        }
    }
    
    return $initiative_number . '.' . $next_sequence;
}

/**
 * Update all program numbers for an initiative when initiative number changes
 * 
 * @param int $initiative_id The initiative ID
 * @param string $new_initiative_number The new initiative number
 * @return array Result array with success status and details
 */
function update_initiative_program_numbers($initiative_id, $new_initiative_number) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get all programs for this initiative ordered by their current sequence
        $programs_query = "SELECT program_id, program_number FROM programs 
                          WHERE initiative_id = ? AND program_number IS NOT NULL 
                          ORDER BY CAST(SUBSTRING_INDEX(program_number, '.', -1) AS UNSIGNED)";
        
        $stmt = $conn->prepare($programs_query);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sequence = 1;
        $updated_count = 0;
        
        while ($program = $result->fetch_assoc()) {
            $new_program_number = $new_initiative_number . '.' . $sequence;
            
            // Update the program number
            $update_query = "UPDATE programs SET program_number = ? WHERE program_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_program_number, $program['program_id']);
            
            if ($update_stmt->execute()) {
                $updated_count++;
                $sequence++;
            }
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'updated_count' => $updated_count,
            'message' => "Updated {$updated_count} program numbers for initiative {$new_initiative_number}"
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => 'Failed to update program numbers: ' . $e->getMessage()
        ];
    }
}

/**
 * Assign hierarchical number to a program when it's assigned to an initiative
 * 
 * @param int $program_id The program ID
 * @param int $initiative_id The initiative ID
 * @return array Result array with success status and new program number
 */
function assign_hierarchical_program_number($program_id, $initiative_id) {
    global $conn;
    
    if (!$initiative_id) {
        // If no initiative, clear the program number
        $update_query = "UPDATE programs SET program_number = NULL WHERE program_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $program_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'program_number' => null];
        } else {
            return ['success' => false, 'error' => 'Failed to clear program number'];
        }
    }
    
    $new_number = generate_next_program_number($initiative_id);
    
    if (!$new_number) {
        return ['success' => false, 'error' => 'Could not generate program number'];
    }
    
    // Update the program with the new hierarchical number
    $update_query = "UPDATE programs SET program_number = ? WHERE program_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_number, $program_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'program_number' => $new_number,
            'message' => "Program assigned number {$new_number}"
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to update program number'
        ];
    }
}

/**
 * Validate program number format
 * 
 * @param string $program_number The program number to validate
 * @param int $initiative_id Optional initiative ID to validate against
 * @return array Validation result
 */
function validate_program_number_format($program_number, $initiative_id = null) {
    if (empty($program_number)) {
        return ['valid' => true, 'message' => 'Empty number is allowed'];
    }
    
    // Check basic format: number.number
    if (!preg_match('/^\d+\.\d+$/', $program_number)) {
        return [
            'valid' => false,
            'message' => 'Program number must be in format: initiative.sequence (e.g., 30.1)'
        ];
    }
    
    // If initiative_id provided, validate against it
    if ($initiative_id) {
        global $conn;
        
        $initiative_query = "SELECT initiative_number FROM initiatives WHERE initiative_id = ?";
        $stmt = $conn->prepare($initiative_query);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $initiative = $result->fetch_assoc();
        
        if ($initiative && $initiative['initiative_number']) {
            $expected_prefix = $initiative['initiative_number'] . '.';
            if (!str_starts_with($program_number, $expected_prefix)) {
                return [
                    'valid' => false,
                    'message' => "Program number should start with {$expected_prefix}"
                ];
            }
        }
    }
    
    return ['valid' => true, 'message' => 'Valid program number format'];
}

/**
 * Get all programs with their hierarchical numbers for an initiative
 * 
 * @param int $initiative_id The initiative ID
 * @return array Array of programs with their numbers
 */
function get_initiative_programs_with_numbers($initiative_id) {
    global $conn;
    
    $query = "SELECT p.program_id, p.program_name, p.program_number, p.created_at
              FROM programs p 
              WHERE p.initiative_id = ? 
              ORDER BY CAST(SUBSTRING_INDEX(COALESCE(p.program_number, '0.0'), '.', -1) AS UNSIGNED)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Renumber all programs in an initiative to fix gaps or inconsistencies
 * 
 * @param int $initiative_id The initiative ID
 * @return array Result array with success status
 */
function renumber_initiative_programs($initiative_id) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Get initiative number
        $initiative_query = "SELECT initiative_number FROM initiatives WHERE initiative_id = ?";
        $stmt = $conn->prepare($initiative_query);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $initiative = $result->fetch_assoc();
        
        if (!$initiative || !$initiative['initiative_number']) {
            throw new Exception('Initiative not found or has no number');
        }
        
        $initiative_number = $initiative['initiative_number'];
        
        // Get all programs for this initiative ordered by creation date
        $programs_query = "SELECT program_id FROM programs 
                          WHERE initiative_id = ? 
                          ORDER BY created_at, program_id";
        
        $stmt = $conn->prepare($programs_query);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sequence = 1;
        $updated_count = 0;
        
        while ($program = $result->fetch_assoc()) {
            $new_program_number = $initiative_number . '.' . $sequence;
            
            $update_query = "UPDATE programs SET program_number = ? WHERE program_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_program_number, $program['program_id']);
            
            if ($update_stmt->execute()) {
                $updated_count++;
                $sequence++;
            }
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'updated_count' => $updated_count,
            'message' => "Renumbered {$updated_count} programs for initiative {$initiative_number}"
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => 'Failed to renumber programs: ' . $e->getMessage()
        ];
    }
}

/**
 * Check if a program number is already in use
 * 
 * @param string $program_number The program number to check
 * @param int $exclude_program_id Optional program ID to exclude from check
 * @return bool True if number is available, false if in use
 */
function is_program_number_available($program_number, $exclude_program_id = null) {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM programs WHERE program_number = ?";
    $params = [$program_number];
    $types = "s";
    
    if ($exclude_program_id) {
        $query .= " AND program_id != ?";
        $params[] = $exclude_program_id;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] == 0;
}

/**
 * Get next available program number for manual assignment
 * This is useful when users want to manually set a specific number
 * 
 * @param string $initiative_number The initiative number
 * @param int $desired_sequence Optional desired sequence number
 * @return string Available program number
 */
function get_available_program_number($initiative_number, $desired_sequence = null) {
    global $conn;
    
    if ($desired_sequence) {
        $test_number = $initiative_number . '.' . $desired_sequence;
        if (is_program_number_available($test_number)) {
            return $test_number;
        }
    }
    
    // Find the next available sequence number
    $sequence = 1;
    do {
        $test_number = $initiative_number . '.' . $sequence;
        if (is_program_number_available($test_number)) {
            return $test_number;
        }
        $sequence++;
    } while ($sequence <= 1000); // Safety limit
    
    return null;
}
?>
