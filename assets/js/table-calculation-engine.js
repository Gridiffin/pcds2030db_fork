/**
 * Table Calculation Engine
 * 
 * JavaScript library for handling calculations in flexible data tables
 * Supports formulas, auto-calculations, and data transformations.
 */

class TableCalculationEngine {
    constructor() {
        this.calculationRows = new Map();
        this.formulas = new Map();
        this.dependencies = new Map();
    }
    
    /**
     * Add a calculation row to the table
     * @param {string} rowId - Unique identifier for the calculation row
     * @param {Object} calculation - Calculation configuration
     */
    addCalculationRow(rowId, calculation) {
        this.calculationRows.set(rowId, calculation);
        this.updateDependencies(rowId, calculation);
    }
    
    /**
     * Remove a calculation row
     * @param {string} rowId - Row to remove
     */
    removeCalculationRow(rowId) {
        this.calculationRows.delete(rowId);
        this.dependencies.delete(rowId);
    }
    
    /**
     * Update dependencies for a calculation
     * @param {string} rowId - Row ID
     * @param {Object} calculation - Calculation configuration
     */
    updateDependencies(rowId, calculation) {
        const deps = [];
        
        if (calculation.type === 'sum' || calculation.type === 'average') {
            deps.push(...calculation.sourceRows);
        } else if (calculation.type === 'formula') {
            // Extract dependencies from formula
            const formula = calculation.formula;
            const matches = formula.match(/\[([^\]]+)\]/g);
            if (matches) {
                matches.forEach(match => {
                    const cellRef = match.slice(1, -1); // Remove brackets
                    deps.push(cellRef);
                });
            }
        }
        
