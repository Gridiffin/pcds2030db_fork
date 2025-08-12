<?php
/**
 * Dashboard Content Partial - Modernized
 * 
 * Modern bento-grid layout for admin dashboard
 * Maintains all functionality with enhanced UX
 */
?>

<!-- hasActivePeriod is injected by parent via $inlineScripts; avoid redeclaration here. -->

<!-- Modern Admin Dashboard -->
<main class="flex-fill">
    <section class="section">
        <div class="container-fluid">
            <!-- Modern Bento Grid Layout -->
            <div class="admin-dashboard-bento admin-fade-in">
                
                <!-- Statistics Overview - Modern Cards -->
                <div class="admin-bento-stats">
                    <div data-period-content="stats_section">
                        <?php require_once __DIR__ . '/_stats_overview_modern.php'; ?>
                    </div>
                </div>

                <!-- Quick Actions - Modern Layout -->
                <div class="admin-bento-quick-actions">
                    <?php require_once __DIR__ . '/_quick_actions_modern.php'; ?>
                </div>

                <!-- Programs Overview - Enhanced -->
                <div class="admin-bento-programs">
                    <?php require_once __DIR__ . '/_programs_overview_modern.php'; ?>
                </div>

                <!-- Outcomes Overview - Streamlined -->
                <div class="admin-bento-outcomes">
                    <?php require_once __DIR__ . '/_outcomes_overview_modern.php'; ?>
                </div>

                <!-- KPI Overview - Separate Card -->
                <div class="admin-bento-kpis">
                    <?php require_once __DIR__ . '/_kpi_overview_modern.php'; ?>
                </div>

            </div>
        </div>
    </section>
</main>
