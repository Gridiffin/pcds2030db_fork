# Header Color System Update - Legacy Blue to Modern Green

## Problem Analysis
- ✅ **Current Issue**: Header system still uses legacy "blue" variant
- ✅ **Root Cause**: Header component was created with old color scheme
- ✅ **Solution**: Update to modern green/forest theme for consistency

## Implementation Plan

### Phase 1: Update Header Component CSS
- [x] **Task 1.1**: Change blue variant to green variant in page-header.css
- [x] **Task 1.2**: Update color variables to match forest theme
- [x] **Task 1.3**: Ensure proper contrast and accessibility
- [x] **Task 1.4**: Test button styling with green background

### Phase 2: Update Admin Pages
- [x] **Task 2.1**: Update dashboard to use green variant
- [ ] **Task 2.2**: Update other admin pages as needed
- [ ] **Task 2.3**: Verify consistency across all admin sections

### Phase 3: Prepare for Agency Side
- [ ] **Task 3.1**: Document green variant usage
- [ ] **Task 3.2**: Prepare agency-specific header variants if needed

## Color Specifications

### Green/Forest Theme
```
Primary Green: #2d5016 (from forest theme)
Secondary Green: #4a7c59 
Light Green: #6fa368
Green Gradient: From primary to secondary
```

### Implementation Details
- Replace all "blue" references with "green"
- Update CSS custom properties
- Maintain white text for contrast
- Keep accessibility compliance

---

**Next Steps**: Update page-header.css with green variant
