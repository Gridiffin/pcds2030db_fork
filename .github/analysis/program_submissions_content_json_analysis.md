# Program Submissions Content JSON Analysis

## Overview
This document analyzes the `content_json` column in the `program_submissions` table and explains how to navigate to the edit programs functionality in the agency side.

## Navigation to Edit Programs (Agency Side)
### File Path: 
`app/views/agency/programs/update_program.php`

### Access Method:
1. Login as an agency or focal user
2. Navigate to `view_programs.php` (main programs list)
3. Click "Edit" button for any program 
4. URL format: `update_program.php?id={program_id}&period_id={period_id}`

### User Permissions:
- **Agency Users**: Can edit their own agency's programs
- **Focal Users**: Can edit programs across all agencies (cross-agency access)
- **Edit Restrictions**: Based on `edit_permissions` field in programs table and finalization status

## Database Structure

### Program Submissions Table
```sql
CREATE TABLE program_submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    period_id INT NOT NULL,
    submitted_by INT NOT NULL,
    content_json TEXT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_draft TINYINT NOT NULL DEFAULT 0,
    
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id),
    FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);
```

### Content JSON Structure
The `content_json` column stores program submission data in JSON format with the following structure:

#### Current Structure (Enhanced Format)
```json
{
    "rating": "not-started|in-progress|completed|delayed",
    "targets": [
        {
            "target_number": "30.1A.1",
            "target_text": "Description of target/objective",
            "status_description": "Current status description",
            "target_status": "not-started|in-progress|completed|delayed",
            "start_date": "2025-01-01",
            "end_date": "2025-12-31"
        }
    ],
    "remarks": "Additional notes or comments",
    "brief_description": "Brief summary of the program",
    "program_name": "Name of the program",
    "program_number": "Program identification number (e.g., 31.2A)",
    "changes_made": [
        {
            "field": "field_name",
            "field_label": "Human readable field name",
            "before": "Previous value",
            "after": "New value", 
            "change_type": "added|removed|modified"
        }
    ]
}
```

#### Sample Real Data
```json
{
  "rating": "not-started",
  "remarks": "",
  "targets": [
    {
      "target_text": "target itu ini",
      "status_description": "status ini itu"
    }
  ],
  "changes_made": [
    {
      "after": null,
      "field": "outcome_links",
      "before": "Certification of FMU & FPMU",
      "change_type": "removed",
      "field_label": "Linked Outcomes"
    }
  ],
  "program_name": "real testing (date ada, attach ada) edit1",
  "program_number": "31.2A",
  "brief_description": "brief summary of this description"
}
```

#### Legacy Structure (Backward Compatibility)
```json
{
    "target": "Semicolon-separated targets",
    "status_description": "Semicolon-separated status descriptions",
    "status": "overall_status",
    "remarks": "Additional remarks"
}
```

## Key Fields Explanation

### Core Fields
- **rating**: Program completion status
  - `not-started`: Program has not begun
  - `in-progress`: Program is currently active
  - `completed`: Program has been finished
  - `delayed`: Program is behind schedule

- **targets**: Array of program objectives with enhanced structure
  - `target_number`: Hierarchical numbering (optional) - format: {initiative}.{program}.{target}
  - `target_text`: Description of the target/objective
  - `status_description`: Current status description
  - `target_status`: Individual target status (not-started, in-progress, completed, delayed)
  - `start_date`: Optional start date for the target
  - `end_date`: Optional end date for the target
  - Supports multiple targets per program
  - Replaces legacy semicolon-separated format

- **brief_description**: Summary of what the program does

- **program_name**: Current name of the program (may differ from programs table)

- **program_number**: Program identification code

- **remarks**: Additional notes or comments about the program

### Target Numbering System
- **Format**: `{initiative}.{program}.{target}` (e.g., 30.1A.1, 30.1A.2)
- **Hierarchy**: Follows same restrictions as program numbers
- **Optional**: Target numbers are not required but recommended for organization
- **Validation**: Must start with the parent program number
- **Uniqueness**: Each target number must be unique within the program
- **Auto-generation**: System suggests next available number based on existing targets

