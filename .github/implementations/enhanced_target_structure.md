# Enhanced Target Structure Implementation

## Overview
Enhance the target structure in program submissions to include individual timelines, status indicators, and hierarchical target numbering.

## Requirements Summary
From the previous analysis, we need to enhance each target with:
1. **Target Number**: Hierarchical numbering (30.x.y format) tied to initiative and program numbers
2. **Individual Status**: Each target has its own status (not-started, in-progress, completed, delayed)
3. **Timeline**: Optional start and end dates for each target
4. **Existing Fields**: Keep target_text and status_description

## New Content JSON Structure
```json
{
    "rating": "program_overall_status",
    "targets": [
        {
            "target_number": "30.1A.1",
            "target_text": "Description of target/objective", 
            "status_description": "Current status description",
            "target_status": "not-started|in-progress|completed|delayed",
            "start_date": "2025-01-01",
            "end_date": "2025-12-31"
        }
    ],
    "remarks": "Additional notes",
    "brief_description": "Program summary",
    "program_name": "Program name",
    "program_number": "30.1A",
    "changes_made": [...]
}
```

## Target Numbering Rules
- Follow same format restrictions as program numbers
- Format: `{initiative}.{program}.{target}` (e.g., 30.1A.1, 30.1A.2)
- Initiative number extracted from program number
- Target counter increments within each program
- Optional field (can be empty)

## Implementation Tasks

### Phase 1: Backend Data Structure ✅
- [x] Analyze current content_json structure
- [x] Design new target structure
- [x] Plan backward compatibility

### Phase 2: Agency Edit Program Interface
- [x] Update form HTML to include new target fields
- [x] Add target numbering validation functions
- [x] Enhance JavaScript for dynamic target management
- [x] Update form processing logic
- [x] Add CSS styling for new fields
- [ ] Test target number validation

### Phase 3: Validation & Numbering
- [x] Create target number validation functions
- [x] Implement auto-numbering suggestions
- [x] Add duplicate number checking
- [x] Ensure hierarchical consistency

### Phase 4: Form Processing
- [x] Update save draft logic
- [x] Update finalization logic
- [x] Enhance change tracking
- [x] Update validation rules

### Phase 5: Testing & Polish
- [ ] Test with existing data
- [ ] Test new target creation
- [ ] Test target number validation
- [ ] Test backward compatibility
- [ ] Clean up any temporary files

## Files to Modify

### Primary Files
- `app/views/agency/programs/update_program.php` - Main edit interface
- `lib/numbering_helpers.php` - Target numbering functions
- `assets/js/agency/program_management.js` - Frontend interactions
- `assets/css/main.css` - Styling for new fields

### Supporting Files
- `lib/agencies/index.php` - Backend processing
- `lib/rating_helpers.php` - Status handling

## Implementation Details

### Target Number Format
- Extract initiative number from program number
- Format: `{initiative_number}.{program_suffix}.{target_counter}`
- Example: Program "30.1A" → Targets "30.1A.1", "30.1A.2", etc.

### Form Fields
1. **Target Number**: Text input with validation
2. **Target Status**: Dropdown (not-started, in-progress, completed, delayed)
3. **Start Date**: Date input (optional)
4. **End Date**: Date input (optional)
5. **Target Text**: Textarea (existing)
6. **Status Description**: Textarea (existing)

### Validation Rules
- Target number must follow format if provided
- Target number must be unique within program
- End date must be after start date if both provided
- Target text is required
- Target status defaults to "not-started"

## Backward Compatibility
- Existing targets without new fields will use defaults
- Legacy semicolon-separated format still supported
- Migration logic handles old data gracefully

## Notes
- Only implementing in agency edit program page as requested
- Other pages (view, details) will be updated in future phases
- Focus on maintaining existing functionality while adding new features
