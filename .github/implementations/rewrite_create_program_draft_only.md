# Rewrite Create Program - Draft Only System

## Problem
The current create program system is complex and has validation issues. We need a simple, draft-focused system that only requires program name with client-side validation.

## Proposed Solution
Create a completely new, simplified create program system that:
- Only saves as draft (no final submission button)
- Requires only program name (with client-side validation)
- Shows basic fields first (name, description, timeline)
- Redirects to view program page after creation
- Completely replaces the existing system

## Steps
1. [x] Create new implementation plan and get clarification on requirements
2. [x] Create new simplified `create_program.php` file
   - [x] Simple form with only basic fields (program name, description, start/end dates)
   - [x] Client-side validation for program name
   - [x] Only "Save Draft" button
3. [x] Create completely new backend function for draft creation
   - [x] Simple validation (only program name required)
   - [x] Return success with program ID for redirect
4. [x] Create simple JavaScript for client-side validation
5. [x] Implement redirect to programs list (view_programs.php)
6. [x] Remove/replace old create program files
7. [ ] Test the new system

## Clarifications (Confirmed)
- Redirect to programs list after creation
- Create completely new backend function
- Timeline: simple start date and end date fields

## Fields to Include (Basic)
- Program Name (required)
- Description (optional)
- Start Date (optional)
- End Date (optional)

## Notes
- No complex targets or rating system in initial creation
- Focus on simplicity and getting drafts working
- Submit functionality will be implemented later