        this.dependencies.set(rowId, deps);
    }
    
    /**
     * Calculate values for all calculation rows
     * @param {Object} tableData - Current table data
     * @param {Array} rows - Row configuration
     * @param {Array} columns - Column configuration
     * @returns {Object} Updated table data with calculated values
     */
    calculateAll(tableData, rows, columns) {
        const updatedData = { ...tableData };
        
        // Process calculations in dependency order
        const processedRows = new Set();
        const rowsToProcess = Array.from(this.calculationRows.keys());
        
        while (rowsToProcess.length > 0) {
            const currentCount = rowsToProcess.length;
            
            for (let i = rowsToProcess.length - 1; i >= 0; i--) {
                const rowId = rowsToProcess[i];
                const calculation = this.calculationRows.get(rowId);
                const dependencies = this.dependencies.get(rowId) || [];
                
                // Check if all dependencies are satisfied
                const canProcess = dependencies.every(dep => {
                    // Check if dependency is a calculation row that's already processed
                    if (this.calculationRows.has(dep)) {
                        return processedRows.has(dep);
                    }
                    // Non-calculation rows are always available
                    return true;
                });
                
                if (canProcess) {
                    this.processCalculation(rowId, calculation, updatedData, rows, columns);
                    processedRows.add(rowId);
                    rowsToProcess.splice(i, 1);
                }
            }
            
            // Prevent infinite loop if there are circular dependencies
            if (rowsToProcess.length === currentCount) {
                console.error('Circular dependency detected in calculations');
                break;
            }
        }
        
        return updatedData;
    }
    
    /**
     * Process a single calculation
     * @param {string} rowId - Row ID
     * @param {Object} calculation - Calculation configuration
     * @param {Object} tableData - Table data to update
     * @param {Array} rows - Row configuration
     * @param {Array} columns - Column configuration
     */
    processCalculation(rowId, calculation, tableData, rows, columns) {
        if (!tableData.data) {
            tableData.data = {};
        }
        
        const rowLabel = this.getRowLabel(rowId, rows);
        if (!tableData.data[rowLabel]) {
            tableData.data[rowLabel] = {};
        }
        
        columns.forEach(column => {
            let value = 0;
            
            switch (calculation.type) {
                case 'sum':
                    value = this.calculateSum(calculation.sourceRows, column.name, tableData, rows);
                    break;
                case 'average':
                    value = this.calculateAverage(calculation.sourceRows, column.name, tableData, rows);
                    break;
                case 'formula':
                    value = this.evaluateFormula(calculation.formula, column.name, tableData, rows, columns);
                    break;
                case 'percentage':
                    value = this.calculatePercentage(calculation.numeratorRow, calculation.denominatorRow, column.name, tableData, rows);
                    break;
            }
            
            tableData.data[rowLabel][column.name] = value;
        });
    }
    
    /**
     * Calculate sum of specified rows
     */
    calculateSum(sourceRows, columnName, tableData, rows) {
        let sum = 0;
        sourceRows.forEach(rowId => {
            const rowLabel = this.getRowLabel(rowId, rows);
            if (tableData.data[rowLabel] && tableData.data[rowLabel][columnName]) {
                sum += parseFloat(tableData.data[rowLabel][columnName]) || 0;
            }
        });
        return sum;
    }
    
    /**
     * Calculate average of specified rows
     */
    calculateAverage(sourceRows, columnName, tableData, rows) {
        const sum = this.calculateSum(sourceRows, columnName, tableData, rows);
        return sourceRows.length > 0 ? sum / sourceRows.length : 0;
    }
    
    /**
     * Calculate percentage
     */
    calculatePercentage(numeratorRow, denominatorRow, columnName, tableData, rows) {
        const numeratorLabel = this.getRowLabel(numeratorRow, rows);
        const denominatorLabel = this.getRowLabel(denominatorRow, rows);
        
        const numerator = parseFloat(tableData.data[numeratorLabel]?.[columnName]) || 0;
        const denominator = parseFloat(tableData.data[denominatorLabel]?.[columnName]) || 0;
        
        return denominator !== 0 ? (numerator / denominator) * 100 : 0;
    }
    
    /**
     * Evaluate a formula expression
     */
    evaluateFormula(formula, columnName, tableData, rows, columns) {
        try {
            // Replace cell references with actual values
            let expression = formula;
            const matches = formula.match(/\[([^\]]+)\]/g);
            
            if (matches) {
                matches.forEach(match => {
                    const cellRef = match.slice(1, -1); // Remove brackets
                    const [rowRef, colRef] = cellRef.split(':');
                    
                    const rowLabel = this.getRowLabel(rowRef, rows);
                    const resolvedColumnName = colRef || columnName;
                    
                    const value = parseFloat(tableData.data[rowLabel]?.[resolvedColumnName]) || 0;
                    expression = expression.replace(match, value.toString());
                });
            }
            
            // Evaluate the mathematical expression
            return this.evaluateMathExpression(expression);
        } catch (error) {
            console.error('Error evaluating formula:', error);
            return 0;
        }
    }
    
    /**
     * Safely evaluate a mathematical expression
     */
    evaluateMathExpression(expression) {
        // Remove any non-mathematical characters for security
        const sanitized = expression.replace(/[^0-9+\-*/.() ]/g, '');
        
        try {
            // Use Function constructor for safe evaluation
            return new Function('return ' + sanitized)();
        } catch (error) {
            console.error('Invalid mathematical expression:', expression);
            return 0;
        }
    }
    
    /**
     * Get row label from row ID
     */
    getRowLabel(rowId, rows) {
        const row = rows.find(r => r.id === rowId);
        return row ? row.label : rowId;
    }
    
    /**
     * Get available calculation types
     */
    static getCalculationTypes() {
        return [
            {
                id: 'sum',
                name: 'Sum',
                description: 'Add values from selected rows',
                icon: 'fa-plus'
            },
            {
                id: 'average',
                name: 'Average',
                description: 'Calculate average of selected rows',
                icon: 'fa-calculator'
            },
            {
                id: 'percentage',
                name: 'Percentage',
                description: 'Calculate percentage between two rows',
                icon: 'fa-percentage'
            },
            {
                id: 'formula',
                name: 'Custom Formula',
                description: 'Use custom mathematical formula',
                icon: 'fa-function'
            }
        ];
    }
    
    /**
     * Validate a formula
     */
    static validateFormula(formula) {
        try {
            // Check for balanced brackets
            const openBrackets = (formula.match(/\[/g) || []).length;
            const closeBrackets = (formula.match(/\]/g) || []).length;
            
            if (openBrackets !== closeBrackets) {
                return { valid: false, error: 'Unbalanced brackets in formula' };
            }
            
            // Check for valid cell references
            const cellRefs = formula.match(/\[([^\]]+)\]/g);
            if (cellRefs) {
                for (const ref of cellRefs) {
                    const cellRef = ref.slice(1, -1);
                    if (!cellRef.match(/^[a-zA-Z0-9_-]+(:([a-zA-Z0-9_-]+))?$/)) {
                        return { valid: false, error: `Invalid cell reference: ${ref}` };
                    }
                }
            }
            
            return { valid: true };
        } catch (error) {
            return { valid: false, error: error.message };
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TableCalculationEngine;
} else {
    window.TableCalculationEngine = TableCalculationEngine;
}
