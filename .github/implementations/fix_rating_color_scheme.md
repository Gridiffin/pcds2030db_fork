# Fix Rating Color for "On Track for Year"

## Problem
The "On Track for Year" rating badge is currently displaying as blue (info class) but should be yellow (warning class) to match the expected color scheme.

## Current State
- "On Track for Year" uses `bg-info` class (blue)
- Should use `bg-warning` class (yellow)

## Tasks
- [x] Update rating map for draft programs table
- [x] Update rating map for finalized programs table 
- [x] Ensure consistent color across both tables
- [x] Verify CSS has correct text color for warning background

## Implementation Complete

### Changes Made:
1. **Draft Programs Table**: Changed "on-track-yearly" from `class: 'info'` to `class: 'warning'`
2. **Finalized Programs Table**: Changed "on-track-yearly" from `class: 'info'` to `class: 'warning'`
3. **CSS Verification**: Confirmed `.rating-badge.bg-warning` has `color: #212529 !important;` for proper text contrast

### Result:
- "On Track for Year" badges now display with yellow/orange gradient background
- Dark text ensures good readability
- Consistent appearance across both draft and finalized program tables

## Status: Complete
- **Started**: 2025-01-03
- **Completed**: 2025-01-03
