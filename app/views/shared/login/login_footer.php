<footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($jsBundle)): ?>
        <script src="<?php echo $jsBundle; ?>"></script>
    <?php endif; ?>
    <div class="copyright-text">© <?php echo date('Y'); ?> <?php echo APP_NAME; ?></div>
</footer> 