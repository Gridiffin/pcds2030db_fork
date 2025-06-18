# Fix Report Generator Issues

## Problem
The report generator had two main issues:
1. It was showing duplicate entries for programs that have multiple submission records due to change history tracking
2. The rating color mapping for 'on-track-yearly' was incorrectly showing green instead of yellow in slide reports

## Solution
1. Modified the query in `get_period_programs.php` to only select programs with their latest non-draft submission for the specified period
2. Fixed the rating color mapping in the slide styler to properly handle 'on-track-yearly' as yellow

## Implementation Tasks

- [x] Analyze the current query logic in `get_period_programs.php`
- [x] Compare with the correct implementation in `report_data.php`
- [x] Update the query in `get_period_programs.php` to only select the latest submission for each program
- [x] Update parameter binding to account for the modified query
- [x] Fix the rating color mapping in `report-slide-styler.js` for 'on-track-yearly'
- [x] Test the changes to ensure no duplicates appear and correct colors are used

## Technical Details

The current implementation in `get_period_programs.php` uses a simple LEFT JOIN:

```php
LEFT JOIN (
    SELECT program_id FROM program_submissions 
    WHERE period_id = ? AND is_draft = 0
) ps ON p.program_id = ps.program_id
```

This doesn't account for multiple submissions per program and can lead to duplicates.

We need to modify it to match the correct implementation in `report_data.php`:

```php
LEFT JOIN (
    SELECT ps1.*
    FROM program_submissions ps1
    INNER JOIN (
        SELECT program_id, MAX(submission_date) as latest_date
        FROM program_submissions
        WHERE period_id = ? AND is_draft = 0
        GROUP BY program_id
    ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_date = ps2.latest_date
    WHERE ps1.period_id = ? AND ps1.is_draft = 0
) ps ON p.program_id = ps.program_id
```

This ensures we only get the latest submission for each program.
