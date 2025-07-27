<?php
/**
 * Table Display Partial
 * Displays tabular outcome data in read-only format
 */
?>

<div class="outcomes-table">
    <table class="table table-bordered table-hover mb-0">
        <thead>
            <tr>
                <th style="width: 150px;">Period</th>
                <?php foreach ($columns as $column): ?>
                    <th class="text-center">
                        <?php if (is_array($column)): ?>
                            <?= htmlspecialchars($column['label'] ?? $column['id']) ?>
                            <?php if (!empty($column['unit'])): ?>
                                <br><small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= htmlspecialchars($column) ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="cell-header">
                            <?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?>
                        </td>
                        <?php foreach ($columns as $column): ?>
                            <?php 
                                $columnId = is_array($column) ? ($column['id'] ?? $column['label']) : $column;
                                $columnLabel = is_array($column) ? ($column['label'] ?? $column['id']) : $column;
                                
                                // Handle different data structures
                                $value = '';
                                if (isset($row['data'])) {
                                    // New structure: row has 'data' property
                                    $value = $row['data'][$columnId] ?? $row['data'][$columnLabel] ?? '';
                                } else {
                                    // Database structure: row contains column values directly
                                    $value = $row[$columnId] ?? $row[$columnLabel] ?? '';
                                }
                                
                                // Format numeric values
                                if (is_numeric($value) && $value != 0) {
                                    $value = number_format($value, 2);
                                }
                            ?>
                            <td class="text-center cell-numeric">
                                <?php if (!empty($value) && $value !== '0.00'): ?>
                                    <?= htmlspecialchars($value) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= count($columns) + 1 ?>" class="text-center text-muted py-4">
                        <i class="fas fa-table me-2"></i>No data rows available
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Table Actions -->
<div class="table-actions mt-3">
    <div class="table-controls">
        <span class="table-status">
            <span class="table-status-indicator"></span>
            <?= count($rows) ?> rows, <?= count($columns) ?> columns
        </span>
        <a href="#" class="table-export-btn" onclick="outcomesModule.viewModule.exportData(); return false;">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>
</div>
