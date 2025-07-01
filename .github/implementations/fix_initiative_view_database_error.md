# Fix Initiative View Issues - Implementation Plan

## Overview
This document outlines the fixes applied to resolve the database query error and improve the Recent Activity Feed in the agency-side "view initiative" page.

## Issues Identified
1. **Fatal Error**: Query references non-existent `outcomes` table 
2. **Incorrect Table Relationships**: Using wrong table names for recent activity feed

## Database Schema Analysis
After examining the actual database structure using DBCode tools, the correct tables are:

### Program Submissions Table (`program_submissions`)
- `submission_id` (primary key)
- `program_id` (foreign key to programs)
- `period_id` (foreign key to reporting_periods)
- `submitted_by` (foreign key to users)
- `content_json`
- `submission_date`
- `updated_at`
- `is_draft`

### Programs Table (`programs`)
- `program_id` (primary key)
- `program_name`
- `program_number`
- `initiative_id` (foreign key to initiatives)
- `owner_agency_id`
- `sector_id`
- Other fields...

### Users Table (`users`)
- `user_id` (primary key)
- `username`
- `agency_name`
- `role` (admin, agency, focal)
- Other fields...

## Implemented Fixes

### 1. Updated Database Query
**File**: `app/views/agency/initiatives/view_initiative.php` (lines ~477-491)

**Before** (Incorrect - references non-existent tables):
```sql
SELECT 
    p.program_name,
    p.program_number,
    a.agency_name,
    o.submission_date,
    o.outcome_id,
    'outcome' as activity_type
FROM outcomes o
JOIN programs p ON o.program_id = p.program_id
JOIN agencies a ON p.agency_id = a.agency_id
WHERE p.initiative_id = ? 
AND o.submission_date IS NOT NULL
ORDER BY o.submission_date DESC
LIMIT 10
```

**After** (Correct - uses actual database tables):
```sql
SELECT 
    p.program_name,
    p.program_number,
    u.agency_name,
    ps.submission_date,
    ps.submission_id,
    'submission' as activity_type,
    ps.is_draft
FROM program_submissions ps
JOIN programs p ON ps.program_id = p.program_id
JOIN users u ON ps.submitted_by = u.user_id
WHERE p.initiative_id = ? 
AND ps.submission_date IS NOT NULL
ORDER BY ps.submission_date DESC
LIMIT 10
```

**Key Changes**:
- Changed from `outcomes` to `program_submissions` table
- Changed from `agencies` to `users` table (which contains agency_name)
- Added `is_draft` field to distinguish draft vs submitted entries
- Updated field references to match actual table structure
- Changed activity_type from 'outcome' to 'submission'

### 2. Enhanced Activity Display
**File**: `app/views/agency/initiatives/view_initiative.php` (lines ~513-520)

**Enhancement**: Added status badges to show submission state
```php
Program Submission
<?php if ($activity['is_draft']): ?>
    <span class="badge bg-warning text-dark ms-2" style="font-size: 0.7em;">Draft</span>
<?php else: ?>
    <span class="badge bg-success ms-2" style="font-size: 0.7em;">Submitted</span>
<?php endif; ?>
```

This provides visual indication of whether submissions are drafts or finalized.

## Verification Steps
1. ✅ Database schema verified using DBCode tools
2. ✅ Query updated to use correct table names and relationships
3. ✅ Activity display enhanced with submission status
4. ✅ Page testing completed - no PHP syntax errors
5. ✅ Database query tested successfully with real data

## Expected Results
- ✅ Fatal error eliminated - page loads without database errors
- ✅ Recent Activity Feed displays actual program submissions
- ✅ Submissions show appropriate status (Draft/Submitted)
- ✅ Activity feed shows latest 10 submissions for the initiative
- ✅ Proper agency attribution via users.agency_name

## Testing Results
- ✅ Query executes successfully without errors
- ✅ Returns proper data structure with correct fields
- ✅ Initiative ID 3 shows 5 recent submissions correctly
- ✅ PHP file passes syntax validation
- ✅ CSS properly imported and no progress bar conflicts

## IMPLEMENTATION COMPLETED ✅

## Follow-up Actions
- Monitor for any additional database-related issues
- Consider adding more activity types in the future (attachments, reports, etc.)
- Potential enhancement: Add filtering options for the activity feed

## Testing Notes
The fixes address the core database error while maintaining the existing UI design and improving functionality with status indicators.
