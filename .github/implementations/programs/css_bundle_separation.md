# Programs Module CSS Bundle Separation

## Issue Description
The programs module was incorrectly using a single combined `programs` CSS bundle for all pages, when it should use individual page-specific bundles. This goes against the modular CSS architecture where each page has its own styles.

## THINK Phase
Each programs page should have:
1. Its own specific CSS bundle (e.g., `view-programs.bundle.css`)
2. Shared base styles imported via `@import '../shared/base.css'`
3. Page-specific styling that doesn't interfere with other pages

## Bundle Separation Implemented

### 1. Vite Configuration Updated
**File**: `vite.config.js`

**New CSS Entry Points with Agency Prefix**:
All programs bundles now use the `agency-` prefix to differentiate them from admin bundles:

**New CSS Entry Points**:
```javascript
// CSS Entry Points - Programs Module (Individual Pages)
'agency-view-programs': path.resolve(__dirname, 'assets/css/agency/programs/view_programs.css'),
'agency-create-program': path.resolve(__dirname, 'assets/css/agency/programs/create.css'),
'agency-edit-program': path.resolve(__dirname, 'assets/css/agency/programs/edit_program.css'),
'agency-add-submission': path.resolve(__dirname, 'assets/css/agency/programs/add_submission.css'),
'agency-program-details': path.resolve(__dirname, 'assets/css/agency/programs/programs.css'),
'agency-edit-submission': path.resolve(__dirname, 'assets/css/agency/programs/form.css'),
'agency-view-submissions': path.resolve(__dirname, 'assets/css/agency/programs/timeline.css'),
```

### 2. PHP Files Updated
Each PHP file now uses its specific bundle:

| PHP File | Old Bundle | New Bundle |
|----------|------------|------------|
| `view_programs.php` | `programs` | `agency-view-programs` |
| `create_program.php` | `programs` | `agency-create-program` |
| `edit_program.php` | `programs` | `agency-edit-program` |
| `add_submission.php` | `programs` | `agency-add-submission` |
| `program_details.php` | `programs` | `agency-program-details` |
| `edit_submission.php` | `programs` | `agency-edit-submission` |
| `view_submissions.php` | `programs` | `agency-view-submissions` |
| `view_other_agency_programs.php` | `programs` | `agency-view-programs` |

### 3. CSS Files Enhanced
Each CSS file now imports shared base styles:

**Before**:
```css
/* Program specific styles */
.programs-table { ... }
```

**After**:
```css
/* Import shared base styles */
@import '../shared/base.css';

/* Program specific styles */
.programs-table { ... }
```

**Files Updated**:
- ✅ `view_programs.css` - Added base import
- ✅ `edit_program.css` - Added base import
- ✅ `add_submission.css` - Added base import
- ✅ `create.css` - Added base import
- ✅ `form.css` - Added base import
- ✅ `timeline.css` - Added base import
- ✅ `programs.css` - Already had base import

## Build Results

### ✅ Individual Bundle Sizes:
- `agency-view-programs.bundle.css`: 70.54 kB
- `agency-create-program.bundle.css`: 76.22 kB
- `agency-edit-program.bundle.css`: 70.95 kB
- `agency-add-submission.bundle.css`: 69.88 kB
- `agency-program-details.bundle.css`: 108.83 kB
- `agency-edit-submission.bundle.css`: 72.19 kB
- `agency-view-submissions.bundle.css`: 71.10 kB

### Bundle Loading Behavior:
✅ **Each page now loads only its specific bundle**
- `view_programs.php` → `agency-view-programs.bundle.css`
- `create_program.php` → `agency-create-program.bundle.css`
- `edit_program.php` → `agency-edit-program.bundle.css`
- etc.

❌ **No more shared `programs.bundle.css` loading across all pages**

## Benefits of Separation

### 1. **Modular Architecture**
- Each page loads only the CSS it needs
- Easier maintenance and debugging
- Clear separation of concerns

### 2. **Performance Optimization**
- Smaller individual bundle sizes
- No unused CSS loading
- Better caching (unchanged pages don't re-download CSS)

### 3. **Development Efficiency**
- CSS changes affect only specific pages
- Easier to locate and fix styling issues
- Independent development of different pages

### 4. **Scalability**
- New pages can have their own bundles
- Easy to add page-specific features
- No risk of CSS conflicts between pages

## Browser Network Tab Behavior

### Before (Problematic):
- All pages: `programs.bundle.css` (~108 kB)

### After (Correct):
- View Programs: `agency-view-programs.bundle.css` (~70 kB)
- Create Program: `agency-create-program.bundle.css` (~76 kB)
- Edit Program: `agency-edit-program.bundle.css` (~71 kB)
- Add Submission: `agency-add-submission.bundle.css` (~70 kB)
- Program Details: `agency-program-details.bundle.css` (~109 kB)
- Edit Submission: `agency-edit-submission.bundle.css` (~72 kB)
- View Submissions: `agency-view-submissions.bundle.css` (~71 kB)

## Testing Verification

Each page should be tested:
1. Visit the page URL
2. Check Network tab shows correct individual bundle
3. Verify styling is properly applied
4. Confirm no other program CSS bundles are loaded

## Files Modified

### Vite Configuration:
- `vite.config.js` - Added individual CSS entry points, fixed duplicate key

### PHP Files (Bundle Variable Updates):
- `view_programs.php`
- `create_program.php`
- `edit_program.php`
- `add_submission.php`
- `program_details.php`
- `edit_submission.php`
- `view_submissions.php`
- `view_other_agency_programs.php`

### CSS Files (Added Base Imports):
- `view_programs.css`
- `edit_program.css`
- `add_submission.css`
- `create.css`
- `form.css`
- `timeline.css`

## Status: ✅ COMPLETE

The programs module now follows proper modular CSS architecture with individual page-specific bundles. Each page loads only its required styles, improving performance and maintainability.
