# Agency Cleanup Plan

## Objective
The goal of this cleanup is to remove refactor trash, unused code, and confusing components from the agency side of the project. This will improve maintainability, reduce technical debt, and enhance performance.

---

## THINK Phase

### Scope of Cleanup
1. **Refactor Trash**:
   - Remove leftover or redundant code from previous refactoring efforts.
2. **Confusing Code**:
   - Simplify code that is hard to understand or maintain.
3. **Unused Code**:
   - Detect and remove unused files, functions, or database entries.

### Impact Analysis
- Ensure no critical functionality is removed.
- Maintain compatibility with the multi-level permission system.
- Preserve the period-based data architecture and audit logging.

### Context Gathering
- Review the `docs/` folder for relevant guidelines, especially:
  - `dashboard_refactoring_summary.md`
  - `project_structure_best_practices.md`
  - `system_context.md`
- Identify dependencies and integration points in the `app/views/agency/` folder and related assets.

---

## Plan Steps

### Step 1: Identify Refactor Trash
- Use `grep_search` to locate comments or markers like `// TODO`, `// FIXME`, or `// Refactor`.
- Review the `dashboard_refactoring_summary.md` for any leftover tasks or deprecated components.

### Step 2: Detect Unused Code
- Use static analysis tools or manual inspection to:
  - Identify unused functions in `app/lib/` and `app/views/agency/`.
  - Check for unused JavaScript files in `assets/js/agency/`.
  - Verify unused CSS styles in `assets/css/`.

### Step 3: Simplify Confusing Code
- **Skip this step for now**: Complex or redundant logic will not be addressed in this cleanup process. Focus will remain on removing unused or redundant components only.

### Step 4: Remove Unused Files
- Check for unused files in:
  - `uploads/programs/`
  - `scripts/`
  - `tests/agency/`

### Step 5: Update Documentation
- Document removed or updated components in:
  - `docs/dashboard_refactoring_summary.md`
  - `docs/system_context.md`

### Step 6: Validate Changes
- Test the cleaned-up codebase for:
  - Role-based permissions (admin, focal, agency).
  - Period-based data queries.
  - Audit logging for any changes.

---

## REASON Phase

### Pros
- Reduces technical debt and improves maintainability.
- Simplifies onboarding for new developers.
- Enhances performance by removing unused assets.

### Cons
- Risk of accidentally removing critical functionality.
- Requires thorough testing to ensure no regressions.

### Justification
The cleanup aligns with best practices and ensures the agency side remains efficient and maintainable.

---

## SUGGEST Phase
I suggest proceeding with the outlined plan. Once approved, the cleanup will be performed step-by-step, with all changes documented and validated through testing.

---

## Implementation Strategy

### Approach
Starting with **grep the whole codebase** to create a comprehensive list of cleanup targets, then categorize and execute in manageable batches.

### Documentation
- Create `agency_cleanup_results.md` to track all search results and mark them as completed after removal.
- Include testing results for each cleaned component.

### Execution Order
1. Grep entire codebase for cleanup targets
2. Categorize results by type and module
3. Remove items in batches
4. Test each batch thoroughly
5. Mark items as completed in the results document
