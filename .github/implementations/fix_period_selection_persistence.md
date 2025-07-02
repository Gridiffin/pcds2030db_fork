# Fix Period Selection Persistence Issue

## Problem
When selecting a different reporting period (either half-yearly or quarterly), the page correctly refreshes with all URL parameters preserved, but the period selection itself doesn't persist. Instead, it defaults back to the current period (Half Year 2).

## Root Cause Analysis
This suggests there may be an issue with how the selected period is being processed server-side. The URL parameters are being preserved, but the server-side logic might be ignoring or overriding the period_id parameter.

## Investigation Steps
1. Check how period_id is being processed in view_all_sectors.php
2. Examine the period selection logic in period_selector_dashboard.php
3. Check if there's any JavaScript that might be resetting the selection
4. Verify session variables aren't overriding the URL parameters

## Potential Fixes
1. Ensure period_id from URL is correctly prioritized over session defaults
2. Fix the logic that selects the default period in the dropdown
3. Ensure the period selection is properly maintained during page reloads
4. Check for any "current period" logic that might be overriding the selection
