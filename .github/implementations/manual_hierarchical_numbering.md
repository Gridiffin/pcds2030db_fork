# Manual Hierarchical Program Numbering

## Problem
The current auto-generation system is too automated. Users want manual control over program numbers where:
- Initiative number is fixed (e.g., "30")
- User manually enters the sequence number (e.g., "1" to make "30.1")
- No auto-generation, just validation and formatting

## Current Issues
- ✅ Auto-generation works but is unwanted
- ❌ Shows "Initiative has no number assigned" message incorrectly
- ❌ Users can't manually control the sequence number
- ❌ System tries to be too "smart"

## Solution Plan

### Phase 1: Update Frontend Behavior
- [x] Remove auto-generation logic from create_program.php
- [x] Show initiative number clearly (e.g., "Initiative 30 - Enter sequence number")
- [x] Allow manual input of sequence number only
- [x] Format as "initiative.sequence" automatically
- [x] Add real-time validation for conflicts

### Phase 2: Update Backend Logic
- [x] Modify AJAX endpoint to validate instead of generate
- [x] Remove auto-generation from program creation
- [x] Keep validation functions for duplicates
- [x] Update error messages

### Phase 3: Update Helper Functions
- [x] Keep validation functions
- [x] Remove or modify auto-generation functions
- [x] Update bulk assignment to use manual approach

### Phase 4: Testing & Cleanup
- [x] Test manual number entry
- [x] Test validation  
- [x] Remove auto-generation references
- [x] Update field positions and messaging
- [x] Show actual initiative numbers instead of confusing messages
- [x] Swap initiative selector above program number fields
- [x] Improve initiative number extraction logic
- [x] Add better error handling for missing initiative numbers
- [ ] Update documentation

## Implementation Details

### Frontend Changes
1. When initiative selected:
   - Show "Initiative 30 - Enter sequence number (e.g., 1 for 30.1)"
   - Enable input field for sequence number only
   - Format as "30.1" automatically

2. Validation:
   - Check if "30.1" already exists
   - Show error if duplicate
   - Allow user to try different sequence

### Backend Changes
1. Remove auto-generation from:
   - create_wizard_program_draft()
   - AJAX endpoints
   - Bulk assignment

2. Keep validation:
   - Format checking
   - Duplicate checking
   - Initiative number matching
