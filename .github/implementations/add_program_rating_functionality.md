# Add Program Rating Functionality

## Problem
- Need to add program rating functionality to the edit program page
- Rating system has 4 options: monthly target achieved, not started, on track, severe delays
- Each rating has specific colors defined in rating_helpers.php
- Report data module currently uses ratings field from content JSON but is messy due to recent DB schema changes
- Need to replace all related fields to use the proper rating system

## Solution Steps

### 1. Database Schema Updates
- [ ] Add rating column to programs table if not exists
- [ ] Update any existing rating-related columns to use the new rating system

### 2. Edit Program Page Updates
- [x] Find the edit program page file
- [x] Add rating dropdown with the 4 options
- [x] Include rating_helpers.php for proper badge display
- [x] Add form handling for rating updates
- [x] Add AJAX endpoint for saving rating

### 3. Report Data Module Updates
- [x] Use grep to find all references to ratings in report data
- [x] Replace content JSON rating references with proper rating field
- [x] Update report generation to use rating_helpers.php for proper display
- [x] Ensure rating colors are properly applied in reports

### 4. JavaScript Updates
- [x] Update any JavaScript that handles program data to include rating
- [x] Add rating display logic using rating_helpers.php functions
- [x] Update form submission to include rating data

### 5. Testing
- [x] Test edit program page rating functionality
- [x] Test report generation with new rating system
- [x] Verify rating colors display correctly

## Implementation Details

### Rating Options (from rating_helpers.php):
- Monthly Target Achieved (success/green)
- Not Started (secondary/gray) 
- On Track (warning/yellow)
- Severe Delays (danger/red)

### Files to Update:
- Edit program page
- Report data module
- Any AJAX endpoints for program updates
- JavaScript files handling program data
- Database schema if needed

## Summary

✅ **COMPLETED** - Program rating functionality has been successfully implemented:

### What was implemented:
1. **Edit Program Page**: Added rating dropdown with 4 options (Monthly Target Achieved, Not Started, On Track, Severe Delays)
2. **Database Integration**: Updated `update_simple_program()` function to handle rating field updates
3. **Report Data Module**: Modified `report_data.php` to include rating field in program queries
4. **JavaScript Updates**: Updated `report-slide-styler.js` to map database rating values to correct colors
5. **Rating Helpers**: Updated `rating_helpers.php` to use correct database ENUM values

### Rating System:
- **Monthly Target Achieved** (success/green) - `monthly_target_achieved`
- **Not Started** (secondary/gray) - `not_started`
- **On Track** (warning/yellow) - `on_track_for_year`
- **Severe Delays** (danger/red) - `severe_delay`

### Key Changes:
- Rating field is now properly saved to the programs table
- Report generation uses the actual rating field instead of content JSON
- Rating colors are correctly mapped in PPTX reports
- All existing rating displays continue to work with the new system
- Backward compatibility maintained for legacy rating values

### Bug Fixes:
- Fixed database error "Data truncated for column 'rating'" by correcting the `convert_legacy_rating()` function to return proper database ENUM values (`not_started` instead of `not-started`)
- Updated fallback values in rating helper functions to use underscore format instead of hyphen format 