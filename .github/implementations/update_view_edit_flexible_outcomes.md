# Implementation: Update View/Edit Outcomes to Support Flexible Structure

## Overview
Update the view and edit outcomes pages to support the new flexible outcomes structure that extends beyond monthly tables, supporting the redesigned outcome creation system.

## Current Issue
The view and edit outcomes pages (`view_outcome.php`) are still designed around the old monthly-only structure and don't support the new flexible outcomes that can have:
- Custom table structures
- Variable column configurations
- Different time periods (not just monthly)
- Flexible data formats

## Analysis Required

### Step 1: ✅ Investigate Current State
- [x] Examine `app/views/agency/outcomes/view_outcome.php`
- [x] Check if there's a separate edit page or if edit is integrated
- [x] Understand current data structure assumptions
- [x] Identify hardcoded monthly logic

**Findings:**
- `view_outcome.php` handles classic (monthly) outcomes
- Flexible outcomes are redirected to `view_outcome_flexible.php`
- `view_outcome_flexible.php` exists but **doesn't support edit mode**
- The redirection preserves edit mode parameter but the flexible viewer ignores it

### Step 2: ✅ Examine New Flexible Structure
- [x] Review the new outcome creation system
- [x] Understand the flexible table schema
- [x] Identify data_json structure changes
- [x] Document supported table types

**Flexible Structure:**
- `table_structure_type`: 'monthly', 'quarterly', 'yearly', 'custom'
- `row_config`: JSON defining rows (id, label, type)
- `column_config`: JSON defining columns (id, label, type, unit)
- `data_json`: Organized as `data[row_id][column_id] = value`

### Step 3: ✅ Plan Updates
- [ ] Design dynamic table rendering logic
- [ ] Plan edit form generation based on table structure
- [ ] Consider validation requirements
- [ ] Plan backward compatibility

### Step 4: ✅ Implementation
- [ ] Update view page to handle flexible structures
- [ ] Update edit functionality to support all table types
- [ ] Implement dynamic form generation
- [ ] Add proper validation

### Step 5: ✅ Testing and Validation
- [ ] Test with monthly outcomes (backward compatibility)
- [ ] Test with new flexible outcomes
- [ ] Verify edit and save functionality
- [ ] Check data integrity

## Files to Examine
- `app/views/agency/outcomes/view_outcome.php`
- `app/views/agency/outcomes/edit_outcome.php` (if separate)
- New flexible outcome creation files for reference
- Database schema for outcomes

## Key Questions
1. What does the new flexible data_json structure look like?
2. How are different table types identified?
3. What validation rules apply to flexible outcomes?
4. Are there any UI/UX patterns established in the creation process?

---
**Status**: 🚧 Investigation Phase
**Priority**: High
**Impact**: Agency UI, Data Management
