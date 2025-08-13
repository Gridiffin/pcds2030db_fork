<?php
/**
 * Admin Edit Program Data Helper
 * 
 * Functions to retrieve edit program data for admin views
 */

/**
 * Get comprehensive edit program data for admin view
 * 
 * @param int $program_id Program ID
 * @return array|null Edit program data array or null if not found
 */
function get_admin_edit_program_data($program_id) {
    global $conn;
    
    if (!$program_id) return null;
    
    // Get program basic information with agency details
    $program_query = "SELECT p.*, 
                             i.initiative_name, 
                             i.initiative_number,
                             a.agency_name,
                             a.agency_id
                      FROM programs p 
                      LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id 
                      LEFT JOIN agency a ON p.agency_id = a.agency_id
                      WHERE p.program_id = ? AND p.is_deleted = 0";
    
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program = $result->fetch_assoc();
    
    if (!$program) return null;
    
    // Get agency information
    $agency_info = [
        'agency_id' => $program['agency_id'],
        'agency_name' => $program['agency_name'],
        'agency_acronym' => null // Not available in current schema
    ];
    
    // Get all agencies for dropdown (admin can move programs between agencies)
    $agencies = [];
    $agencies_query = "SELECT agency_id, agency_name 
                       FROM agency 
                       ORDER BY agency_name";
    
    $result = $conn->query($agencies_query);
    while ($agency = $result->fetch_assoc()) {
        $agencies[] = $agency;
    }
    
    // Get all active initiatives for dropdown
    $initiatives = [];
    $initiatives_query = "SELECT initiative_id, initiative_name, initiative_number, initiative_description
                          FROM initiatives 
                          ORDER BY initiative_name";
    
    $result = $conn->query($initiatives_query);
    while ($initiative = $result->fetch_assoc()) {
        $initiatives[] = $initiative;
    }
    
    // Get all sectors for dropdown (no sectors table in current schema)
    $sectors = [];
    
    return [
        'program' => $program,
        'agency_info' => $agency_info,
        'agencies' => $agencies,
        'initiatives' => $initiatives,
        'sectors' => $sectors,
        'permissions' => [
            'can_edit' => true,    // Admin can edit all
            'can_view' => true     // Admin can view all
        ]
    ];
}