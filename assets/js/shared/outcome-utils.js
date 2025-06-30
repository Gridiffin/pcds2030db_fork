/**
 * Outcome Utilities - Shared JavaScript for outcome table management
 */
const OutcomeUtils = {
    
    /**
     * Initialize outcome table functionality
     */
    init: function() {
        this.bindEvents();
    },
    
    /**
     * Bind all event handlers
     */
    bindEvents: function() {
        const self = this;
        
        // Add row button
        $(document).on('click', '.add-row', function() {
            self.addRow();
        });
        
        // Remove row button
        $(document).on('click', '.remove-row', function() {
            const rowIndex = $(this).data('row-index');
            self.removeRow(rowIndex);
        });
        
        // Add column button
        $(document).on('click', '.add-column', function() {
            self.addColumn();
        });
        
        // Remove column button
        $(document).on('click', '.remove-column', function() {
            const columnIndex = $(this).data('column-index');
            self.removeColumn(columnIndex);
        });
        
        // Save outcome button
        $(document).on('click', '.save-outcome', function() {
            const outcomeId = $(this).data('outcome-id');
            self.saveOutcome(outcomeId);
        });
        
        // Cancel edit button
        $(document).on('click', '.cancel-edit', function() {
            self.cancelEdit();
        });
        
        // Update data attributes when labels change
        $(document).on('input', '.row-label', function() {
            self.updateRowDataAttributes($(this));
        });
        
        $(document).on('input', '.column-label', function() {
            self.updateColumnDataAttributes($(this));
        });
    },
    
    /**
     * Add a new row to the table
     */
    addRow: function() {
        const tbody = $('.outcome-table tbody');
        const rowCount = tbody.find('tr').length;
        const newRowLabel = 'Row ' + (rowCount + 1);
        
        // Get current columns
        const columns = this.getCurrentColumns();
        
        let rowHtml = '<tr data-row-index="' + rowCount + '">';
        
        // Row header
        rowHtml += '<td class="row-header" data-row-index="' + rowCount + '">';
        rowHtml += '<input type="text" class="row-label form-control" value="' + newRowLabel + '" data-original="' + newRowLabel + '">';
        rowHtml += '<button type="button" class="btn btn-sm btn-danger remove-row" data-row-index="' + rowCount + '">×</button>';
        rowHtml += '</td>';
        
        // Data cells for each column
        columns.forEach(function(column) {
            rowHtml += '<td class="data-cell" data-row="' + newRowLabel + '" data-column="' + column + '">';
            rowHtml += '<input type="number" class="cell-input form-control" value="" data-row="' + newRowLabel + '" data-column="' + column + '">';
            rowHtml += '</td>';
        });
        
        rowHtml += '</tr>';
        
        tbody.append(rowHtml);
        this.updateRowIndices();
    },
    
    /**
     * Remove a row from the table
     */
    removeRow: function(rowIndex) {
        if ($('.outcome-table tbody tr').length <= 1) {
            alert('Cannot remove the last row.');
            return;
        }
        
        $('.outcome-table tbody tr[data-row-index="' + rowIndex + '"]').remove();
        this.updateRowIndices();
    },
    
    /**
     * Add a new column to the table
     */
    addColumn: function() {
        const headerRow = $('.outcome-table thead tr');
        const columnCount = headerRow.find('.column-header').length;
        const newColumnLabel = 'Column ' + (columnCount + 1);
        
        // Add header cell
        const headerHtml = '<th class="column-header" data-column-index="' + columnCount + '">' +
                          '<input type="text" class="column-label form-control" value="' + newColumnLabel + '" data-original="' + newColumnLabel + '">' +
                          '<button type="button" class="btn btn-sm btn-danger remove-column" data-column-index="' + columnCount + '">×</button>' +
                          '</th>';
        
        headerRow.find('.add-column-cell').before(headerHtml);
        
        // Add data cells to each row
        $('.outcome-table tbody tr').each(function() {
            const rowLabel = $(this).find('.row-label').val();
            const cellHtml = '<td class="data-cell" data-row="' + rowLabel + '" data-column="' + newColumnLabel + '">' +
                           '<input type="number" class="cell-input form-control" value="" data-row="' + rowLabel + '" data-column="' + newColumnLabel + '">' +
                           '</td>';
            $(this).append(cellHtml);
        });
        
        this.updateColumnIndices();
    },
    
    /**
     * Remove a column from the table
     */
    removeColumn: function(columnIndex) {
        if ($('.outcome-table thead .column-header').length <= 1) {
            alert('Cannot remove the last column.');
            return;
        }
        
        // Remove header
        $('.outcome-table thead .column-header[data-column-index="' + columnIndex + '"]').remove();
        
        // Remove data cells from all rows
        $('.outcome-table tbody tr').each(function() {
            $(this).find('td.data-cell').eq(columnIndex).remove();
        });
        
        this.updateColumnIndices();
    },
    
    /**
     * Update row data attributes when row label changes
     */
    updateRowDataAttributes: function($input) {
        const newLabel = $input.val();
        const originalLabel = $input.data('original');
        const $row = $input.closest('tr');
        
        // Update data attributes in the row's cells
        $row.find('.data-cell').each(function() {
            const $cell = $(this);
            const $cellInput = $cell.find('.cell-input');
            
            $cell.attr('data-row', newLabel);
            $cellInput.attr('data-row', newLabel);
        });
        
        // Update the original data attribute
        $input.data('original', newLabel);
    },
    
    /**
     * Update column data attributes when column label changes
     */
    updateColumnDataAttributes: function($input) {
        const newLabel = $input.val();
        const originalLabel = $input.data('original');
        const columnIndex = $input.closest('th').data('column-index');
        
        // Update data attributes in all cells in this column
        $('.outcome-table tbody tr').each(function() {
            const $cell = $(this).find('.data-cell').eq(columnIndex);
            const $cellInput = $cell.find('.cell-input');
            
            $cell.attr('data-column', newLabel);
            $cellInput.attr('data-column', newLabel);
        });
        
        // Update the original data attribute
        $input.data('original', newLabel);
    },
    
    /**
     * Update row indices after adding/removing rows
     */
    updateRowIndices: function() {
        $('.outcome-table tbody tr').each(function(index) {
            $(this).attr('data-row-index', index);
            $(this).find('.row-header').attr('data-row-index', index);
            $(this).find('.remove-row').attr('data-row-index', index);
        });
    },
    
    /**
     * Update column indices after adding/removing columns
     */
    updateColumnIndices: function() {
        $('.outcome-table thead .column-header').each(function(index) {
            $(this).attr('data-column-index', index);
            $(this).find('.remove-column').attr('data-column-index', index);
        });
    },
    
    /**
     * Get current column labels
     */
    getCurrentColumns: function() {
        const columns = [];
        $('.outcome-table thead .column-label').each(function() {
            columns.push($(this).val());
        });
        return columns;
    },
    
    /**
     * Get current row labels
     */
    getCurrentRows: function() {
        const rows = [];
        $('.outcome-table tbody .row-label').each(function() {
            rows.push($(this).val());
        });
        return rows;
    },
    
    /**
     * Collect all data from the table
     */
    collectTableData: function() {
        const rows = this.getCurrentRows();
        const columns = this.getCurrentColumns();
        const data = {};
        
        // Initialize data structure
        rows.forEach(function(row) {
            data[row] = {};
        });
        
        // Collect cell data
        $('.outcome-table .cell-input').each(function() {
            const $input = $(this);
            const row = $input.attr('data-row');
            const column = $input.attr('data-column');
            const value = $input.val() || '';
            
            if (data[row]) {
                data[row][column] = value;
            }
        });
        
        return {
            rows: rows,
            columns: columns,
            data: data
        };
    },
    
    /**
     * Save outcome data to the server
     */
    saveOutcome: function(outcomeId) {
        const self = this;
        const tableData = this.collectTableData();
        
        // Show saving status
        $('.save-status').html('<span class="text-info">Saving...</span>');
        
        $.ajax({
            url: '../../../ajax/submit_outcome.php',
            method: 'POST',
            data: {
                outcome_id: outcomeId,
                outcome_data: JSON.stringify(tableData)
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('.save-status').html('<span class="text-success">Saved successfully!</span>');
                        setTimeout(function() {
                            $('.save-status').html('');
                        }, 3000);
                    } else {
                        $('.save-status').html('<span class="text-danger">Error: ' + (result.message || 'Unknown error') + '</span>');
                    }
                } catch (e) {
                    $('.save-status').html('<span class="text-danger">Error: Invalid response from server</span>');
                }
            },
            error: function() {
                $('.save-status').html('<span class="text-danger">Error: Could not save data</span>');
            }
        });
    },
    
    /**
     * Cancel edit and return to view
     */
    cancelEdit: function() {
        if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
            window.history.back();
        }
    },
    
    /**
     * Validate table data before saving
     */
    validateTableData: function() {
        const rows = this.getCurrentRows();
        const columns = this.getCurrentColumns();
        
        // Check for empty row labels
        for (let i = 0; i < rows.length; i++) {
            if (!rows[i].trim()) {
                alert('Row ' + (i + 1) + ' must have a label.');
                return false;
            }
        }
        
        // Check for empty column labels
        for (let i = 0; i < columns.length; i++) {
            if (!columns[i].trim()) {
                alert('Column ' + (i + 1) + ' must have a label.');
                return false;
            }
        }
        
        // Check for duplicate row labels
        const uniqueRows = [...new Set(rows)];
        if (uniqueRows.length !== rows.length) {
            alert('Row labels must be unique.');
            return false;
        }
        
        // Check for duplicate column labels
        const uniqueColumns = [...new Set(columns)];
        if (uniqueColumns.length !== columns.length) {
            alert('Column labels must be unique.');
            return false;
        }
        
        return true;
    }
};

// Initialize when document is ready
$(document).ready(function() {
    OutcomeUtils.init();
});
