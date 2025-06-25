# Migration Script for Program Number Format Changes

## Purpose
When changing program number format constants, this script helps migrate all existing data.

## Usage
```php
// Include the migration script
require_once 'migrate_program_numbers.php';

// Option 1: Migrate specific initiative
migrate_initiative_numbers($initiative_id);

// Option 2: Migrate all initiatives
migrate_all_program_numbers();
```

## Migration Functions Needed

### migrate_all_program_numbers()
```php
function migrate_all_program_numbers() {
    global $conn;
    
    // Get all initiatives
    $query = "SELECT initiative_id FROM initiatives WHERE initiative_number IS NOT NULL";
    $result = $conn->query($query);
    
    $success_count = 0;
    $error_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $migrate_result = renumber_initiative_programs($row['initiative_id']);
        
        if ($migrate_result['success']) {
            $success_count++;
        } else {
            $error_count++;
            echo "Error migrating initiative {$row['initiative_id']}: {$migrate_result['error']}\n";
        }
    }
    
    echo "Migration complete: {$success_count} initiatives migrated, {$error_count} errors\n";
}
```

## Steps for Format Change
1. [ ] Backup database
2. [ ] Change constants in numbering_helpers.php
3. [ ] Test new format with new programs
4. [ ] Run migration script for existing data
5. [ ] Verify all programs use new format
6. [ ] Update documentation
