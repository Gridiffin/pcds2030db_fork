<?php
/**
 * Fixed SQL for get_admin_programs_list function
 * This removes references to deleted status and description columns
 * And filters out draft programs
 */

// NEW SIMPLIFIED SQL - Remove status column references and filter drafts
$sql = "SELECT 
    p.program_id, p.program_name, p.owner_agency_id, p.sector_id, p.created_at,
    s.sector_name, 
    u.agency_name,
    ps.submission_id, ps.is_draft, ps.submission_date, ps.updated_at, ps.period_id AS submission_period_id
FROM programs p
JOIN sectors s ON p.sector_id = s.sector_id
JOIN users u ON p.owner_agency_id = u.user_id
LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?
WHERE (ps.is_draft = 0 OR ps.is_draft IS NULL)"; // Only show final submissions or no submissions

// REMOVE THESE PROBLEMATIC PARTS:
// 1. JSON_EXTRACT(ps.content_json, '$.status') as status  -- REMOVED (column deleted)
// 2. p.description references in search -- REMOVED (column deleted)  
// 3. Draft programs (ps.is_draft = 1) -- FILTERED OUT

echo "Fixed SQL query:\n";
echo $sql . "\n\n";

echo "Changes made:\n";
echo "1. Removed: JSON_EXTRACT(ps.content_json, '\$.status') as status\n";
echo "2. Added filter: WHERE (ps.is_draft = 0 OR ps.is_draft IS NULL)\n";
echo "3. Will need to remove p.description from search filters\n";
echo "4. Simplified button logic: Only show Unsubmit for final submissions\n";
?>