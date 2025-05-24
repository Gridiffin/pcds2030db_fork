# Fix User Activation/Deactivation Indefinite Table Refresh Loop

The goal is to fix the issue where activating or deactivating a user on the `manage_users.php` page causes the tables to refresh indefinitely.

## Problem Analysis

The current `refreshTable` JavaScript function in `user_table_manager.js` fetches the entire `manage_users.php` page content when an AJAX refresh is triggered (e.g., after toggling user status). It then tries to parse this full HTML and replace only a part of the DOM. This can lead to several issues:
1.  **Inefficiency**: Fetching and parsing the entire page is wasteful.
2.  **Incorrect Updates**: If not handled carefully, only parts of the table section might be updated, or updates might fail.
3.  **Potential for Loops**: If the DOM parsing or replacement fails, or if JavaScript errors occur, it might trigger `window.location.reload()` or other recursive calls, potentially leading to a loop, especially if the error condition persists.

The `manage_users.php` script does not currently have a dedicated AJAX handler to return only the table content.

## Proposed Solution

1.  **Modify `manage_users.php`**:
    *   [ ] Wrap both the "Admin Users" table card and the "Agency Users" table card in a single `div` with a unique ID, for example, `id="userTablesWrapper"`.
    *   [ ] Implement a check at the beginning of `manage_users.php`. If `isset($_GET['ajax_table']) && $_GET['ajax_table'] == '1'`, the script should:
        *   Fetch the necessary user data (`$admin_users`, `$agency_users`).
        *   Output *only* the HTML content for the two user table cards (i.e., the content that would go inside `#userTablesWrapper`).
        *   Call `exit;` to prevent the rest of the page (header, nav, footer, scripts) from being sent in the AJAX response.
2.  **Modify `assets/js/admin/user_table_manager.js`**:
    *   [ ] Update the `refreshTable` function:
        *   Ensure the `fetch` URL correctly points to `manage_users.php?ajax_table=1`.
        *   Target the new wrapper ID (e.g., `#userTablesWrapper`) for `innerHTML` replacement with the AJAX response.
        *   The loading spinner logic should be applied within this wrapper.
        *   Ensure `attachEventListeners()` is called after the content is updated to re-bind events to the new table elements. The `listenersAttached` flag within `user_table_manager.js` should be reset to `false` before calling `attachEventListeners` to ensure it runs.

## Detailed Steps

### `manage_users.php` Modifications

1.  **Add a wrapper div**:
    Locate the sections for "Admin Users Table Card" and "Agency Users Table Card". Wrap them with `<div id="userTablesWrapper">` and `</div>`.
2.  **Implement AJAX handler**:
    At the top of the file (after initial includes, session start, and admin verification), add a block:
    ```php
    if (isset($_GET['ajax_table']) && $_GET['ajax_table'] == '1') {
        // Copied user fetching logic
        $all_users = get_all_users();
        $admin_users = array_filter($all_users, function($user) { return $user['role'] === 'admin'; });
        $agency_users = array_filter($all_users, function($user) { return $user['role'] === 'agency'; });
    
        // Copied HTML for Admin Users table card
        // ...
        // Copied HTML for Agency Users table card
        // ...
        exit; 
    }
    ```

### `assets/js/admin/user_table_manager.js` Modifications

1.  **Update `refreshTable` function**:
    ```javascript
    function refreshTable() {
        const tableWrapper = document.getElementById('userTablesWrapper');
        if (!tableWrapper) {
            console.error('User tables wrapper (#userTablesWrapper) not found. Reloading page.');
            window.location.reload();
            return;
        }

        const originalContent = tableWrapper.innerHTML;
        tableWrapper.innerHTML = \`
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="ms-2 mb-0 text-muted">Refreshing user list...</p>
            </div>
        \`;

        fetch(window.APP_URL + '/app/views/admin/manage_users.php?ajax_table=1')
            .then(response => {
                if (!response.ok) {
                    throw new Error(\`HTTP error! status: \${response.status}, statusText: \${response.statusText}\`);
                }
                return response.text();
            })
            .then(html => {
                if (html.trim() === '') {
                    throw new Error('Empty response from server');
                }
                tableWrapper.innerHTML = html;
                
                listenersAttached = false; // Reset flag
                attachEventListeners(); 
                
                // highlightTableRows(); // Optional
            })
            .catch(error => {
                console.error('Table refresh error:', error);
                tableWrapper.innerHTML = originalContent; 
                
                listenersAttached = false; // Reset flag for restored content
                attachEventListeners();

                if (toastManager) {
                    toastManager.show('Error', \`Failed to refresh user list: \${error.message}. Please try again.\`, 'danger');
                } else {
                    alert(\`Failed to refresh user list: \${error.message}. Please try again.\`);
                }
            });
    }
    ```

## Testing
- After changes, test user activation/deactivation for both admin and agency users.
- Verify that the tables refresh correctly without a full page reload.
- Verify that there is no indefinite refresh loop.
- Verify that action buttons (edit, delete, toggle) on the refreshed tables still work.
- Check browser console for any JavaScript errors.
