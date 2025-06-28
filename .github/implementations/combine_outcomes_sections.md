# Implementation: Combine Outcomes Sections into "Other Outcomes"

## Overview
Combine the separate "Submitted Outcomes" and "Draft Outcomes" sections in the agency outcomes page into a single unified "Other Outcomes" section, simplifying the user interface and removing the draft/submitted distinction.

## Current State Analysis

### Existing Sections in submit_outcomes.php:
1. **Important Outcomes** - Already unified ✅
2. **Submitted Outcomes** - Separate section for non-important submitted outcomes
3. **Draft Outcomes** - Separate section for non-important draft outcomes

### Target State:
1. **Important Outcomes** - Keep as is ✅  
2. **Other Outcomes** - Single unified section for all non-important outcomes

## Implementation Steps

### Step 1: ✅ Update Data Preparation
- [x] Verify that `$regular_outcomes` already contains merged outcomes
- [x] Ensure proper deduplication by `metric_id`

### Step 2: ✅ Replace Dual Sections with Single Section
- [ ] Remove the separate "Submitted Outcomes" section
- [ ] Remove the separate "Draft Outcomes" section  
- [ ] Create single "Other Outcomes" section using `$regular_outcomes`
- [ ] Update section styling and layout

### Step 3: ✅ Update UI Elements
- [ ] Change section title to "Other Outcomes"
- [ ] Update icons and styling to be neutral (not draft/submitted specific)
- [ ] Ensure proper action buttons (View & Edit, Delete)

### Step 4: ✅ Handle Empty States
- [ ] Update empty state messages
- [ ] Ensure proper conditional rendering

### Step 5: ✅ Testing & Validation
- [ ] Test with various outcome combinations
- [ ] Verify no duplicate entries
- [ ] Check responsive layout

## Technical Details

### Current Structure:
```php
// Two separate sections
<?php if (!empty($outcomes)): ?>
    <!-- Submitted Outcomes Section -->
<?php endif; ?>

<?php if (!empty($draft_outcomes)): ?>  
    <!-- Draft Outcomes Section -->
<?php endif; ?>
```

### Target Structure:
```php
// Single unified section
<?php if (!empty($regular_outcomes)): ?>
    <!-- Other Outcomes Section -->
<?php endif; ?>
```

---
**Status**: 🚧 In Progress
**Priority**: Medium
**Impact**: Agency UI, User Experience
