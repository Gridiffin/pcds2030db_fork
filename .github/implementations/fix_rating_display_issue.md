# Fix Rating Display Issue in Recent Program Updates

## Problem Description
Rating pills in the "Recent Program Updates" section of the agency dashboard are showing incorrect ratings - all defaulting to "not started" instead of displaying the actual program ratings. This is a data display issue where the rating logic appears correct in PHP but the actual rating values aren't being retrieved or displayed properly.

## Investigation Plan

### Step 1: Database Structure Analysis
- [ ] Use DBCode extension to examine database connections
- [ ] Check the structure of program-related tables
- [ ] Verify rating field exists and contains data
- [ ] Examine the query used to populate `$recentUpdates`

### Step 2: Controller Analysis  
- [ ] Review DashboardController's `getRecentUpdates()` method
- [ ] Check if rating field is included in the database query
- [ ] Verify data transformation between database and display

### Step 3: Data Flow Verification
- [ ] Trace how `$recentUpdates` data flows from controller to view
- [ ] Check if rating data is being lost during processing
- [ ] Verify the `$program['rating']` fallback logic

### Step 4: Fix Implementation
- [ ] Correct the database query to include rating data
- [ ] Update any missing field mappings
- [ ] Test rating display functionality

### Step 5: Testing & Validation
- [ ] Verify correct rating pills are displayed
- [ ] Test with different rating types
- [ ] Ensure no regression in other functionality

## Current State
- Rating display logic in dashboard.php appears correct: `$rating = $program['rating'] ?? 'not-started';`
- Previous status→rating conversion completed successfully
- Need to investigate why `$program['rating']` is not being populated with actual rating values

## Files to Examine
- `app/controllers/DashboardController.php` - Main controller handling dashboard data
- Database tables related to programs and ratings
- Any rating calculation or aggregation logic
