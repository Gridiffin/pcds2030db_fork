# Drag and Drop File Acceptance Issue Fix

## Issue Description
- Dragover and dragleave events are firing correctly (visible in console)
- But files show red circle with slash when dragging over the drop zone
- Files cannot actually be dropped into the browser

## Root Cause Analysis
The red circle with slash typically indicates:
1. Missing `e.preventDefault()` on dragenter event
2. Incorrect `dropEffect` or `effectAllowed` settings
3. Missing dragenter event handler
4. CSS pointer-events preventing drop
5. File type restrictions preventing drop

## Investigation Plan

### Phase 1: Check Drag Event Handlers
- [ ] Verify all required drag events are handled (dragenter, dragover, dragleave, drop)
- [ ] Ensure e.preventDefault() is called on all events
- [ ] Check dropEffect and effectAllowed settings

### Phase 2: Check CSS Styling
- [ ] Verify no CSS is blocking pointer events
- [ ] Check if child elements are interfering with drag events
- [ ] Test with simplified HTML structure

### Phase 3: Browser-Specific Issues
- [ ] Test in different browsers
- [ ] Check for browser security restrictions
- [ ] Verify file type acceptance

### Phase 4: Implementation Fix
- [ ] Add missing dragenter event handler
- [ ] Fix event handling issues
- [ ] Test complete drag and drop workflow

## Files to Investigate
- `app/views/agency/programs/create_program.php` - Creation wizard drag/drop
- `app/views/agency/programs/update_program.php` - Update form drag/drop

## Success Criteria
- [ ] Files can be dragged over drop zone without red circle
- [ ] Files can be successfully dropped
- [ ] Drop zone visual feedback works correctly
- [ ] File upload process initiates on drop
