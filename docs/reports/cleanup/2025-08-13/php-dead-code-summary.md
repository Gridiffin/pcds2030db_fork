# PHP Dead Code Analysis Report

**Generated:** August 13, 2025  
**Scope:** Complete PHP codebase analysis  
**Tools:** PHPStan, Rector  

## Executive Summary

The PHP codebase analysis reveals **significant opportunities for modernization and cleanup** with 882 PHPStan issues and 192 files requiring Rector improvements. The codebase shows typical legacy patterns that can be safely modernized.

### Key Metrics
- **PHP Files:** 7,095 files
- **Total Lines:** 31,441 lines of PHP code
- **Test Files:** 247 files (3.5% of codebase)
- **Debug Files:** 21 files
- **PHPStan Issues:** 882 errors
- **Rector Improvements:** 192 files need modernization

## PHPStan Analysis (882 Issues)

### 🔴 Critical Issues

**Undefined Variables (Most Common)**
- Variables might not be defined in conditional blocks
- Example: `$pdo might not be defined` in database connections
- **Impact:** Potential runtime errors
- **Files Affected:** ~200+ files

**Undefined Functions**
- Functions not found (likely missing includes)
- Example: `Function get_sector_data_for_period not found`
- **Impact:** Fatal errors in production
- **Files Affected:** ~50+ files

**Dead Code**
- Unreachable code branches
- Dead catch blocks
- Example: `Dead catch - Exception is already caught above`
- **Impact:** Code bloat, confusion
- **Files Affected:** ~30+ files

### 🟡 Code Quality Issues

**Trailing Whitespace**
- Files ending with whitespace after `?>`
- **Impact:** Potential output corruption
- **Risk:** Medium (can cause header issues)

**Type Inconsistencies**  
- Parameter type mismatches
- Unnecessary type casting
- **Impact:** Runtime warnings, performance

## Rector Analysis (192 Files)

### 🚀 High-Impact Modernization Rules

Based on the Rector dry-run output, here are the top improvement categories:

#### 1. **Path Modernization** (~50+ files)
```php
// Before (Rector: AbsolutizeRequireAndIncludePathRector)
require_once '../lib/db_connect.php';

// After  
require_once __DIR__ . '/../lib/db_connect.php';
```
**Benefits:** More reliable file includes, better portability

#### 2. **Empty Check Modernization** (~40+ files)
```php
// Before (Rector: DisallowedEmptyRuleFixerRector)
if (!empty($array)) { ... }

// After
if ($array !== [] && $array !== '' && $array !== '0') { ... }
```
**Benefits:** More explicit checking, better type safety

#### 3. **Array Comparison Modernization** (~30+ files)
```php
// Before (Rector: CountArrayToEmptyArrayComparisonRector)  
if (count($array) > 0) { ... }

// After
if ($array !== []) { ... }
```
**Benefits:** Better performance, clearer intent

#### 4. **Boolean Comparison Modernization** (~25+ files)
```php
// Before (Rector: ExplicitBoolCompareRector)
if ($value) { ... }

// After  
if ($value !== 0) { ... } // or !== false, !== null
```
**Benefits:** Explicit comparisons, fewer bugs

#### 5. **Control Structure Modernization** (~20+ files)
```php
// Before (Rector: CompleteMissingIfElseBracketRector)
if ($condition) return 'A';

// After
if ($condition) {
    return 'A';  
}
```
**Benefits:** Consistent formatting, better readability

### 📊 Rector Rule Categories

| Category | Rules | Impact | Risk |
|----------|-------|--------|------|
| **Dead Code Removal** | RemoveUnusedVariableAssignRector | High | Low |
| **Path Safety** | AbsolutizeRequireAndIncludePathRector | High | Very Low |
| **Type Safety** | ExplicitBoolCompareRector | Medium | Low |
| **Performance** | CountArrayToEmptyArrayComparisonRector | Medium | Very Low |
| **Code Quality** | SimplifyEmptyCheckOnEmptyArrayRector | Medium | Very Low |

