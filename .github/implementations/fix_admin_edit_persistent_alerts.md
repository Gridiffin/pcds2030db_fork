# Fix Admin Edit Program - Persistent Alert Messages

## Problem
Alert messages (success/error) in the admin edit program view were automatically disappearing after 5 seconds due to JavaScript auto-dismiss functionality. Users needed the messages to remain visible until manually dismissed to ensure they have enough time to read them.

## Root Cause
The `assets/js/utilities/initialization.js` file contains an `initAlerts()` function that automatically hides all alerts after 5000ms (5 seconds) unless they have the `alert-permanent` class:

```javascript
// Auto-hide alerts after timeout
document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
    setTimeout(() => {
        if (alert.parentNode) {
            fade(alert, 500, () => {
                alert.parentNode.removeChild(alert);
            });
        }
    }, 5000);
});
```

## Solution
Added the `alert-permanent` class to prevent automatic dismissal and `alert-dismissible` with close buttons for manual dismissal:

**Session Messages:**
```php
<div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible alert-permanent show" role="alert">
    <?php echo htmlspecialchars($_SESSION['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
```

**Save Options Help:**
```php
<div class="alert alert-info alert-dismissible alert-permanent mb-3">
    <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Save Options Explained</h6>
    <small>
        <strong>Save as Draft:</strong> Save your progress without finalizing. You can continue editing later.<br>
        <!-- ... other explanations ... -->
    </small>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
```

## Changes Made
1. **app/views/admin/programs/edit_program.php**: 
   - Added `alert-permanent` class to success/error alert div to prevent auto-dismissal
   - Added `alert-permanent` and `alert-dismissible` classes to the "Save Options Explained" info alert
   - Added `aria-label` for accessibility on all close buttons
   - Added close buttons to allow manual dismissal when users are ready

## Files Modified
- **app/views/admin/programs/edit_program.php**: Updated both the session message alert and the informational "Save Options Explained" alert

## Testing
- Success messages now remain visible until manually dismissed
- Error messages now remain visible until manually dismissed  
- Close button functionality still works properly
- No visual fade-in/out issues
- Messages are cleared from session after display

## Benefits
- Users have unlimited time to read important messages
- Better user experience for slower readers
- Consistent with typical web application behavior
- Maintains manual dismissal option for users who want to clear messages
