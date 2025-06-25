# Disable Outcome Value Automation

## Problem
The current system automatically updates outcome values when programs are completed, but we want to:
- ✅ **Keep tracking** completed programs in the `completed_programs` array
- ❌ **Disable automatic value updates** to `total_value` and other outcome metrics
- 🎯 **Prepare for future** more sophisticated impact calculation system

## Solution Steps

### Phase 1: Disable Value Updates
- [x] 1. Comment out or disable the `total_value` increment logic
- [x] 2. Keep the `completed_programs` array tracking intact
- [x] 3. Update audit logs to reflect that only tracking is happening
- [ ] 4. Test that programs are still tracked but values don't change

### Phase 2: Add Configuration Flag (Optional)
- [ ] 5. Add a configuration flag to easily enable/disable automation
- [ ] 6. Allow admin to toggle automation on/off via settings

### Phase 3: Documentation
- [ ] 7. Update comments in code to explain the disabled automation
- [ ] 8. Update system documentation

## Files to Modify
- `app/lib/outcome_automation.php` - Main automation logic
- Documentation files

## Current Behavior
```php
// This currently happens:
if ($is_cumulative && isset($data_json['total_value'])) {
    $data_json['total_value'] = ($data_json['total_value'] ?? 0) + 1;  // Auto-increment
}
```

## Desired Behavior
```php
// We want this instead:
// Track the program completion but don't auto-update values
// $data_json['total_value'] remains unchanged
// Only $data_json['completed_programs'] gets updated
```

## Testing Plan
1. Create a test program
2. Link it to an outcome
3. Mark program as completed
4. Verify: completed_programs array is updated
5. Verify: total_value and other metrics remain unchanged
