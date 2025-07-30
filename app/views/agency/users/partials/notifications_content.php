<!-- Notifications Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="notifications-container">
                <!-- Header with Stats -->
                <div class="notifications-header mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card total">
                                <div class="stat-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value total-notifications"><?php echo $stats['total'] ?? 0; ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card unread">
                                <div class="stat-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value unread-notifications"><?php echo $stats['unread'] ?? 0; ?></div>
                                    <div class="stat-label">Unread</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card read">
                                <div class="stat-icon">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value read-notifications"><?php echo $read_count; ?></div>
                                    <div class="stat-label">Read</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card recent">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value recent-notifications"><?php echo $stats['recent'] ?? 0; ?></div>
                                    <div class="stat-label">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="notifications-action-bar mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <button class="btn btn-success mark-all-read-btn">
                                    <i class="fas fa-check-double me-2"></i>
                                    Mark All as Read
                                </button>
                                <button class="btn btn-outline-primary refresh-notifications-btn">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="notifications-filters mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="quick-filters">
                                <div class="btn-group" role="group" aria-label="Notification filters">
                                    <button type="button" class="btn btn-<?php echo $current_filter === 'all' ? 'primary' : 'outline-primary'; ?> notifications-filter-btn" data-filter="all">
                                        <i class="fas fa-list me-1"></i>
                                        All
                                    </button>
                                    <button type="button" class="btn btn-<?php echo $current_filter === 'unread' ? 'primary' : 'outline-primary'; ?> notifications-filter-btn" data-filter="unread">
                                        <i class="fas fa-envelope me-1"></i>
                                        Unread
                                    </button>
                                    <button type="button" class="btn btn-<?php echo $current_filter === 'read' ? 'primary' : 'outline-primary'; ?> notifications-filter-btn" data-filter="read">
                                        <i class="fas fa-envelope-open me-1"></i>
                                        Read
                                    </button>
                                    <button type="button" class="btn btn-<?php echo $current_filter === 'today' ? 'primary' : 'outline-primary'; ?> notifications-filter-btn" data-filter="today">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        Today
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Empty state (controlled by JavaScript) -->
                <div class="notifications-empty text-center py-5" style="display: none;">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications found</h5>
                    <p class="text-muted">You're all caught up!</p>
                </div>

                <!-- Loading and error states for JavaScript -->
                <div class="notifications-loading text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading notifications...</p>
                </div>

                <div class="notifications-error alert alert-danger" style="display: none;" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span class="error-message">Failed to load notifications.</span>
                </div>

                <div class="notifications-success alert alert-success" style="display: none;" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span class="success-message">Action completed successfully.</span>
                </div>

                <!-- Pagination -->
                <?php if ($notifications_result['total_pages'] > 1): ?>
                    <div class="notifications-pagination mt-4">
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&per_page=<?php echo $per_page; ?>&filter=<?php echo $current_filter; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($notifications_result['total_pages'], $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&filter=<?php echo $current_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $notifications_result['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&per_page=<?php echo $per_page; ?>&filter=<?php echo $current_filter; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>