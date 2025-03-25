</div> <!-- Close page-wrapper -->
    
    <!-- Footer - moved outside page-wrapper for proper alignment -->
    <footer class="footer py-3">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo APP_URL; ?>/assets/images/logo-small.png" alt="PCDS Logo" height="30" class="me-2 d-none d-md-block">
                        <span>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-center py-2 py-md-0">
                    <div class="quick-links">
                        <a href="<?php echo APP_URL; ?>/about.php" class="text-muted mx-2">About</a>
                        <span class="text-muted">|</span>
                        <a href="<?php echo APP_URL; ?>/help.php" class="text-muted mx-2">Help</a>
                        <span class="text-muted">|</span>
                        <a href="<?php echo APP_URL; ?>/contact.php" class="text-muted mx-2">Contact</a>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex justify-content-md-end align-items-center">
                        <span class="text-muted me-2">Version 1.0.0</span>
                        <span class="badge rounded-pill bg-secondary">Beta</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core App JavaScript -->
    <script>
        // Handle preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('preloader-hide');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 300);
            }
        });
    </script>
    
    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
