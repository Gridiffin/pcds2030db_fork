<?php
/**
 * Hierarchical Program Numbering System
 * 
 * This module provides functions for managing hierarchical program numbers
 * where programs are numbered based on their parent initiative number.
 * Format: Initiative.Sequence (e.g., 30.1, 30.2, 30.3)
 */

// Program Number Format Configuration
const PROGRAM_NUMBER_SEPARATOR = '.';
const PROGRAM_NUMBER_MAX_SEQUENCE = 1000;            // Safety limit for sequence generation only

// Flexible regex patterns - supports unlimited sub-levels
const PROGRAM_NUMBER_REGEX_INITIATIVE_PREFIX = '/^\d+\./';              // Must start with initiative number + dot
const PROGRAM_NUMBER_REGEX_FLEXIBLE = '/^\d+\.[\w\.]+$/';               // Initiative.anything (letters, numbers, dots)
const PROGRAM_NUMBER_REGEX_BASIC = '/^[\w\.]+$/';                       // Most permissive: letters, numbers, dots, but at least one dot

/**
 * Centralized program number validation function
 * 
 * @param string $program_number The program number to validate
 * @param bool $strict_format Whether to use strict format validation (default: false)
 * @return bool True if valid, false if invalid
 */
function is_valid_program_number_format($program_number, $strict_format = false) {
    if (empty($program_number)) {
        return true; // Empty numbers are allowed
    }
    
    // For strict format, ensure it starts with initiative number followed by dot
    if ($strict_format) {
        return preg_match(PROGRAM_NUMBER_REGEX_INITIATIVE_PREFIX, $program_number) === 1 &&
               preg_match(PROGRAM_NUMBER_REGEX_FLEXIBLE, $program_number) === 1;
    } else {
        // Basic validation - check for valid characters and at least one dot
        return preg_match(PROGRAM_NUMBER_REGEX_BASIC, $program_number) === 1 && 
               strpos($program_number, '.') !== false;
    }
}

/**
 * Get program number validation error message
 * 
 * @param bool $strict_format Whether strict format was used
 * @return string Error message for invalid format
 */
function get_program_number_format_error($strict_format = false) {
    if ($strict_format) {
        return 'Program number must start with initiative number followed by dot (e.g., 31.2, 31.25.6, 31.2A.3B)';
    } else {
        return 'Program number can only contain numbers, letters, and dots.';
    }
}

/**
 * Extract initiative number from a program number
 * 
 * @param string $program_number The program number to analyze
 * @return string|null Initiative number or null if invalid
 */
function get_initiative_from_program_number($program_number) {
    if (empty($program_number) || !preg_match(PROGRAM_NUMBER_REGEX_INITIATIVE_PREFIX, $program_number)) {
        return null;
    }
    
    $parts = explode(PROGRAM_NUMBER_SEPARATOR, $program_number);
    return $parts[0];
}

/**
 * Parse a program number into its components (flexible parsing)
 * 
 * @param string $program_number The program number to parse
 * @return array Array with components: initiative, suffix_parts
 */
function parse_program_number($program_number) {
    $result = [
        'initiative' => null,
        'suffix_parts' => []
    ];
    
    if (empty($program_number)) {
        return $result;
    }
    
    $parts = explode(PROGRAM_NUMBER_SEPARATOR, $program_number);
    if (count($parts) >= 2) {
        $result['initiative'] = $parts[0];
        $result['suffix_parts'] = array_slice($parts, 1); // Everything after initiative
    }
    
    return $result;
}

/**
 * Build a program number from components
 * 
 * @param string $initiative Initiative number
 * @param int $sequence Sequence number
 * @param string $letter Optional letter (for level 2 and 3)
 * @param int $subsequence Optional sub-sequence (for level 3)
 * @return string Formatted program number
 */
function build_program_number($initiative, $sequence, $letter = null, $subsequence = null) {
    $number = $initiative . PROGRAM_NUMBER_SEPARATOR . $sequence;
    
    if ($letter) {
        $number .= $letter;
        
        if ($subsequence) {
            $number .= PROGRAM_NUMBER_SEPARATOR . $subsequence;
        }
    }
    
    return $number;
}

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
    
    // Find the highest numeric sequence for this initiative
    // Use a simpler approach - just find next available integer
    $sequence_query = "SELECT program_number FROM programs 
                       WHERE initiative_id = ? AND program_number REGEXP ? 
                       ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(program_number, '.', 2), '.', -1) AS UNSIGNED) DESC 
                       LIMIT 1";
    
    // Pattern to match initiative.number (like 31.2, but not 31.2A or 31.25.6)
    $pattern = '^' . $initiative_number . '\\.[0-9]+$';
    $stmt = $conn->prepare($sequence_query);
    $stmt->bind_param("is", $initiative_id, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $next_sequence = 1;
    if ($row = $result->fetch_assoc()) {
        $last_number = $row['program_number'];
        // Extract the numeric part after the first dot
        $parts = explode(PROGRAM_NUMBER_SEPARATOR, $last_number);
        if (count($parts) >= 2 && is_numeric($parts[1])) {
            $next_sequence = intval($parts[1]) + 1;
        }
    }
    
    return $initiative_number . PROGRAM_NUMBER_SEPARATOR . $next_sequence;
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
                          ORDER BY 
                              CAST(SUBSTRING_INDEX(program_number, '.', 1) AS UNSIGNED),
                              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(program_number, '.', 2), '.', -1) AS UNSIGNED),
                              CASE WHEN program_number REGEXP '[A-Z]' THEN 
                                  SUBSTRING(program_number, LOCATE('.', program_number) + LENGTH(SUBSTRING_INDEX(program_number, '.', 1)) + 1, 1)
                              ELSE '' END,
                              CASE WHEN program_number REGEXP '[A-Z]\\.[0-9]+$' THEN 
                                  CAST(SUBSTRING_INDEX(program_number, '.', -1) AS UNSIGNED)
                              ELSE 0 END";
        
        $stmt = $conn->prepare($programs_query);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sequence = 1;
        $updated_count = 0;
        
        while ($program = $result->fetch_assoc()) {
            $new_program_number = $new_initiative_number . PROGRAM_NUMBER_SEPARATOR . $sequence;
            
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
    
    $new_number = generate_next_program_number($initiative_id, 1); // Default to Level 1
    
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
    
    // Check basic format using centralized validation
    if (!is_valid_program_number_format($program_number, true)) {
        return [
            'valid' => false,
            'message' => get_program_number_format_error(true)
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
            $expected_prefix = $initiative['initiative_number'] . PROGRAM_NUMBER_SEPARATOR;
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
            $new_program_number = $initiative_number . PROGRAM_NUMBER_SEPARATOR . $sequence;
            
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
        $test_number = $initiative_number . PROGRAM_NUMBER_SEPARATOR . $desired_sequence;
        if (is_program_number_available($test_number)) {
            return $test_number;
        }
    }
    
    // Find the next available sequence number
    $sequence = 1;
    do {
        $test_number = $initiative_number . PROGRAM_NUMBER_SEPARATOR . $sequence;
        if (is_program_number_available($test_number)) {
            return $test_number;
        }
        $sequence++;
    } while ($sequence <= PROGRAM_NUMBER_MAX_SEQUENCE); // Safety limit
    
    return null;
}
?>
