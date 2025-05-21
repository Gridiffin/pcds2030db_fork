# PCDS2030 Dashboard - Code Cleanup Plan

Before beginning the major restructuring effort outlined in our project restructuring plan, we should clean up the codebase to remove unused files, dead code, and redundant functionality. This will make the restructuring process cleaner and reduce technical debt.

## Cleanup Approach

To ensure we don't accidentally remove something important, we'll use a multi-step process:

1. **Identify Potentially Unused Files**
2. **Mark Files for Review**
3. **Verify Dependencies**
4. **Create Backup**
5. **Remove Confirmed Unused Elements**

## 1. Identification Methods

We'll use several complementary techniques to identify unused code:

### A. Static Analysis
- Trace file includes/requires across the project
- Identify unreferenced PHP files
- Find CSS/JS files not linked in any HTML/PHP files

### B. Dynamic Analysis
- Use access logs to identify unaccessed endpoints
- Add temporary logging to track file execution
- Monitor database query patterns to identify unused tables/columns

### C. Manual Code Review
- Review files with unusual timestamps or naming patterns
- Examine test/debugging files that may have been left in production
- Look for commented-out code blocks that can be removed

## 2. Classification of Files for Review

We'll create a tracking spreadsheet with the following categories:

| File Path | Referenced By | Last Modified | Status | Notes |
|-----------|--------------|--------------|--------|-------|
| path/to/file.php | file1.php, file2.php | YYYY-MM-DD | Keep/Remove/Review | Reasoning |

### Status Categories
- **Keep**: File is actively used
- **Remove**: File is confirmed unused
- **Review**: Requires further investigation
- **Archive**: Might be needed for reference but not in production

## 3. High Priority Areas for Cleanup

Based on initial inspection, these areas likely contain unused code:

### Debugging/Testing Files
- Files in `/debug` directory if any
- Files with "test", "example", or "temp" in their names
- Files ending with `.bak`, `.old`, or similar extensions

### Development Artifacts
- Backup files
- Version control artifacts
- IDE configuration files

### Deprecated Features
- Old reporting mechanisms that have been replaced
- Previous iterations of dashboard views
- Legacy API endpoints

## 4. Safe Removal Process

For each file identified for removal:

1. Create a backup
2. Move to a temporary "to_remove" directory instead of deleting
3. Test application functionality
4. If no issues after 1 week of testing, permanently delete

## 5. Code within Files

For unused functions or code blocks within otherwise active files:

1. Identify using code coverage tools or manual review
2. Comment out with clear markers and date
3. After testing period with no issues, remove completely

## 6. Cleanup Tasks Checklist

### Phase 1: Initial Scan

- [ ] Generate list of all PHP files in the project
- [ ] Trace include/require relationships
- [ ] Identify standalone files with no references
- [ ] Scan for duplicate or near-duplicate files

### Phase 2: Database Cleanup

- [ ] Identify unused database tables/columns
- [ ] Document deprecated schema elements
- [ ] Create SQL script for safe removal of unused elements

### Phase 3: Frontend Cleanup

- [ ] Identify unused CSS rules
- [ ] Find unused JavaScript functions
- [ ] Remove redundant assets (images, fonts, etc.)

### Phase 4: Documentation

- [ ] Update documentation to reflect removed components
- [ ] Document reasons for removal
- [ ] Update data flow diagrams if applicable

## Tools to Consider

1. **Composer Unused Package Detector**
   - For identifying unused Composer packages

2. **PHP Dead Code Detector**
   - For finding unused functions/methods

3. **Coverage Analysis Tools**
   - To identify unexecuted code paths

4. **Grep/Find Commands**
   - For manual tracing of file references

## Example Identification Commands

```bash
# Find PHP files not referenced by any other file
grep -l "require\|include" --include="*.php" -r . | xargs -I{} grep -L "{}" --include="*.php" -r .

# Find CSS files not referenced in any HTML/PHP files
find . -name "*.css" | xargs -I{} bash -c 'grep -l "{}" --include="*.php" --include="*.html" -r . > /dev/null || echo "{}"'

# Find JavaScript files not referenced
find . -name "*.js" | xargs -I{} bash -c 'grep -l "{}" --include="*.php" --include="*.html" -r . > /dev/null || echo "{}"'
```

## Next Steps

1. Execute initial scan and create removal candidates list
2. Review with team members who have historical knowledge
3. Implement logging to verify non-usage
4. Create backups before removal
5. Begin systematic cleanup

Remember: When in doubt, keep the file but mark it for future review rather than removing it immediately. It's better to be cautious than to break functionality.