### Change Tracking
- **changes_made**: Array tracking modifications made during this submission
  - Tracks field changes, additions, and removals
  - Used for audit trail and history
  - Particularly important for outcome links

## Data Evolution

### Legacy to Modern Migration
The system handles both old and new data formats:

1. **Legacy Format**: Used semicolon-separated values in single fields
2. **Modern Format**: Uses structured arrays for better data management
3. **Migration Logic**: Automatically converts legacy data when accessed

### Backward Compatibility
```php
// Legacy data handling in update_program.php
if (strpos($target_text, ';') !== false) {
    $target_parts = array_map('trim', explode(';', $target_text));
    $status_parts = array_map('trim', explode(';', $status_description));
    
    $targets = [];
    foreach ($target_parts as $index => $target_part) {
        if (!empty($target_part)) {
            $targets[] = [
                'target_text' => $target_part,
                'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
            ];
        }
    }
}
```

## Form Processing

### Save Draft Process
1. Collect form data (program info + submission content)
2. Update programs table (basic info)
3. Create new submission record (preserves history)
4. Store content as JSON in content_json field
5. Set is_draft = 1

### Finalization Process
1. Validate content exists and is complete
2. Check targets and rating are provided
3. Update is_draft = 0
4. Set final submission_date

### Data Validation
- Content cannot be empty
- Must have at least one target
- Must have a rating
- Program name and number validation

## Related Files

### Core Files
- `app/views/agency/programs/update_program.php` - Main edit interface
- `app/views/agency/programs/view_programs.php` - Programs listing
- `app/views/agency/programs/program_details.php` - View program details

### Supporting Libraries
- `lib/agencies/index.php` - Agency-specific functions
- `lib/rating_helpers.php` - Rating utilities
- `lib/audit_log.php` - Change tracking
- `lib/session.php` - User permissions

### JavaScript Components
- `assets/js/agency/program_management.js` - Form interactions
- `assets/js/utilities/rating_utils.js` - Rating handling
- `assets/js/utilities/program-history.js` - History display

## Database Relationships

### Primary Relationships
```sql
program_submissions.program_id → programs.program_id
program_submissions.period_id → reporting_periods.period_id  
program_submissions.submitted_by → users.user_id
```

### Data Flow
1. **Programs Table**: Stores basic program information
2. **Program Submissions**: Stores period-specific progress data
3. **Reporting Periods**: Defines submission timeframes
4. **Users**: Tracks who submitted what

## Performance Considerations

### Indexing
- `idx_program_period_draft` index on (program_id, period_id, is_draft)
- Optimizes queries for finding submissions by program and period

### JSON Operations
- Uses `JSON_PRETTY()` for readable output
- JSON parsing in PHP for data manipulation
- Consider JSON column type for MySQL 5.7+ for better performance

## Security Measures

### Data Validation
- Input sanitization before JSON encoding
- SQL parameterized queries
- User permission checks before allowing edits

### Audit Trail
- Every submission creates a new record (preserves history)
- Change tracking in changes_made array
- Audit logging for all operations

## Usage Examples

### Querying Submissions
```sql
-- Get latest submission for a program in current period
SELECT ps.*, JSON_PRETTY(ps.content_json) as formatted_content
FROM program_submissions ps
WHERE ps.program_id = 261 
  AND ps.period_id = 2
ORDER BY ps.submission_id DESC 
LIMIT 1;
```

### Extracting JSON Data
```sql
-- Extract specific fields from JSON
SELECT 
    submission_id,
    JSON_EXTRACT(content_json, '$.rating') as rating,
    JSON_EXTRACT(content_json, '$.targets') as targets,
    JSON_EXTRACT(content_json, '$.brief_description') as description
FROM program_submissions 
WHERE content_json IS NOT NULL;
```

## Conclusion

The `content_json` column in `program_submissions` provides a flexible, versioned storage mechanism for program progress data. The system maintains backward compatibility while supporting modern structured data formats, making it suitable for complex program management requirements.

The edit functionality in `update_program.php` provides a comprehensive interface for agencies to manage their program submissions with proper validation, audit tracking, and permission controls.
