# Add Program Number Badge to Program Details Page

## Problem Statement
The program details page shows the program name but doesn't display the program number badge, which is inconsistent with other views in the system where program numbers are prominently displayed as blue badges.

## Solution Overview
Add program number badges in two locations in the program details page:
1. In the page header subtitle (next to the program name)
2. In the basic information section (next to the program name)

## Implementation Tasks

### ✅ Task 1: Locate Program Details File
- [x] Found file: `app/views/agency/programs/program_details.php`
- [x] Identified two locations where program name is displayed:
  - Line 170: Page header subtitle
  - Line 251: Basic information section

### 🔄 Task 2: Update Page Header Subtitle
- [ ] Modify the header config to include program number badge in subtitle
- [ ] Ensure proper HTML formatting for badge display

### 🔄 Task 3: Update Basic Information Section
- [ ] Add program number badge next to program name in the info table
- [ ] Follow the same badge styling used in other views (blue info badge)

### 🔄 Task 4: Test and Validate
- [ ] Test with programs that have program numbers
- [ ] Test with programs that don't have program numbers
- [ ] Ensure consistent styling with other views

## Technical Details

### Badge Implementation Pattern
Based on other views in the system, program numbers should be displayed as:
```html
<?php if (!empty($program['program_number'])): ?>
    <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
<?php endif; ?>
```

### Files to Modify
- `app/views/agency/programs/program_details.php`

### Styling Consistency
- Use `badge bg-info` class for blue background
- Add `me-2` for margin-right spacing
- Include tooltip with "Program Number" title
- Display before the program name

## Benefits
1. **Consistency**: Matches the program number display pattern used throughout the system
2. **Easy Identification**: Users can quickly identify programs by their numbers
3. **Better UX**: Consistent visual experience across all program views
