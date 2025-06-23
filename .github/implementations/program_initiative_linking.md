# Program-Initiative Linking Implementation

## Overview
Implement functionality to allow agencies to link their programs to initiatives during program creation and editing. This includes updating program forms, enhancing program listings to show initiative information, and ensuring proper validation.

## Implementation Plan

### Phase 1: Agency Program Form Updates
- [x] Update program creation form to include initiative selection
- [x] Update program editing form to include initiative selection
- [x] Add initiative dropdown with active initiatives only
- [x] Implement proper validation and error handling

### Phase 2: Program Listing Enhancements
- [x] Update agency program listings to show initiative information
- [ ] Add initiative filtering in program lists
- [x] Show initiative badge/indicator on program cards

### Phase 3: Admin Program Management Updates
- [ ] Update admin program views to show initiative assignments
- [ ] Add bulk initiative assignment functionality
- [ ] Enhance program search/filtering by initiative

### Phase 4: Backend API Enhancements
- [x] Update program submission APIs to handle initiative assignments
- [x] Add validation to ensure only active initiatives can be assigned
- [x] Update program retrieval APIs to include initiative information

## Files to be Created/Modified

### Agency Views (Primary Focus)
- [x] `app/views/agency/programs/create_program.php` - Add initiative selection
- [x] `app/views/agency/programs/update_program.php` - Add initiative selection
- [x] `app/views/agency/programs/view_programs.php` - Show initiative info
- [ ] `app/views/agency/ajax/submit_program.php` - Handle initiative assignment

### Admin Views (Secondary)
- [ ] `app/views/admin/programs/programs.php` - Show initiative assignments
- [ ] `app/views/admin/programs/assign_programs.php` - Add initiative assignment

### Backend APIs
- [x] Update existing program submission APIs
- [x] Enhance program validation functions
- [x] Add initiative selection helper functions

### JavaScript/Frontend
- [ ] Add initiative selection UI components
- [ ] Implement dynamic loading of initiatives
- [ ] Add form validation for initiative selection

## Design Considerations

### User Experience
- Make initiative selection optional (programs can exist without initiatives)
- Show only active initiatives in dropdowns
- Provide clear indication when a program is linked to an initiative
- Allow easy changing of initiative assignments

### Data Integrity
- Validate that selected initiatives are active
- Ensure agencies can only see their relevant initiatives (if needed)
- Maintain referential integrity in database
- Handle cases where initiatives become inactive

### UI/UX Patterns
- Follow existing form patterns in the application
- Use consistent styling and layout
- Implement proper loading states and feedback
- Ensure mobile responsiveness

## Success Criteria
- [x] Agencies can select initiatives when creating programs
- [x] Agencies can change initiative assignments when editing programs
- [x] Program listings clearly show initiative associations
- [x] Only active initiatives appear in selection dropdowns
- [x] Form validation prevents invalid initiative selections
- [x] All existing program functionality remains intact
- [x] Initiative information appears in program submissions
- [ ] Admin views show initiative assignments for all programs

## Implementation Notes

### Database Schema
✅ Already implemented:
- `programs.initiative_id` foreign key exists
- `initiatives` table with `is_active` flag exists
- Proper indexes and constraints in place

### API Compatibility
- Maintain backward compatibility with existing program APIs
- Ensure initiative_id is properly handled in all program operations
- Update program retrieval to include initiative information when needed

### Security Considerations
- Validate initiative selections server-side
- Ensure agencies can only assign to initiatives they have permission for
- Prevent assignment to inactive initiatives
- Sanitize all initiative-related inputs
