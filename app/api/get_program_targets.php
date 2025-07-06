<?php
/**
 * Get Program Targets API
 * 
 * Returns targets for selected programs to allow admin selection
 * Used for target selector feature in report generation
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Set content type
header('Content-Type: application/json');

try {
    // Get parameters
    $period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
    $sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 1;
    $selected_program_ids_raw = isset($_GET['selected_program_ids']) ? $_GET['selected_program_ids'] : '';

    // Validate required parameters
    if (!$period_id) {
        throw new Exception('Missing required parameter: period_id');
    }

    if (empty($selected_program_ids_raw)) {
        throw new Exception('Missing required parameter: selected_program_ids');
    }

    // Parse selected program IDs
    $selected_program_ids = [];
    if (is_string($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', explode(',', $selected_program_ids_raw));
    } elseif (is_array($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', $selected_program_ids_raw);
    }

    // Filter out invalid IDs
    $selected_program_ids = array_filter($selected_program_ids, function($id) {
        return $id > 0;
    });

    if (empty($selected_program_ids)) {
        throw new Exception('No valid program IDs provided');
    }

    // Handle half-yearly period logic (same as report_data.php)
    $period_ids = [$period_id];

    // Get period details to check if it's half-yearly
    $period_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period_result = $stmt->get_result();
    $period_data = $period_result->fetch_assoc();

    // Check if this is a half-yearly period
    if ($period_data && $period_data['period_type'] === 'half') {
        $period_number = (int)$period_data['period_number'];
        $year = $period_data['year'];
        
        if ($period_number == 1) { // Half Yearly 1 includes Q1 and Q2
            $q1q2_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (1, 2)";
            $stmt = $conn->prepare($q1q2_query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $q1q2_result = $stmt->get_result();
            
            $period_ids = [$period_id];
            while ($row = $q1q2_result->fetch_assoc()) {
                $period_ids[] = $row['period_id'];
            }
        } elseif ($period_number == 2) { // Half Yearly 2 includes Q3 and Q4
            $q3q4_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (3, 4)";
            $stmt = $conn->prepare($q3q4_query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $q3q4_result = $stmt->get_result();
            
            $period_ids = [$period_id];
            while ($row = $q3q4_result->fetch_assoc()) {
                $period_ids[] = $row['period_id'];
            }
        }
    }

    // Create the period IN clause
    $period_in_clause = implode(',', array_fill(0, count($period_ids), '?'));

    // Create placeholders for program IDs
    $program_placeholders = implode(',', array_fill(0, count($selected_program_ids), '?'));

    // Query to get programs with their targets (using same logic as report_data.php)
    $programs_query = "SELECT p.program_id, p.program_name, 
                        GROUP_CONCAT(ps.content_json ORDER BY rp.quarter ASC SEPARATOR '|||') as all_content_json,
                        GROUP_CONCAT(CONCAT(rp.quarter, ':', ps.period_id) ORDER BY rp.quarter ASC SEPARATOR ',') as period_info
                      FROM programs p
                      LEFT JOIN (
                          SELECT ps1.*
                          FROM program_submissions ps1
                          INNER JOIN (
                              SELECT program_id, period_id, MAX(submission_date) as latest_date, MAX(submission_id) as latest_submission_id
                              FROM program_submissions
                              WHERE period_id IN ($period_in_clause) AND is_draft = 0
                              GROUP BY program_id, period_id
                          ) ps2 ON ps1.program_id = ps2.program_id 
                               AND ps1.period_id = ps2.period_id
                               AND ps1.submission_date = ps2.latest_date 
                               AND ps1.submission_id = ps2.latest_submission_id
                          WHERE ps1.period_id IN ($period_in_clause) AND ps1.is_draft = 0
                      ) ps ON p.program_id = ps.program_id
                      LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                      WHERE p.program_id IN ($program_placeholders)
                      GROUP BY p.program_id, p.program_name
                      ORDER BY p.program_name";

    // Prepare parameters (period_ids twice for both IN clauses, then program_ids)
    $params = array_merge($period_ids, $period_ids, $selected_program_ids);
    $param_types = str_repeat('i', count($period_ids) * 2) . str_repeat('i', count($selected_program_ids));

    $stmt = $conn->prepare($programs_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $programs_with_targets = [];

    while ($program = $result->fetch_assoc()) {
        $program_targets = [];

        // Process aggregated content from multiple periods (same logic as report_data.php)
        $all_content_jsons = $program['all_content_json'] ? explode('|||', $program['all_content_json']) : [];
        $period_info = $program['period_info'] ? explode(',', $program['period_info']) : [];

        $target_counter = 1;

        // Process each period's content
        foreach ($all_content_jsons as $index => $content_json) {
            if (empty(trim($content_json))) continue;

            $content = json_decode($content_json, true);
            if (!$content) continue;

            // Extract period info for labeling
            $period_quarter = isset($period_info[$index]) ? explode(':', $period_info[$index])[0] : 'unknown';
            $period_label = "Q{$period_quarter}";

            // Process targets from this period
            if (isset($content['targets']) && is_array($content['targets']) && !empty($content['targets'])) {
                // New format with targets array
                foreach ($content['targets'] as $target_index => $t) {
                    $target_text = $t['target_text'] ?? $t['text'] ?? 'No target set';
                    $status_desc = $t['status_description'] ?? 'No status update available';

                    // Clean up newlines
                    $target_text = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $target_text);
                    $status_desc = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $status_desc);

                    $program_targets[] = [
                        'target_id' => $target_counter,
                        'target_number' => $target_counter,
                        'target_text' => $target_text,
                        'status_description' => $status_desc,
                        'period_label' => $period_label,
                        'period_quarter' => $period_quarter,
                        'source_period_id' => isset($period_info[$index]) ? explode(':', $period_info[$index])[1] : null,
                        'selected' => true // Default to selected
                    ];

                    $target_counter++;
                }
            } elseif (isset($content['target'])) {
                // Old format with direct target property
                $target_text = $content['target'] ?? 'No target set';
                $status_description = $content['status_text'] ?? 'No status update available';

                // Check if targets are semicolon-separated
                if (strpos($target_text, ';') !== false) {
                    $target_parts = array_map('trim', explode(';', $target_text));
                    $status_parts = array_map('trim', explode(';', $status_description));

                    foreach ($target_parts as $idx => $target_part) {
                        if (!empty($target_part)) {
                            $target_part = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $target_part);
                            $status_part = isset($status_parts[$idx]) ? $status_parts[$idx] : 'No status update available';
                            $status_part = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $status_part);

                            $program_targets[] = [
                                'target_id' => $target_counter,
                                'target_number' => $target_counter,
                                'target_text' => $target_part,
                                'status_description' => $status_part,
                                'period_label' => $period_label,
                                'period_quarter' => $period_quarter,
                                'source_period_id' => isset($period_info[$index]) ? explode(':', $period_info[$index])[1] : null,
                                'selected' => true
                            ];

                            $target_counter++;
                        }
                    }
                } else {
                    // Single target
                    $target_text = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $target_text);
                    $status_description = str_replace(['\\n', '\\r', '\\r\\n'], "\n", $status_description);

                    $program_targets[] = [
                        'target_id' => $target_counter,
                        'target_number' => $target_counter,
                        'target_text' => $target_text,
                        'status_description' => $status_description,
                        'period_label' => $period_label,
                        'period_quarter' => $period_quarter,
                        'source_period_id' => isset($period_info[$index]) ? explode(':', $period_info[$index])[1] : null,
                        'selected' => true
                    ];

                    $target_counter++;
                }
            }
        }

        // Add program to results if it has targets
        if (!empty($program_targets)) {
            $programs_with_targets[] = [
                'program_id' => $program['program_id'],
                'program_name' => $program['program_name'],
                'target_count' => count($program_targets),
                'targets' => $program_targets
            ];
        }
    }

    // Return the response
    echo json_encode([
        'success' => true,
        'period_info' => $period_data,
        'programs' => $programs_with_targets,
        'total_programs' => count($programs_with_targets),
        'total_targets' => array_sum(array_column($programs_with_targets, 'target_count'))
    ]);

} catch (Exception $e) {
    error_log("Error in get_program_targets.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
