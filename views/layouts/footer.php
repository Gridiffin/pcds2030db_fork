</div> <!-- Close container-fluid from admin_nav.php -->
    
<!-- Footer with reduced height -->
<footer class="footer py-2 bg-white border-top">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center">
                    <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="PCDS Logo" height="24" class="me-2 d-none d-md-block">
                </div>
            </div>
            <div class="col-md-4 text-center">
                <span>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></span>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex justify-content-md-end align-items-center">
                    <span class="text-muted small me-2">Version 1.0.0</span>
                    <span class="badge rounded-pill bg-secondary">Beta</span>
                </div>
            </div>
        </div>
    </div>
</footer>
    
<!-- JavaScript dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Utility scripts -->
<script src="<?php echo APP_URL; ?>/assets/js/utilities/status_utils.js"></script>
    
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
