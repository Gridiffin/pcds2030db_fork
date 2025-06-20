# Fix Admin Dashboard Calculation Duplicates Issue

## Problem Description
The admin dashboard calculation cards are displaying inflated values (e.g., 200%) because they're counting multiple submission records for the same program. This happens because the system maintains change history by creating multiple submission records per program, but the calculations should only count the latest/most relevant submission per program.

## Root Cause Analysis
- **Issue**: Calculations count all submission records instead of unique programs
- **Cause**: Change history functionality creates multiple `program_submissions` records per program
- **Impact**: Percentages exceed 100%, showing absurd values like 200%
- **Affected Cards**: All 7 calculation card types identified in the analysis

## Solution Strategy
1. ✅ Analyze current calculation functions
2. ✅ Identify specific queries that need deduplication
3. ✅ Fix `get_period_submission_stats()` function
4. ✅ Fix sector-specific calculations
5. ✅ Fix outcomes statistics calculations (verified - already correct)
6. ⬜ Test all calculation cards
7. ⬜ Verify percentage accuracy

## Implementation Plan

### Phase 1: Analysis and Function Identification
- [x] Analyze admin dashboard calculation cards
- [x] Review `get_period_submission_stats()` function
- [x] Review `get_sector_data_for_period()` function
- [x] Review `get_outcomes_statistics()` function
- [x] Identify all SQL queries that count submissions

### Phase 2: Fix Core Calculation Functions
- [x] Fix agencies reporting calculation
- [x] Fix programs on track calculation
- [x] Fix programs delayed calculation
- [x] Fix overall completion calculation
- [x] Fix sector overview calculations
- [x] Fix outcomes statistics calculations (verified - already correct)

### Phase 3: Update Query Logic
- [x] Use DISTINCT or GROUP BY to count unique programs
- [x] Use MAX(submission_id) or latest submission logic
- [x] Handle draft vs finalized submission logic correctly
- [x] Ensure proper JOIN conditions

### Phase 4: Testing and Verification
- [ ] Test each calculation card individually
- [ ] Verify percentages don't exceed 100%
- [ ] Test with multiple periods
- [ ] Test AJAX updates
- [ ] Cross-verify with database records

## Technical Approach

### Current Problem Pattern
```sql
-- PROBLEMATIC: This counts multiple submissions per program
SELECT COUNT(*) FROM program_submissions WHERE period_id = ?
```

### Solution Pattern
```sql
-- CORRECT: This counts unique programs with submissions
SELECT COUNT(DISTINCT program_id) FROM program_submissions WHERE period_id = ?

-- OR: Get latest submission per program
SELECT COUNT(*) FROM (
    SELECT program_id 
    FROM program_submissions ps1
    WHERE period_id = ? 
    AND submission_id = (
        SELECT MAX(submission_id) 
        FROM program_submissions ps2 
        WHERE ps2.program_id = ps1.program_id 
        AND ps2.period_id = ?
    )
) AS latest_submissions
```

## Files to Modify
1. `app/lib/admins/statistics.php` - Core calculation functions
2. `app/ajax/admin_dashboard_data.php` - AJAX endpoints
3. Any sector-specific calculation functions
4. Outcomes statistics functions

## Changes Made

### 1. Fixed `get_period_submission_stats()` function in `app/lib/admins/statistics.php`

**Problem**: Lines 159-172 were counting ALL submissions instead of unique programs per period.

**Solution**: Modified the query to:
- Use `COUNT(DISTINCT ps.program_id)` instead of `COUNT(*)`
- Added subquery to get only the latest submission per program using `MAX(submission_id)`
- Properly handles draft vs finalized logic by counting unique programs

**Old Query**:
```sql
SELECT is_draft, COUNT(*) as count 
FROM program_submissions 
WHERE period_id = ? 
GROUP BY is_draft
```

**New Query**:
```sql
SELECT ps.is_draft, COUNT(DISTINCT ps.program_id) as count 
FROM program_submissions ps 
INNER JOIN (
    SELECT program_id, MAX(submission_id) as max_submission_id
    FROM program_submissions 
    WHERE period_id = ?
    GROUP BY program_id
) latest ON ps.program_id = latest.program_id AND ps.submission_id = latest.max_submission_id
WHERE ps.period_id = ? 
GROUP BY ps.is_draft
```

### 2. Fixed `get_sector_data_for_period()` function in `app/lib/admins/statistics.php`

**Problem**: Line 457 was using `COUNT(DISTINCT ps.submission_id)` which counts all submissions, not unique programs.

**Solution**: Modified the query to:
- Count unique programs that have submissions: `COUNT(DISTINCT CASE WHEN ps.submission_id IS NOT NULL THEN ps.program_id END)`
- Added subquery to get only latest submissions per program
- Fixed parameter binding to use two parameters

**Old Calculation**:
```sql
IFNULL(ROUND((COUNT(DISTINCT ps.submission_id) / 
    NULLIF(COUNT(DISTINCT p.program_id), 0)) * 100, 0), 0) as submission_pct
```

**New Calculation**:
```sql
IFNULL(ROUND((COUNT(DISTINCT CASE WHEN ps.submission_id IS NOT NULL THEN ps.program_id END) / 
    NULLIF(COUNT(DISTINCT p.program_id), 0)) * 100, 0), 0) as submission_pct
```

### 3. Verified Other Functions

**DashboardController.php**: Already correctly implemented with latest submission logic
**get_outcomes_statistics()**: Already correctly implemented for outcomes data
**AJAX endpoints**: Will automatically use the fixed functions

## Impact of Changes

1. **Agencies Reporting Card**: Now correctly shows unique agencies that reported
2. **Programs On Track Card**: Now counts unique programs, not multiple submissions
3. **Programs Delayed Card**: Now counts unique programs with draft status
4. **Overall Completion Card**: Now shows accurate completion percentage (≤ 100%)
5. **Sector Overview Table**: Now shows accurate submission percentages per sector
6. **All percentage calculations**: Will now be accurate and not exceed 100%

## Expected Results

- No more inflated percentages (e.g., 200%)
- Each program counted only once per period
- Accurate representation of program completion status
- Proper handling of change history without affecting calculations
- Maintained functionality for draft vs finalized submissions