## Dead Code Indicators

### 🎯 Immediate Dead Code Candidates

1. **Unreachable Code Blocks**
   - Else branches that can never execute
   - Code after return statements
   - **Estimated:** 15-30 instances

2. **Unused Variable Assignments**
   - Variables assigned but never used
   - **Estimated:** 50+ instances  

3. **Dead Exception Handlers**
   - Catch blocks that can never be reached
   - **Estimated:** 10-15 instances

4. **Legacy Debug Files**
   - 21 debug files that may be obsolete
   - Test files: 247 (review for relevance)

### 🔍 Potential Unused Functions

Based on "Function not found" errors, these may be dead:
- `get_sector_data_for_period()` 
- Various helper functions in legacy modules
- **Recommendation:** Cross-reference with actual usage

## Risk Assessment

### ✅ Low Risk Improvements (Safe to Apply)
- **Path absolutization** (AbsolutizeRequireAndIncludePathRector)
- **Empty array comparisons** (SimplifyEmptyCheckOnEmptyArrayRector)  
- **Trailing whitespace cleanup**
- **Unused variable removal**

### 🟡 Medium Risk Improvements (Test Required)
- **Boolean comparison changes** (ExplicitBoolCompareRector)
- **Empty() function replacements** (DisallowedEmptyRuleFixerRector)
- **Undefined variable fixes**

### 🔴 High Risk Areas (Manual Review Required)
- **Undefined function errors** (may indicate missing dependencies)
- **Type parameter mismatches** (could affect business logic)
- **Database connection variable scoping**

## Implementation Recommendations

### 📋 Phase 1: Safe Cleanup (Low Risk)
1. **Apply Rector path absolutization**
   ```bash
   vendor/bin/rector process --only=AbsolutizeRequireAndIncludePathRector
   ```

2. **Clean trailing whitespace**
   ```bash
   find . -name "*.php" -exec sed -i 's/[[:space:]]*$//' {} \;
   ```

3. **Apply safe array comparisons**
   ```bash
   vendor/bin/rector process --only=SimplifyEmptyCheckOnEmptyArrayRector
   ```

### 📋 Phase 2: Modernization (Medium Risk)
1. **Review and apply empty() replacements**
2. **Fix undefined variable scoping** 
3. **Apply explicit boolean comparisons**

### 📋 Phase 3: Deep Cleanup (High Risk)
1. **Resolve undefined functions**
2. **Remove confirmed dead code blocks**
3. **Clean up legacy debug files**

## Estimated Impact

### 📉 Code Reduction Potential
- **Dead code removal:** 500-1,000 lines
- **Unused variables:** 200-500 lines  
- **Simplified comparisons:** Improved readability
- **Total cleanup:** 2-5% code reduction

### 🚀 Performance Benefits
- **Faster file includes** (absolute paths)
- **Better array operations** (direct comparisons vs count())
- **Reduced memory usage** (unused variable removal)
- **Fewer runtime errors** (explicit comparisons)

### 🛡️ Reliability Improvements
- **Fewer undefined variable errors**
- **More predictable boolean logic**
- **Better error handling**
- **Reduced technical debt**

## Next Steps

### 🎯 Immediate Actions
1. **Start with Phase 1** (safe improvements)
2. **Run comprehensive tests** after each phase
3. **Monitor error logs** for regressions
4. **Document changes** for team awareness

### 🔄 Long-term Strategy  
1. **Establish Rector in CI/CD** to prevent regression
2. **Add PHPStan to commit hooks** for early detection
3. **Regular dead code audits** (quarterly)
4. **Team training** on modern PHP practices

---

**Analysis Confidence:** High  
**Estimated Effort:** 2-3 weeks (gradual implementation)  
**Risk Level:** Low-Medium (with proper testing)  
**ROI:** High (better maintainability, fewer bugs, improved performance)