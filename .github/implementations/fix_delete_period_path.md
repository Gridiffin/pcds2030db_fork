## Fix Incorrect Path in delete_period.php

**Problem:** The script `app/ajax/delete_period.php` fails to load `config.php` due to an incorrect relative path. This results in a fatal error when trying to delete a reporting period.

**Error Log:**
```
Warning: require_once(../../config/config.php): Failed to open stream: No such file or directory in D:\laragon\www\pcds2030_dashboard\app\ajax\delete_period.php on line 15
Fatal error: Uncaught Error: Failed opening required '../../config/config.php' (include_path='.;D:/laragon/etc/php/pear') in D:\laragon\www\pcds2030_dashboard\app\ajax\delete_period.php:15
```

**Solution Steps:**

1.  [ ] **Identify the incorrect path:** The path `../../config/config.php` in `app/ajax/delete_period.php` is incorrect.
2.  [ ] **Determine the correct path:** The `config.php` file is located at `app/config/config.php`. The correct relative path from `app/ajax/delete_period.php` is `../config/config.php`.
3.  [ ] **Modify `delete_period.php`:** Update line 15 to use the correct path.
    -   Change `require_once '../../config/config.php';`
    -   To `require_once '../config/config.php';`
4.  [ ] **Test:** Verify that deleting a reporting period no longer produces the error.
