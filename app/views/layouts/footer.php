<!-- Modern Footer - Sticky at bottom -->
<footer class="app-footer mt-auto bg-dark text-light py-3" role="contentinfo" aria-label="Site footer">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="footer-copyright">
                <span>&copy; <?php echo date('Y'); ?> <?php echo defined('APP_NAME') ? APP_NAME : 'PCDS 2030 Dashboard'; ?></span>
            </div>
            <div class="footer-version">
                <span class="version-label me-2">Version</span>
                <span class="badge bg-success"><?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?></span>
            </div>
        </div>
    </div>
</footer>
