# Example: Changing Program Number Format

## Current Format: `30.1, 30.2, 30.3`
## New Format: `30-1, 30-2, 30-3`

### Step 1: Change Constants
```php
const PROGRAM_NUMBER_SEPARATOR = '-';  // Changed from '.'
const PROGRAM_NUMBER_REGEX_STRICT = '/^\d+\-\d+$/';  // Updated pattern
```

### Step 2: What Happens Immediately
- ✅ New programs will use format `30-1, 30-2, 30-3`
- ✅ Validation accepts both old and new formats temporarily
- ❌ Existing programs still show `30.1, 30.2, 30.3` in database

### Step 3: Update Existing Data (Manual)
```php
// Update all programs for initiative ID 5
$result = renumber_initiative_programs(5);
// This would change: 30.1, 30.2, 30.3 → 30-1, 30-2, 30-3
```

### Step 4: Final Result
- ✅ All new programs: `30-1, 30-2, 30-3`
- ✅ All existing programs: `30-1, 30-2, 30-3`
- ✅ All validation uses new format
