<?php
/**
 * Notification Header Partial
 * Header section with stats, filters, search, and bulk actions
 */
?>

<div class="notifications-header">
    <!-- Statistics Row -->
    <div class="notifications-stats">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value total-notifications"><?php echo $stats['total'] ?? 0; ?></div>
                        <div class="stat-label">Total Notifications</div>
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
                        <div class="stat-value read-notifications"><?php echo ($stats['total'] ?? 0) - ($stats['unread'] ?? 0); ?></div>
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
    <div class="notifications-action-bar">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <!-- Bulk Selection -->
                    <div class="bulk-selection">
                        <div class="form-check">
                            <input class="form-check-input select-all-notifications" type="checkbox" id="selectAllNotifications">
                            <label class="form-check-label" for="selectAllNotifications">
                                Select All
                            </label>
                        </div>
                    </div>

                    <!-- Bulk Actions (hidden by default) -->
                    <div class="bulk-actions" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">
                                <span class="selected-count">0</span> selected
                            </span>
                            <button class="btn btn-sm btn-outline-success mark-selected-read-btn">
                                <i class="fas fa-check me-1"></i>
                                Mark as Read
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-selected-btn" disabled>
                                <i class="fas fa-trash me-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 justify-content-md-end">
                    <!-- Action Buttons -->
                    <button class="btn btn-success mark-all-read-btn notifications-action-btn">
                        <i class="fas fa-check-double me-2"></i>
                        Mark All as Read
                    </button>
                    <button class="btn btn-outline-primary refresh-notifications-btn notifications-action-btn">
                        <i class="fas fa-sync-alt me-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="notifications-filters-search">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <!-- Quick Filters -->
                <div class="quick-filters">
                    <div class="btn-group" role="group" aria-label="Notification filters">
                        <button type="button" class="btn btn-outline-primary notifications-filter-btn active" data-filter="all">
                            <i class="fas fa-list me-1"></i>
                            All
                        </button>
                        <button type="button" class="btn btn-outline-primary notifications-filter-btn" data-filter="unread">
                            <i class="fas fa-envelope me-1"></i>
                            Unread
                        </button>
                        <button type="button" class="btn btn-outline-primary notifications-filter-btn" data-filter="read">
                            <i class="fas fa-envelope-open me-1"></i>
                            Read
                        </button>
                        <button type="button" class="btn btn-outline-primary notifications-filter-btn" data-filter="today">
                            <i class="fas fa-calendar-day me-1"></i>
                            Today
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Search -->
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" class="form-control notifications-search-input" placeholder="Search notifications..." aria-label="Search notifications">
                        <button class="btn btn-outline-secondary notifications-search-btn" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary clear-search-btn" type="button" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
