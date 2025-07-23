<div class="container-fluid">
    <?php
    // Include initiative overview partial
    require_once __DIR__ . '/initiative_overview.php';

    // Include initiative metrics partial
    require_once __DIR__ . '/initiative_metrics.php';
    ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <?php
            // Include initiative information card
            require_once __DIR__ . '/initiative_info.php';
            
            // Include rating distribution chart
            require_once __DIR__ . '/rating_distribution.php';
            ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php
            // Include programs list
            require_once __DIR__ . '/programs_list.php';
            
            // Include activity feed
            require_once __DIR__ . '/activity_feed.php';
            ?>
        </div>
    </div>

    <?php
    // Include status grid section
    require_once __DIR__ . '/status_grid.php';
    ?>
</div>
