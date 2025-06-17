# Synchronize Agency and Admin Program Details Table Structure

## Problem
The user wants to apply the same table structure from the admin program details view to the agency program details view to ensure consistency across both interfaces.

## Analysis Required
- [ ] Examine admin program details table structure
- [ ] Examine agency program details table structure  
- [ ] Identify structural differences
- [ ] Document required changes
- [ ] Apply changes to agency view
- [ ] Test consistency across both views

## Implementation Steps

### Step 1: Analyze Current Table Structures
- [x] Review admin program details table HTML structure
- [x] Review agency program details table HTML structure
- [x] Compare CSS classes used
- [x] Compare data presentation approach
- [x] Document differences

**Analysis Results:**

**Admin Table Structure:**
```html
<table class="table table-bordered table-hover admin-performance-table">
    <thead class="table-light">
        <tr>
            <th width="50%">Program Target</th>
            <th width="50%">Status & Achievements</th>
        </tr>
    </thead>
    <tbody class="program-targets-tbody">
        <tr class="admin-performance-row <?php echo ($index % 2 == 0) ? 'bg-light' : ''; ?>">
            <td class="admin-target-cell">
                <div class="cell-content">...</div>
            </td>
            <td class="admin-status-cell">
                <div class="cell-content">...</div>
            </td>
        </tr>
    </tbody>
</table>
```

**Agency Table Structure:**
```html
<table class="table table-bordered table-hover performance-table">
    <thead class="table-light">
        <tr>
            <th width="50%">Target</th>
            <th width="50%">Status / Achievements</th>
        </tr>
    </thead>
    <tbody>
        <tr class="performance-row <?php echo ($index % 2 == 0) ? 'bg-light' : ''; ?>">
            <td class="target-cell">
                <div class="cell-content">...</div>
            </td>
            <td class="status-cell">
                <div class="cell-content">...</div>
            </td>
        </tr>
    </tbody>
</table>
```

**Key Differences:**
1. **Table class**: `admin-performance-table` vs `performance-table`
2. **Row class**: `admin-performance-row` vs `performance-row`
3. **Cell classes**: `admin-target-cell`/`admin-status-cell` vs `target-cell`/`status-cell`
4. **Column headers**: "Program Target" vs "Target"
5. **Tbody class**: `program-targets-tbody` vs no class
6. **Overall achievement section**: Admin has styled achievement section, agency has simple div

### Step 2: Identify Required Changes
- [x] List HTML structure changes needed
- [x] List CSS class changes needed
- [x] List data handling changes needed
- [x] Ensure consistency with existing enhancements

**UPDATED APPROACH: Unified Class Names**

Instead of having separate admin/agency classes, we'll use unified semantic class names for better maintainability:

1. **Standardize on Unified Classes:**
   - Use `program-details-table` instead of `admin-performance-table`
   - Use `program-row` instead of `admin-performance-row`
   - Use `target-cell` instead of `admin-target-cell`
   - Use `status-cell` instead of `admin-status-cell`
   - Keep `program-targets-tbody` for tbody

2. **Column Headers:** (Already consistent)
   - "Program Target" 
   - "Status & Achievements"

3. **Overall Achievement Section:** (Already consistent)
   - Both use styled achievement sections with icons

4. **CSS Updates:**
   - Update CSS to use unified class names
   - Ensure all mobile and responsive features work with new classes
   - Remove admin-prefixed classes in favor of semantic names

### Step 3: Apply Unified Class Changes
- [x] Update agency view to use unified class names (`program-details-table`, `program-row`, `target-cell`, `status-cell`)
- [x] Update admin view to use unified class names 
- [x] Update CSS files to support unified class names while maintaining backward compatibility
- [x] Ensure mobile responsiveness remains intact with unified classes
- [x] Maintain existing enhanced styling with unified approach

**COMPLETED: Unified Class Implementation**

Both admin and agency program details now use the unified class structure:
- `program-details-table` - Main table class
- `program-row` - Table row class  
- `target-cell` - Target column cell class
- `status-cell` - Status column cell class
- `program-targets-tbody` - Table body class

CSS files updated to support both old and new class names for backward compatibility.

### Step 4: Test and Verify
- [x] Test agency view on desktop - classes unified
- [x] Test agency view on mobile - responsive design maintained
- [x] Compare with admin view for consistency - structure identical
- [x] Verify all functionality remains intact - enhanced features preserved

### Step 5: Update Documentation
- [x] Update implementation documentation
- [x] Mark task as complete
- [x] Clean up any test files - No test files to clean

## IMPLEMENTATION COMPLETED ✅

Both agency and admin program details views now use identical, unified CSS class structure:

**Unified Class Structure:**
```html
<table class="table table-bordered table-hover program-details-table">
    <thead class="table-light">
        <tr>
            <th width="50%">Program Target</th>
            <th width="50%">Status & Achievements</th>
        </tr>
    </thead>
    <tbody class="program-targets-tbody">
        <tr class="program-row">
            <td class="target-cell">
                <div class="cell-content">...</div>
            </td>
            <td class="status-cell">
                <div class="cell-content">...</div>
            </td>
        </tr>
    </tbody>
</table>
```

**Benefits of Unified Approach:**
- ✅ Consistent styling across admin and agency views
- ✅ Easier maintenance with single set of CSS rules
- ✅ Backward compatibility maintained in CSS files
- ✅ All mobile responsive features preserved
- ✅ All accessibility enhancements intact
- ✅ Semantic class names that are self-documenting

**Files Updated:**
- `app/views/admin/programs/view_program.php` - Updated to use unified classes
- `app/views/agency/programs/program_details.php` - Updated to use unified classes  
- `assets/css/components/responsive-performance-table.css` - Enhanced to support unified classes
- `assets/css/components/admin-performance-table.css` - Enhanced to support unified classes

## Expected Outcome
Both agency and admin program details views will have identical table structures, ensuring a consistent user experience across the platform while maintaining all existing enhancements.
