            </div> <!-- Close container-fluid -->
        </main>
          <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="text-muted small">
                        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>
                    </div>
                    <div class="text-muted small">
                        Version <span class="badge bg-light text-dark"><?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?></span>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- Close content-wrapper -->
    
<!-- JavaScript dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

<!-- Chart.js - Ensure it's always loaded before dashboard scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Utility scripts -->
<script src="<?php echo asset_url('js/utilities', 'rating_utils.js'); ?>"></script>
<script src="<?php echo asset_url('js/utilities', 'dropdown_init.js'); ?>"></script>
    
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
    
<!-- Additional page-specific scripts -->
<?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
    <?php foreach($additionalScripts as $script): ?>
        <?php if (strpos($script, 'http') === 0 || strpos($script, '//') === 0): ?>
            <!-- External script -->
            <script src="<?php echo $script; ?>"></script>
        <?php elseif (strpos($script, 'asset_url') !== false || strpos($script, 'APP_URL') !== false): ?>
            <!-- Script already using helper functions -->
            <script src="<?php echo $script; ?>"></script>
        <?php else: ?>
            <!-- Convert relative path to asset_url -->
            <?php
                // Extract path parts
                $pathParts = explode('/', $script);
                $filename = array_pop($pathParts);
                $directory = implode('/', $pathParts);
                // Remove 'assets/' prefix if present
                $directory = str_replace('assets/', '', $directory);
            ?>
            <script src="<?php echo asset_url($directory, $filename); ?>"></script>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Toast container for notifications -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Shared utilities -->
<script src="<?php echo APP_URL; ?>/assets/js/utilities/initialization.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/utilities/form_utils.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/utilities/filter_utils.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>

<!-- Inline page-specific scripts -->
<?php if (isset($inlineScripts)): ?>
    <script>
        <?php echo $inlineScripts; ?>
    </script>
<?php endif; ?>
</body>
</html>
