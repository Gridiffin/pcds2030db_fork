# Implementation: Exclude Draft Programs from Reports

## Problem
Currently, the report generation system allows draft programs to be selected for inclusion in PowerPoint slides. However, draft programs should not be included in reports as they may contain incomplete or unreviewed information.

## Solution
Modify the system to exclude draft programs from being selected in the report generator UI. This ensures that only finalized program submissions can be included in generated reports.

## Implementation Tasks

- [x] Analyze how programs are fetched for report selection (`get_period_programs.php`)
- [x] Update the SQL query in `get_period_programs.php` to exclude draft programs
- [x] Update the parameter binding in `get_period_programs.php` to match the modified query
- [x] Update informational log messages to reflect that only non-draft programs are included
- [x] Update UI text and tooltips to inform users that only finalized programs are available

## Technical Details

The existing implementation filtered out draft programs during the actual report generation in `report_data.php`, but it still allowed draft programs to be selected in the UI. 

The main changes were made in `get_period_programs.php`:

1. Modified the JOIN to only include programs with non-draft submissions:
```php
LEFT JOIN (
    SELECT program_id FROM program_submissions 
    WHERE period_id = ? AND is_draft = 0
) ps ON p.program_id = ps.program_id
```

2. Changed the WHERE clause to require that a non-draft submission exists:
```php
WHERE (ps.program_id IS NOT NULL)
```

3. Updated parameter binding to account for the changed query structure.

4. Updated log messages to be more specific about excluding draft programs.

This implementation ensures consistency between the programs shown in the UI and those that will actually appear in the generated reports.
