<?php
/**
 * Notification Pagination Partial
 * Comprehensive pagination with controls and information
 */

$currentPage = $pagination['current_page'] ?? 1;
$lastPage = $pagination['total_pages'] ?? 1;
$total = $pagination['total_count'] ?? 0;
$perPage = $pagination['per_page'] ?? 10;
$from = (($currentPage - 1) * $perPage) + 1;
$to = min($currentPage * $perPage, $total);
?>

<div class="notifications-pagination">
    <!-- Pagination Info -->
    <div class="notifications-pagination-info">
        <div class="notifications-pagination-summary">
            Showing <?php echo $from; ?> to <?php echo $to; ?> of <?php echo $total; ?> notifications
        </div>

        <div class="notifications-per-page">
            <label for="notificationsPerPage">Show:</label>
            <select id="notificationsPerPage" class="form-select form-select-sm">
                <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>

    <!-- Pagination Navigation -->
    <?php if ($lastPage > 1): ?>
        <nav class="notifications-pagination-nav" aria-label="Notifications pagination">
            <ul class="pagination">
                <!-- Previous Button -->
                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                    <button class="page-link pagination-btn" data-page="<?php echo $currentPage - 1; ?>" aria-label="Previous" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </li>

                <?php
                // Calculate page range to show
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
                
                // Adjust range if needed to show 5 pages when possible
                if ($endPage - $startPage < 4) {
                    if ($startPage == 1) {
                        $endPage = min($lastPage, $startPage + 4);
                    } else {
                        $startPage = max(1, $endPage - 4);
                    }
                }

                // First page + ellipsis if needed
                if ($startPage > 1): ?>
                    <li class="page-item">
                        <button class="page-link pagination-btn" data-page="1">1</button>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <button class="page-link pagination-btn" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
                    </li>
                <?php endfor; ?>

                <!-- Last page + ellipsis if needed -->
                <?php if ($endPage < $lastPage): ?>
                    <?php if ($endPage < $lastPage - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <button class="page-link pagination-btn" data-page="<?php echo $lastPage; ?>"><?php echo $lastPage; ?></button>
                    </li>
                <?php endif; ?>

                <!-- Next Button -->
                <li class="page-item <?php echo $currentPage >= $lastPage ? 'disabled' : ''; ?>">
                    <button class="page-link pagination-btn" data-page="<?php echo $currentPage + 1; ?>" aria-label="Next" <?php echo $currentPage >= $lastPage ? 'disabled' : ''; ?>>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Jump to Page (for large datasets) -->
        <?php if ($lastPage > 10): ?>
            <div class="notifications-pagination-jump">
                <label for="jumpToPage">Go to page:</label>
                <select id="jumpToPage" class="form-select form-select-sm">
                    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $currentPage ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
