# Improve Structure Manager UI - Implementation

## Problems Identified

### 1. Duplicate Notifications
- [x] **Issue**: When deleting rows/columns, duplicate notifications appear
- [x] **Cause**: Both success feedback and operation confirmation messages are shown
- [ ] **Solution**: Remove redundant notifications, keep only one clear message

### 2. Missing Edit Functionality
- [ ] **Issue**: Users must delete entire columns/rows to change details
- [ ] **Impact**: Data loss when editing structure elements
- [ ] **Solution**: Add inline edit functionality for existing columns/rows

### 3. Table Structure Type Consistency
- [ ] **Issue**: Adding columns to yearly tables may break structure type logic
- [ ] **Concern**: System cannot validate if user-added rows follow year patterns
- [ ] **Solution**: Smart structure detection and validation

## Implementation Plan

### Phase 1: Fix Duplicate Notifications
- [ ] Analyze current notification functions
- [ ] Remove redundant feedback messages
- [ ] Ensure single, clear notification per action

### Phase 2: Add Edit Functionality
- [ ] Add edit buttons to existing column/row items
- [ ] Implement inline editing for labels and types
- [ ] Add validation for edited values
- [ ] Preserve data during edits

### Phase 3: Smart Structure Management
- [ ] Detect table structure types (yearly, monthly, custom)
- [ ] Add structure-aware column/row suggestions
- [ ] Implement validation for structure consistency
- [ ] Handle structure type transitions gracefully

## Files to Modify
- `app/views/agency/outcomes/view_outcome.php` (main functionality)
- `assets/css/table-structure-designer.css` (styling for edit controls)

## Status: 🔄 IN PROGRESS
