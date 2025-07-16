# Fix undefined $title variable and deprecation warning in view_outcome.php

## TODO List

- [x] Investigate why $title is undefined in app/views/admin/outcomes/view_outcome.php
- [x] Ensure $title is set before any output or includes
- [x] Restore fallback in output line to prevent warnings and deprecation notices
- [x] Test to confirm warning is resolved
- [x] Re-assign $title after including page_header.php to prevent it from being unset (fixes card header issue)

## Details

- The warning occurred because $title was not set before being used in the card header, causing a deprecation notice in PHP 8+ when passing null to htmlspecialchars().
- The fix restores the fallback in the output line:
  ```php
  <?= htmlspecialchars($title ?? 'Untitled Outcome') ?>
  ```
  This ensures that if $title is not set, 'Untitled Outcome' will be displayed and no warning will occur.
- The initialization logic for $title is also present at the top of the file, but the fallback in the output line provides an extra safety net.
- The `page_header.php` file unsets the global $title variable at the end, so $title must be re-assigned after including it if it is used again later in the file (e.g., in the card header).
- The fix re-assigns $title after the header include to ensure it is always defined for subsequent use.

**This task is now complete.**
