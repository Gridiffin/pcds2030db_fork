# Admin Program Details - Draft Programs Popup & Initiative Details Button

## Overview
Enhanced the admin program details page with two new features:
1. **Draft Program Popup**: Shows a modal when clicking on related programs that are still in draft status
2. **Initiative Details Button**: Adds direct navigation to admin initiative details page

## Implementation Date
August 11, 2025

## Changes Made

### 1. Database Layer Enhancement
**File**: `app/lib/admins/admin_program_details_data.php`

- Modified the related programs query to include draft status detection
- Added `is_draft_only` field that checks if a program has only draft submissions or no submissions
- Uses LEFT JOIN with `program_submissions` to determine draft status

```sql
CASE 
    WHEN COUNT(ps.submission_id) = 0 THEN 1
    WHEN COUNT(CASE WHEN ps.is_draft = 0 THEN 1 END) = 0 THEN 1
    ELSE 0
END as is_draft_only
```

### 2. UI Enhancement - Initiative Details Button
**File**: `app/views/admin/programs/partials/admin_program_details_content.php`

- Added "View Initiative Details" button in the initiative information card header
- Button links to `app/views/admin/initiatives/view_initiative.php?id={initiative_id}`
- Styled with Bootstrap outline-primary button with external link icon

### 3. UI Enhancement - Draft Program Handling
**File**: `app/views/admin/programs/partials/admin_program_details_content.php`

- Modified related programs display to check `is_draft_only` status
- Draft programs show:
  - Lock icon (`fas fa-lock`)
  - "Draft" badge with warning styling
  - Muted text color
  - Tooltip indicating draft status
  - Click handler that triggers modal instead of navigation

### 4. Modal Implementation
**File**: `app/views/admin/programs/partials/admin_program_details_content.php`

- Added Bootstrap modal `#draftProgramModal`
- Modal displays:
  - Warning icon and professional messaging
  - Dynamic program name insertion
  - Explanation that only finalized submissions are visible in admin view

### 5. JavaScript Implementation
**File**: `app/views/admin/programs/partials/admin_program_details_content.php`

- Added JavaScript for:
  - Bootstrap tooltip initialization
  - Draft program link click handling
  - Modal display with dynamic content
  - Event prevention for draft program links

## Features

### Draft Program Detection
- Programs with no submissions are considered draft
- Programs with only draft submissions (no finalized) are considered draft
- Finalized programs allow normal navigation

### User Experience
- Clear visual indication of draft status (lock icon, badge, muted styling)
- Informative tooltip on hover
- Professional modal popup explaining why access is restricted
- Smooth user flow without broken links

### Admin Navigation
- Direct access to initiative details from program context
- Maintains admin perspective across related pages
- Consistent button styling and iconography

## Technical Details

### Query Performance
- Single query fetches all related programs with draft status
- GROUP BY ensures one record per program
- Efficient COUNT operations for draft detection

### Security Considerations
- No changes to access control logic
- Maintains admin-only visibility of finalized data
- Proper HTML escaping for all dynamic content

### Compatibility
- Uses existing Bootstrap 5 modal system
- Compatible with current tooltip implementation
- No breaking changes to existing functionality

## Testing Recommendations

1. **Test with draft programs**: Verify modal appears when clicking draft related programs
2. **Test with finalized programs**: Ensure normal navigation works
3. **Test initiative button**: Verify correct navigation to initiative details
4. **Test mixed scenarios**: Programs with both draft and finalized submissions
5. **Test responsive design**: Ensure modal and button work on mobile devices

## Files Modified

1. `app/lib/admins/admin_program_details_data.php` - Data layer enhancement
2. `app/views/admin/programs/partials/admin_program_details_content.php` - UI implementation

## Build Status
✅ Successfully compiled with Vite build system
✅ No breaking changes detected
✅ All existing functionality preserved
