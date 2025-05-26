# Fix View Outcome Page Warnings

**Date:** May 26, 2025  
**Status:** ✅ **COMPLETED**

## Issues to fix:

- [x] Warning: Undefined array key "submitted_by_username" in view_outcome.php line 165
- [x] Warning: Undefined variable $outcome_id in view_outcome.php line 100

## Problem Analysis:
1. The view_outcome.php file is attempting to access a non-existent array key "submitted_by_username"
2. An undefined variable $outcome_id is being used when it should be $metric_id

## Solution:

### 1. Fix submitted_by_username warning
- [x] Added submitted_by column to sector_outcomes_data table
- [x] Updated the get_outcome_data() function to join with users table and include username
- [x] Added proper null checking in the view template

### 2. Fix undefined $outcome_id variable
- [x] Updated edit button URL to use metric_id instead of outcome_id:
  ```php
  'url' => 'edit_outcome.php?metric_id=' . $metric_id
  ```

## Database Changes:
- [x] Added submitted_by column to sector_outcomes_data table:
  ```sql
  ALTER TABLE sector_outcomes_data 
  ADD COLUMN submitted_by INT NULL AFTER is_draft,
  ADD CONSTRAINT fk_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(user_id);
  ```

## SQL Changes:
- [x] Updated SQL in get_outcome_data() function to include user information:
  ```sql
  SELECT sod.*, s.sector_name, rp.year, rp.quarter, rp.status as period_status,
  u.username as submitted_by_username
  FROM sector_outcomes_data sod
  LEFT JOIN sectors s ON sod.sector_id = s.sector_id
  LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
  LEFT JOIN users u ON sod.submitted_by = u.user_id
  WHERE sod.metric_id = ? AND sod.is_draft = 0
  LIMIT 1
  ```

## Frontend Changes:
- [x] Updated the HTML template with proper null checking for submitted_by_username
- [x] Ensured consistent use of metric_id parameter instead of outcome_id
