# User Profile Feature Implementation Plan

## Overview

Create a comprehensive user profile page that allows users to edit their username, password, and email. The page will be accessible from the navbar "Profile" link and follow the project's modular structure.

## Database Schema

From `currentpcds2030db.sql`, the users table has:

- `user_id` (int, primary key)
- `username` (varchar(100), not null)
- `pw` (varchar(255), not null) - password hash
- `fullname` (varchar(200), nullable)
- `email` (varchar(255), not null)
- `agency_id` (int, not null, foreign key)
- `role` (enum: 'admin','agency','focal')
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `is_active` (tinyint)

## Files to Create/Modify

### 1. Main Profile Page

- [x] **File**: `app/views/agency/users/profile.php`
  - Main profile page using base.php layout pattern
  - Configure page title, CSS/JS bundles
  - Set up header configuration
  - Include profile content partial

### 2. Profile Content Partial

- [x] **File**: `app/views/agency/users/partials/profile_content.php`
  - Form for editing username, password, email
  - Include current user information display
  - Form validation (client-side and server-side)
  - Success/error message handling

### 3. Profile Handler/Controller

- [x] **File**: `app/handlers/profile_handler.php`
  - Handle form submission
  - Validate input data
  - Update user information in database
  - Password hashing
  - Email validation
  - Username uniqueness check

### 4. Profile CSS

- [x] **File**: `assets/css/agency/users/profile.css`
  - Consistent styling with other pages
  - Form styling
  - Responsive design
  - Success/error message styles

### 5. Profile JavaScript

- [x] **File**: `assets/js/agency/users/profile.js`
  - Client-side form validation
  - Password strength indicator
  - Confirm password matching
  - Form submission handling

### 6. User Helper Functions

- [x] **File**: `app/lib/user_functions.php`
  - `get_user_by_id($conn, $user_id)`
  - `update_user_profile($conn, $user_id, $data)`
  - `validate_username_unique($conn, $username, $current_user_id)`
  - `validate_email_format($email)`

### 7. Update Navbar Links

- [x] **File**: `app/views/layouts/navbar-modern.php` (line 253)
  - Update Profile link to point to actual profile page
- [x] **File**: `app/views/layouts/admin-navbar-modern.php` (line 356)
  - Update admin Profile link as well

## Security Considerations

- [x] Password hashing using `password_hash()` with bcrypt
- [x] CSRF protection
- [x] Input sanitization and validation
- [x] SQL injection prevention (prepared statements)
- [x] Session validation

## Form Validation Rules

- [x] **Username**:
  - Required, 3-50 characters
  - Alphanumeric and underscore only
  - Must be unique
- [x] **Email**:
  - Required, valid email format
  - Must be unique (optional enhancement)
- [x] **Password**:
  - Optional (only if user wants to change)
  - Minimum 8 characters if provided
  - Must contain at least one letter and one number
- [x] **Confirm Password**:
  - Required if password is provided
  - Must match password

## User Experience Features

- [x] Show current values in form fields
- [x] Password strength indicator
- [x] Real-time validation feedback
- [x] Success/error messages
- [x] Form auto-save indication
- [x] Responsive design for mobile devices

## CSS Bundle Integration

- [x] Add profile.css to Vite configuration
- [x] Create agency-users CSS bundle for profile page
- [x] Import necessary component styles

## Testing Checklist

- [x] Test form submission with valid data
- [x] Test form validation with invalid data
- [x] Test password update functionality
- [x] Test username uniqueness validation
- [x] Test email format validation
- [x] Test responsive design on different screen sizes
- [x] Test with different user roles (agency, focal, admin)

## Implementation Order

1. ✅ Create user helper functions
2. ✅ Create profile handler
3. ✅ Create profile CSS file
4. ✅ Create profile JavaScript file
5. ✅ Create profile content partial
6. ✅ Create main profile page
7. ✅ Update navbar links
8. ✅ Test all functionality

## Progress Tracking

- [x] **Phase 1**: Core infrastructure (helper functions, handler)
- [x] **Phase 2**: Frontend implementation (CSS, JS, HTML)
- [x] **Phase 3**: Integration and testing
- [x] **Phase 4**: Final refinements and documentation

## Notes

- Follow project's modular CSS/JS structure using Vite bundles
- Maintain consistency with existing form styling patterns
- Ensure proper error handling and user feedback
- Use project's existing design tokens and color scheme
- Follow accessibility best practices
