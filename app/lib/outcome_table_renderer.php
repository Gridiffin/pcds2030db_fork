<?php
/**
 * Outcome Table Renderer
 * Shared component for rendering outcome tables in both view and edit modes
 */
class OutcomeTableRenderer {
    
    private $mode; // 'view' or 'edit'
    private $outcomeData;
    private $outcomeId;
    
    public function __construct($mode = 'view') {
        $this->mode = $mode;
    }
    
    /**
     * Set the outcome data to render
     */
    public function setData($outcomeData, $outcomeId = null) {
        $this->outcomeData = $outcomeData;
        $this->outcomeId = $outcomeId;
    }
    
    /**
     * Render the complete outcome table
     */
    public function render() {
        if (!$this->outcomeData) {
            return $this->renderEmptyState();
        }
        
        $tableHtml = $this->renderTableHeader();
        $tableHtml .= $this->renderTableBody();
        $tableHtml .= $this->renderTableFooter();
        
        if ($this->mode === 'edit') {
            $tableHtml .= $this->renderEditControls();
        }
        
        return $tableHtml;
    }
    
    /**
     * Render table header with columns
     */
    private function renderTableHeader() {
        $data = json_decode($this->outcomeData, true);
        if (!$data || !isset($data['columns'])) {
            return '';
        }
        
        $html = '<div class="outcome-table-container">';
        $html .= '<table class="outcome-table table table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="row-header">Metrics</th>';
        
        foreach ($data['columns'] as $index => $column) {
            $html .= '<th class="column-header" data-column-index="' . $index . '">';
            if ($this->mode === 'edit') {
                $html .= '<input type="text" class="column-label form-control" value="' . htmlspecialchars($column) . '" data-original="' . htmlspecialchars($column) . '">';
                $html .= '<button type="button" class="btn btn-sm btn-danger remove-column" data-column-index="' . $index . '">×</button>';
            } else {
                $html .= htmlspecialchars($column);
            }
            $html .= '</th>';
        }
        
        if ($this->mode === 'edit') {
            $html .= '<th class="add-column-cell">';
            $html .= '<button type="button" class="btn btn-sm btn-success add-column">+</button>';
            $html .= '</th>';
        }
        
        $html .= '</tr>';
        $html .= '</thead>';
        
        return $html;
    }
    
    /**
     * Render table body with rows and data
     */
    private function renderTableBody() {
        $data = json_decode($this->outcomeData, true);
        if (!$data || !isset($data['rows']) || !isset($data['columns'])) {
            return '';
        }
        
        $html = '<tbody>';
        
        foreach ($data['rows'] as $rowIndex => $row) {
            $html .= '<tr data-row-index="' . $rowIndex . '">';
            
            // Row header
            $html .= '<td class="row-header" data-row-index="' . $rowIndex . '">';
            if ($this->mode === 'edit') {
                $html .= '<input type="text" class="row-label form-control" value="' . htmlspecialchars($row) . '" data-original="' . htmlspecialchars($row) . '">';
                $html .= '<button type="button" class="btn btn-sm btn-danger remove-row" data-row-index="' . $rowIndex . '">×</button>';
            } else {
                $html .= htmlspecialchars($row);
            }
            $html .= '</td>';
            
            // Data cells
            foreach ($data['columns'] as $colIndex => $column) {
                $value = isset($data['data'][$row][$column]) ? $data['data'][$row][$column] : '';
                $html .= '<td class="data-cell" data-row="' . htmlspecialchars($row) . '" data-column="' . htmlspecialchars($column) . '">';
                
                if ($this->mode === 'edit') {
                    $html .= '<input type="number" class="cell-input form-control" value="' . htmlspecialchars($value) . '" data-row="' . htmlspecialchars($row) . '" data-column="' . htmlspecialchars($column) . '">';
                } else {
                    $html .= htmlspecialchars($value);
                }
                
                $html .= '</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render table footer
     */
    private function renderTableFooter() {
        if ($this->mode !== 'edit') {
            return '';
        }
        
        $html = '<div class="table-footer mt-3">';
        $html .= '<button type="button" class="btn btn-success add-row">Add Row</button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render edit controls (save, cancel, etc.)
     */
    private function renderEditControls() {
        if ($this->mode !== 'edit') {
            return '';
        }
        
        $html = '<div class="edit-controls mt-4">';
        $html .= '<button type="button" class="btn btn-primary save-outcome" data-outcome-id="' . $this->outcomeId . '">Save Changes</button>';
        $html .= '<button type="button" class="btn btn-secondary cancel-edit ml-2">Cancel</button>';
        $html .= '<div class="save-status mt-2"></div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render empty state when no data is available
     */
    private function renderEmptyState() {
        if ($this->mode === 'edit') {
            return $this->renderEmptyEditTable();
        }
        
        return '<div class="empty-state">
                    <p>No outcome data available.</p>
                </div>';
    }
    
    /**
     * Render empty table for editing (when creating new outcome)
     */
    private function renderEmptyEditTable() {
        $html = '<div class="outcome-table-container">';
        $html .= '<table class="outcome-table table table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="row-header">Metrics</th>';
        $html .= '<th class="column-header" data-column-index="0">';
        $html .= '<input type="text" class="column-label form-control" value="Column 1" data-original="Column 1">';
        $html .= '<button type="button" class="btn btn-sm btn-danger remove-column" data-column-index="0">×</button>';
        $html .= '</th>';
        $html .= '<th class="add-column-cell">';
        $html .= '<button type="button" class="btn btn-sm btn-success add-column">+</button>';
        $html .= '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr data-row-index="0">';
        $html .= '<td class="row-header" data-row-index="0">';
        $html .= '<input type="text" class="row-label form-control" value="Row 1" data-original="Row 1">';
        $html .= '<button type="button" class="btn btn-sm btn-danger remove-row" data-row-index="0">×</button>';
        $html .= '</td>';
        $html .= '<td class="data-cell" data-row="Row 1" data-column="Column 1">';
        $html .= '<input type="number" class="cell-input form-control" value="" data-row="Row 1" data-column="Column 1">';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '<div class="table-footer mt-3">';
        $html .= '<button type="button" class="btn btn-success add-row">Add Row</button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get the data structure from the current table (for saving)
     */
    public static function getDataFromForm() {
        // This will be called via JavaScript to collect form data
        // The actual implementation is in the JavaScript utilities
        return null;
    }
}
?>
