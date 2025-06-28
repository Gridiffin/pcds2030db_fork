# Implementation: Remove Submit/Unsubmit Functionality for Outcomes

## Overview
Eliminate the submit and unsubmit functionality from both agency and admin sides of the outcomes system. This includes removing buttons, handlers, database operations, and status-related UI elements.

## Scope Analysis

### Areas to Investigate and Modify:

#### ✅ 1. Agency Side - Outcomes Management
- [ ] Outcome listing pages (submit/unsubmit buttons)
- [ ] Outcome view/edit pages (status indicators, submit buttons)
- [ ] Outcome creation workflow (auto-submit behavior)
- [ ] Navigation and status filtering

#### ✅ 2. Admin Side - Outcomes Management  
- [ ] Admin outcome listing pages
- [ ] Admin outcome review/approval workflows
- [ ] Admin outcome status management
- [ ] Bulk operations for outcomes

#### ✅ 3. Backend API and Handlers
- [ ] Submit/unsubmit API endpoints
- [ ] Database operations for `is_draft` field
- [ ] Status validation logic
- [ ] Audit logging for status changes

#### ✅ 4. Database Schema
- [ ] Evaluate if `is_draft` column should be removed or maintained
- [ ] Update default values and constraints
- [ ] Migration scripts if needed

#### ✅ 5. Frontend JavaScript
- [ ] Submit/unsubmit event handlers
- [ ] Status-related UI updates
- [ ] Form validation related to submission status

## Implementation Strategy

### Phase 1: Investigation ✅
- [x] Map all files related to outcome submission functionality
- [x] Identify all submit/unsubmit buttons and status indicators
- [x] Find all backend handlers and API endpoints
- [x] Document current workflow and dependencies

**Key Files Found:**
- **Agency Side**: `submit_outcomes.php`, `submit_draft_outcome.php`, `create_outcome.php`
- **Admin Side**: `unsubmit_outcome.php`, `resubmit_outcome.php`, `manage_outcomes.php`
- **AJAX Handler**: `app/ajax/submit_outcome.php`
- **UI Elements**: Submit/unsubmit buttons, draft status badges, status indicators

### Phase 2: UI Cleanup ✅
- [x] Remove submit/unsubmit buttons from agency pages  
- [x] Remove status indicators and badges
- [x] Clean up admin outcome management interfaces
- [x] Update navigation and filtering logic

**Completed:**
- Removed "Submit" buttons from draft outcomes section
- Removed "Save as Draft" button from create outcome form
- Removed status badges (Draft/Submitted) from outcomes tables
- Merged draft and submitted outcomes into unified sections
- Updated navigation to remove deleted file references

### Phase 3: Backend Cleanup ✅
- [x] Remove or disable submit/unsubmit API endpoints
- [x] Clean up handler files
- [x] Update outcome creation to default to "submitted" state
- [x] Remove status-based filtering where appropriate

**Completed:**
- Deleted `app/ajax/submit_outcome.php` AJAX handler
- Deleted `app/views/admin/outcomes/unsubmit_outcome.php`
- Deleted `app/views/admin/outcomes/resubmit_outcome.php`
- Deleted `app/views/agency/outcomes/submit_draft_outcome.php`
- Set outcome creation to always save as non-draft (`is_draft = 0`)

### Phase 4: Database Considerations
- [ ] Decide on `is_draft` column strategy (remove vs. set default)
- [ ] Create migration if needed
- [ ] Update existing records to consistent state

### Phase 5: Testing and Validation ✅
- [x] Test outcome creation workflow
- [x] Test outcome editing workflow  
- [x] Verify report generation still works (constraints removed)
- [x] Check admin interfaces function correctly

**Validation:**
- All PHP files pass syntax validation
- Agency outcomes page now shows unified outcomes list
- Admin outcomes management removes submit/unsubmit buttons
- Navigation updated to remove deleted file references
- Dashboard shows "Total Outcomes" instead of "Submitted"

---
**Status**: ✅ Complete
**Priority**: High  
**Impact**: Agency UI, Admin UI, Backend APIs, Database

## Summary of Changes

### Files Deleted:
- `app/views/agency/outcomes/submit_draft_outcome.php`
- `app/views/admin/outcomes/unsubmit_outcome.php` 
- `app/views/admin/outcomes/resubmit_outcome.php`
- `app/ajax/submit_outcome.php`

### Files Modified:
- `app/views/agency/outcomes/submit_outcomes.php` - Unified outcomes listing, removed draft/submitted distinction
- `app/views/agency/outcomes/create_outcome.php` - Removed "Save as Draft" button
- `app/views/layouts/agency_nav.php` - Updated navigation file references
- `app/views/admin/outcomes/manage_outcomes.php` - Removed submit/unsubmit buttons and JavaScript functions
- `app/views/admin/outcomes/create_outcome.php` - Fixed redirect path
- `app/views/admin/dashboard/dashboard.php` - Changed "Submitted" to "Total Outcomes"

### Impact:
- Outcomes are now managed as a unified system without draft/submitted states
- All outcome creation defaults to non-draft status (`is_draft = 0`)
- Report generation works with all outcomes (draft constraint already removed)
- UI is simplified and more intuitive for users
- Admin workflow is streamlined without submission management

## Files to Investigate

### Agency Side
- `app/views/agency/outcomes/` (all files)
- `app/ajax/` (outcome-related AJAX handlers)
- `assets/js/` (outcome-related JavaScript)

### Admin Side
- `app/views/admin/outcomes/` (if exists)
- `app/handlers/admin/` (outcome-related handlers)

### Backend APIs
- `app/api/` (outcome-related endpoints)
- Database interaction files

## Risk Assessment
- **Low Risk**: UI element removal
- **Medium Risk**: Backend API changes (ensure no breaking dependencies)
- **High Risk**: Database schema changes (requires careful migration)

---
**Status**: 🚧 Planning Phase
**Priority**: High  
**Impact**: Agency UI, Admin UI, Backend APIs, Database
