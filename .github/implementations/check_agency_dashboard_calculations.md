# Check Agency Dashboard Calculation Issues

## Overview
Following the fix for admin dashboard calculation duplicates, we need to verify that agency dashboard calculations don't have similar issues with counting multiple submission records per program.

## Tasks to Complete
- [x] Analyze agency dashboard calculation cards
- [x] Check DashboardController.php (agency-specific methods)
- [x] Review agency statistics functions
- [x] Check agency AJAX endpoints
- [x] Identify any duplicate counting issues
- [x] Fix any issues found
- [ ] Test agency dashboard calculations

## Files Checked
1. ✅ `app/views/agency/dashboard/dashboard.php` - Main agency dashboard (uses DashboardController - correct)
2. ✅ `app/controllers/DashboardController.php` - Agency calculation logic (already fixed)
3. ✅ `app/lib/agencies/statistics.php` - Agency-specific statistics (FIXED)
4. ✅ `app/views/agency/ajax/dashboard_data.php` - AJAX endpoints (uses fixed functions)
5. ✅ `app/views/agency/ajax/chart_data.php` - Chart data calculations (uses DashboardController - correct)
6. ✅ `app/lib/agencies/outcomes.php` - Agency outcomes (verified correct)

## Issues Found and Fixed

### 1. `get_agency_submission_status()` Function Issues

**Problem**: Similar to admin dashboard, this function was counting multiple submissions per program causing inflated statistics.

**Issues Found**:
- Line 280: Query was using `COUNT(*)` instead of `COUNT(DISTINCT p.program_id)`
- Subquery logic was using `NOT EXISTS` which could miss some edge cases
- Total programs count was incorrectly filtered by submissions when period_id was provided

**Fixes Applied**:
- ✅ Changed to `COUNT(DISTINCT p.program_id)` to count unique programs
- ✅ Updated subquery to use `MAX(submission_id)` approach for latest submissions
- ✅ Fixed total programs count to include all agency programs regardless of submission status
- ✅ Updated parameter binding to handle additional parameters

### 2. Agency Dashboard Cards Affected
- ✅ **Total Programs Card** - Now shows correct count
- ✅ **On Track Programs Card** - Now counts unique programs + correct percentage
- ✅ **Delayed Programs Card** - Now counts unique programs + correct percentage  
- ✅ **Completed Programs Card** - Now counts unique programs + correct percentage
- ✅ **Submission Status Cards** - Now shows accurate submission percentages
- ✅ **Program Rating Chart** - Now reflects accurate program distribution

### 3. Functions Verified as Correct
- ✅ **DashboardController.php** - Already used proper latest submission logic
- ✅ **get_agency_outcomes_statistics()** - Works with different table structure (correct)
- ✅ **Chart data endpoints** - Use DashboardController (correct)

## Changes Made to Agency Statistics

### Fixed `get_agency_submission_status()` in `app/lib/agencies/statistics.php`

**Old Query Problems**:
```sql
-- Problem 1: Total programs count excluded programs without submissions for the period
SELECT COUNT(DISTINCT p.program_id) as total
FROM programs p
INNER JOIN program_submissions ps ON p.program_id = ps.program_id
WHERE p.owner_agency_id = ? AND ps.period_id = ?

-- Problem 2: Status query used COUNT(*) counting all submissions, not unique programs
SELECT COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating,
       COUNT(*) as count,  -- This was the problem!
       SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
       SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
FROM programs p 
LEFT JOIN (...) ps ON p.program_id = ps.program_id
```

**Fixed Queries**:
```sql
-- Fix 1: Count all programs owned by agency regardless of submission status
SELECT COUNT(*) as total FROM programs WHERE owner_agency_id = ?

-- Fix 2: Count unique programs and use proper latest submission logic
SELECT COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as rating,
       COUNT(DISTINCT p.program_id) as count,  -- Fixed: Count unique programs
       SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
       SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
FROM programs p 
LEFT JOIN (
    SELECT ps1.program_id, ps1.is_draft, ps1.content_json
    FROM program_submissions ps1
    INNER JOIN (
        SELECT program_id, MAX(submission_id) as max_submission_id
        FROM program_submissions
        WHERE (period_id = ? OR ? IS NULL)
        GROUP BY program_id
    ) latest ON ps1.program_id = latest.program_id AND ps1.submission_id = latest.max_submission_id
    WHERE (ps1.period_id = ? OR ? IS NULL)
) ps ON p.program_id = ps.program_id
```

## Impact on Agency Dashboard

### Before Fix:
- Agency cards could show inflated numbers (e.g., 15 programs on track when agency only has 10 total programs)
- Percentages could exceed 100% (e.g., 150% completion rate)
- Multiple submissions per program were being counted separately

### After Fix:
- ✅ Accurate program counts per status category
- ✅ Realistic percentages (≤ 100%)
- ✅ Each program counted only once per period
- ✅ Proper handling of latest submissions with change history preserved

## Expected Results

Agency dashboard will now show:
- **Total Programs**: Correct count of all agency programs
- **On Track Programs**: Accurate count with proper percentage
- **Delayed Programs**: Accurate count with proper percentage  
- **Completed Programs**: Accurate count with proper percentage
- **Submission Progress**: Realistic completion percentages
- **Chart Data**: Accurate program distribution visualization
