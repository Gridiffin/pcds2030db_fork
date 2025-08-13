<?php
/**
 * Manage Users Content Partial
 * 
 * Main content for the manage users page
 */
?>

<!-- Make APP_URL and other data available to JavaScript -->
<script>
    // Define APP_URL for JavaScript to fix the "APP_URL is not defined" error
    window.APP_URL = '<?php echo APP_URL; ?>';
    
    // Store any success/error messages for toast notifications - always use toast for AJAX responses
    window.pageMessages = {
        message: '<?php echo addslashes($message); ?>',
        type: '<?php echo $message_type; ?>',
        // Always use toast for ajax responses or when explicitly requested
        useToast: <?php echo (!empty($message) && (isset($show_toast_only) && $show_toast_only)) ? 'true' : 'false'; ?>
    };
</script>

<?php if (!empty($message) && empty($show_toast_only)): ?>
    <div class="alert alert-forest alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> alert-icon"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>    </div>
<?php endif; ?>

<!-- User Management Content -->
<main class="flex-fill">
    <div id="userTablesWrapper">
        <?php
        // Render Admin Users Table
        $users = $admin_users;
        $tableTitle = 'Admin Users';
        $roleType = 'admin';
        include __DIR__ . '/../_user_table.php';
        
        // Render Agency Users Table
        $users = $agency_users;
        $tableTitle = 'Agency Users';
        $roleType = 'agency';
        include __DIR__ . '/../_user_table.php';
        ?>
    </div> <!-- End of userTablesWrapper -->
</main>
