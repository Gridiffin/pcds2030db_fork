<?php
/**
 * Dashboard Content Partial
 * 
 * Main content for the admin dashboard
 */
?>

<script>
    const hasActivePeriod = <?php echo $hasActivePeriod ? 'true' : 'false'; ?>;
</script>

<!-- Dashboard Content -->
<main class="flex-fill">
    <section class="section">
        <div class="container-fluid">
            <!-- Period Selector Component -->

            <!-- Quick Actions Section -->
            <?php require_once __DIR__ . '/_quick_actions.php'; ?>

            <!-- Stats Overview -->
            <div data-period-content="stats_section">
                <?php require_once __DIR__ . '/_stats_overview.php'; ?>
            </div>

            <!-- Programs Overview Section -->
            <?php require_once __DIR__ . '/_programs_overview.php'; ?>

            <!-- Outcomes Overview Section -->
            <?php require_once __DIR__ . '/_outcomes_overview.php'; ?>

        </div>
    </section>
</main>
