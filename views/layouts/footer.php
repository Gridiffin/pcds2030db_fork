</div> <!-- Close container-fluid from admin_nav.php -->
    
<!-- Footer with reduced height -->
<footer class="footer py-2 bg-white border-top">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>
            </div>
            <div class="text-muted small">
                Version <span class="badge bg-light text-dark"><?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?></span>
            </div>
        </div>
    </div>
</footer>
    
<!-- JavaScript dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

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

<!-- Toast container for notifications -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Core JS files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dropdown initialization - Fixed for dropdowns not working -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        // Additional event to ensure proper working
        document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
                bootstrap.Dropdown.getOrCreateInstance(this).toggle();
            });
        });
    });
</script>

<!-- Shared utilities -->
<script src="<?php echo APP_URL; ?>/assets/js/utilities/initialization.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/utilities/form_utils.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/utilities/filter_utils.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/utilities/status_utils.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>

<!-- Additional scripts -->
<?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
    <?php foreach($additionalScripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Inline page-specific scripts -->
<?php if (isset($inlineScripts)): ?>
    <script>
        <?php echo $inlineScripts; ?>
    </script>
<?php endif; ?>
</body>
</html>
