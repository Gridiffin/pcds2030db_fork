# Single Page Program Creation Wizard (Draft-Only)

## Overview
Redesign the create program flow into a single-page wizard/stepper interface that guides users through program creation while only saving drafts. This eliminates the poor UX of creating basic info → redirecting → finding program → editing.

## User Flow
```
Single Page: create_program.php
├── Step 1: Basic Information (Program Name, Description, Timeline)
├── Step 2: Program Details (Objectives, Beneficiaries, Budget)
├── Step 3: Targets & Metrics (Optional advanced section)
├── Step 4: Review & Save
└── Only Action: [Save Draft] (no submit button to prevent accidental submissions)
```

## Key Features
- ✅ All steps on one page with progress indicator
- ✅ Users can navigate back/forward between steps
- ✅ Auto-save progress on each step
- ✅ Only "Save Draft" action (no submit to prevent accidental submissions)
- ✅ Clear progress indication
- ✅ Validation per step
- ✅ Responsive design

## Technical Implementation

### 1. Frontend Structure
- [ ] Create wizard/stepper component with Bootstrap
- [ ] Implement step navigation (Previous/Next buttons)
- [ ] Add progress indicator
- [ ] Create form sections for each step
- [ ] Implement client-side validation per step
- [ ] Add auto-save functionality

### 2. Backend Structure
- [ ] Modify existing `create_simple_program_draft` function to handle all fields
- [ ] Add AJAX endpoints for auto-save
- [ ] Implement step-by-step data validation
- [ ] Ensure all data is saved as draft

### 3. Database Considerations
- [ ] Review current program table structure
- [ ] Ensure all wizard fields can be stored
- [ ] Test draft saving with partial data

### 4. UI/UX Components
- [ ] Step progress indicator
- [ ] Form sections with proper spacing
- [ ] Navigation buttons (Previous/Next)
- [ ] Auto-save status indicator
- [ ] Validation feedback per step

## Files to Modify/Create

### Primary Files
- [ ] `app/views/agency/create_program.php` - Main wizard interface
- [ ] `app/lib/agencies/programs.php` - Enhanced draft saving functions
- [ ] `assets/js/agency/program_wizard.js` - Wizard functionality
- [ ] `assets/css/components/program_wizard.css` - Wizard styling

### Supporting Files
- [ ] Update `assets/css/main.css` to import new CSS
- [ ] Create AJAX endpoints for auto-save
- [ ] Update form validation utilities

## Step-by-Step Implementation

### Phase 1: Basic Wizard Structure
- [ ] Create stepper/progress indicator HTML
- [ ] Implement step navigation JavaScript
- [ ] Style wizard components
- [ ] Test navigation between steps

### Phase 2: Form Sections
- [ ] Organize existing form fields into logical steps
- [ ] Add new fields for comprehensive program creation
- [ ] Implement per-step validation
- [ ] Test form data persistence

### Phase 3: Auto-Save & Draft Management
- [ ] Implement auto-save on step completion
- [ ] Add AJAX draft saving endpoints
- [ ] Create draft status indicators
- [ ] Test partial data saving

### Phase 4: Polish & Testing
- [ ] Responsive design testing
- [ ] Cross-browser compatibility
- [ ] User experience testing
- [ ] Performance optimization

## Benefits of This Approach
1. **Better UX**: Complete task in one place
2. **Reduced Friction**: No page redirects or context switching
3. **Progress Saving**: Auto-save prevents data loss
4. **Client Safety**: No accidental submissions (draft-only)
5. **Professional Feel**: Modern wizard interface
6. **Guided Experience**: Step-by-step completion

## Current Status
- [ ] Planning phase - ready to implement
