# Project Restructuring - Phase 2 Implementation Plan

This document outlines the step-by-step implementation plan for Phase 2 of the project restructuring, focusing on URL consistency.

## Helper Functions Implementation

- [x] Add `view_url()` function to config.php
- [x] Add `api_url()` function to config.php 
- [x] Add `ajax_url()` function to config.php
- [x] Add `asset_url()` function to config.php

## URL Updates Implementation Plan

### 1. View File Links

1. [ ] Create script to find relative links in view files
2. [ ] Update admin dashboard links using `view_url()`
3. [ ] Update agency dashboard links using `view_url()`
4. [ ] Update all other admin view files
5. [ ] Update all other agency view files
6. [ ] Verify and test navigation between views

### 2. AJAX and API URLs

1. [ ] Update admin JavaScript files to use `ajax_url()` and `api_url()`
2. [ ] Update agency JavaScript files to use `ajax_url()` and `api_url()`
3. [ ] Update AJAX references in PHP files
4. [ ] Test all AJAX functionality

### 3. Asset References

1. [ ] Update CSS references in layout files
2. [ ] Update JS references in layout files
3. [ ] Update image references across all files
4. [ ] Test asset loading

### 4. Form Actions

1. [ ] Identify all form actions in admin views
2. [ ] Identify all form actions in agency views
3. [ ] Update form actions to use absolute URLs
4. [ ] Test form submissions

### 5. Testing

1. [ ] Test navigation from all entry points
2. [ ] Test form submissions
3. [ ] Test AJAX calls
4. [ ] Test API endpoints
5. [ ] Test with different browser cache settings

## Implementation Approach

We'll use a combination of manual updates for critical files and automated scripts for bulk updates to ensure consistency across the codebase.

### Files to Update Manually:

- Layout files (headers, footers)
- Main dashboard files
- Critical form pages

### Files to Update with Scripts:

- Secondary views
- Non-critical forms
- Asset references in bulk

## Success Criteria

- All links work correctly with no 404 errors
- Form submissions work properly
- AJAX calls function as expected
- Assets load correctly
- No JavaScript console errors related to URLs
