/**
 * Outcomes Management Logic Unit Tests
 * Tests for admin outcomes management functionality
 */

// Mock DOM elements and functions
document.body.innerHTML = `
    <div id="outcomeModal">
        <form id="outcomeForm">
            <input type="text" id="outcomeTitle" name="title" required>
            <textarea id="outcomeDescription" name="description" required></textarea>
            <select id="outcomeType" name="type" required>
                <option value="">Select Type</option>
                <option value="output">Output</option>
                <option value="outcome">Outcome</option>
                <option value="impact">Impact</option>
            </select>
            <input type="number" id="targetValue" name="target_value" min="0" step="0.01">
            <input type="text" id="unit" name="unit" placeholder="e.g., hectares, people">
            <button type="submit">Save Outcome</button>
        </form>
    </div>
    <div id="outcomeTable">
        <input type="text" id="outcomeSearch" placeholder="Search outcomes...">
        <select id="typeFilter">
            <option value="">All Types</option>
            <option value="output">Output</option>
            <option value="outcome">Outcome</option>
            <option value="impact">Impact</option>
        </select>
    </div>
`;

describe('Outcomes Management Logic', () => {
    let mockForm;
    let mockSearchInput;
    let mockTypeFilter;

    beforeEach(() => {
        // Reset DOM
        document.body.innerHTML = `
            <div id="outcomeModal">
                <form id="outcomeForm">
                    <input type="text" id="outcomeTitle" name="title" required>
                    <textarea id="outcomeDescription" name="description" required></textarea>
                    <select id="outcomeType" name="type" required>
                        <option value="">Select Type</option>
                        <option value="output">Output</option>
                        <option value="outcome">Outcome</option>
                        <option value="impact">Impact</option>
                    </select>
                    <input type="number" id="targetValue" name="target_value" min="0" step="0.01">
                    <input type="text" id="unit" name="unit" placeholder="e.g., hectares, people">
                    <button type="submit">Save Outcome</button>
                </form>
            </div>
            <div id="outcomeTable">
                <input type="text" id="outcomeSearch" placeholder="Search outcomes...">
                <select id="typeFilter">
                    <option value="">All Types</option>
                    <option value="output">Output</option>
                    <option value="outcome">Outcome</option>
                    <option value="impact">Impact</option>
                </select>
            </div>
        `;

        mockForm = document.getElementById('outcomeForm');
        mockSearchInput = document.getElementById('outcomeSearch');
        mockTypeFilter = document.getElementById('typeFilter');
    });

    describe('Outcome Validation Functions', () => {
        test('should validate outcome title correctly', () => {
            const validateOutcomeTitle = (title) => {
                if (!title || title.trim() === '') {
                    return { valid: false, message: 'Outcome title is required' };
                }
                if (title.length < 5) {
                    return { valid: false, message: 'Outcome title must be at least 5 characters' };
                }
                if (title.length > 200) {
                    return { valid: false, message: 'Outcome title must be less than 200 characters' };
                }
                return { valid: true, message: 'Outcome title is valid' };
            };

            expect(validateOutcomeTitle('')).toEqual({ valid: false, message: 'Outcome title is required' });
            expect(validateOutcomeTitle('Test')).toEqual({ valid: false, message: 'Outcome title must be at least 5 characters' });
            expect(validateOutcomeTitle('a'.repeat(201))).toEqual({ valid: false, message: 'Outcome title must be less than 200 characters' });
            expect(validateOutcomeTitle('Valid Outcome Title')).toEqual({ valid: true, message: 'Outcome title is valid' });
        });

        test('should validate outcome description correctly', () => {
            const validateOutcomeDescription = (description) => {
                if (!description || description.trim() === '') {
                    return { valid: false, message: 'Outcome description is required' };
                }
                if (description.length < 10) {
                    return { valid: false, message: 'Outcome description must be at least 10 characters' };
                }
                if (description.length > 1000) {
                    return { valid: false, message: 'Outcome description must be less than 1000 characters' };
                }
                return { valid: true, message: 'Outcome description is valid' };
            };

            expect(validateOutcomeDescription('')).toEqual({ valid: false, message: 'Outcome description is required' });
            expect(validateOutcomeDescription('Short')).toEqual({ valid: false, message: 'Outcome description must be at least 10 characters' });
            expect(validateOutcomeDescription('a'.repeat(1001))).toEqual({ valid: false, message: 'Outcome description must be less than 1000 characters' });
            expect(validateOutcomeDescription('This is a valid outcome description with sufficient detail.')).toEqual({ valid: true, message: 'Outcome description is valid' });
        });

        test('should validate outcome type correctly', () => {
            const validateOutcomeType = (type) => {
                const validTypes = ['output', 'outcome', 'impact'];
                if (!type || type.trim() === '') {
                    return { valid: false, message: 'Outcome type is required' };
                }
                if (!validTypes.includes(type)) {
                    return { valid: false, message: 'Please select a valid outcome type' };
                }
                return { valid: true, message: 'Outcome type is valid' };
            };

            expect(validateOutcomeType('')).toEqual({ valid: false, message: 'Outcome type is required' });
            expect(validateOutcomeType('invalid')).toEqual({ valid: false, message: 'Please select a valid outcome type' });
            expect(validateOutcomeType('output')).toEqual({ valid: true, message: 'Outcome type is valid' });
            expect(validateOutcomeType('outcome')).toEqual({ valid: true, message: 'Outcome type is valid' });
            expect(validateOutcomeType('impact')).toEqual({ valid: true, message: 'Outcome type is valid' });
        });

        test('should validate target value correctly', () => {
            const validateTargetValue = (value) => {
                if (value === null || value === undefined || value === '') {
                    return { valid: true, message: 'Target value is optional' };
                }
                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    return { valid: false, message: 'Target value must be a valid number' };
                }
                if (numValue < 0) {
                    return { valid: false, message: 'Target value cannot be negative' };
                }
                return { valid: true, message: 'Target value is valid' };
            };

            expect(validateTargetValue('')).toEqual({ valid: true, message: 'Target value is optional' });
            expect(validateTargetValue('invalid')).toEqual({ valid: false, message: 'Target value must be a valid number' });
            expect(validateTargetValue('-5')).toEqual({ valid: false, message: 'Target value cannot be negative' });
            expect(validateTargetValue('100')).toEqual({ valid: true, message: 'Target value is valid' });
            expect(validateTargetValue('50.5')).toEqual({ valid: true, message: 'Target value is valid' });
        });

        test('should validate unit correctly', () => {
            const validateUnit = (unit) => {
                if (unit === null || unit === undefined || unit === '') {
                    return { valid: true, message: 'Unit is optional' };
                }
                if (unit.length > 50) {
                    return { valid: false, message: 'Unit must be less than 50 characters' };
                }
                return { valid: true, message: 'Unit is valid' };
            };

            expect(validateUnit('')).toEqual({ valid: true, message: 'Unit is optional' });
            expect(validateUnit('a'.repeat(51))).toEqual({ valid: false, message: 'Unit must be less than 50 characters' });
            expect(validateUnit('hectares')).toEqual({ valid: true, message: 'Unit is valid' });
            expect(validateUnit('people')).toEqual({ valid: true, message: 'Unit is valid' });
        });

        test('should validate complete outcome form', () => {
            const validateOutcomeForm = (formData) => {
                const errors = [];
                
                const titleValidation = validateOutcomeTitle(formData.title);
                if (!titleValidation.valid) errors.push(titleValidation.message);
                
                const descriptionValidation = validateOutcomeDescription(formData.description);
                if (!descriptionValidation.valid) errors.push(descriptionValidation.message);
                
                const typeValidation = validateOutcomeType(formData.type);
                if (!typeValidation.valid) errors.push(typeValidation.message);
                
                const targetValueValidation = validateTargetValue(formData.target_value);
                if (!targetValueValidation.valid) errors.push(targetValueValidation.message);
                
                const unitValidation = validateUnit(formData.unit);
                if (!unitValidation.valid) errors.push(unitValidation.message);
                
                return {
                    valid: errors.length === 0,
                    errors: errors
                };
            };

            const validateOutcomeTitle = (title) => {
                if (!title || title.trim() === '') {
                    return { valid: false, message: 'Outcome title is required' };
                }
                return { valid: true };
            };

            const validateOutcomeDescription = (description) => {
                if (!description || description.trim() === '') {
                    return { valid: false, message: 'Outcome description is required' };
                }
                return { valid: true };
            };

            const validateOutcomeType = (type) => {
                if (!type || type.trim() === '') {
                    return { valid: false, message: 'Outcome type is required' };
                }
                return { valid: true };
            };

            const validateTargetValue = (value) => {
                if (value === null || value === undefined || value === '') {
                    return { valid: true };
                }
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    return { valid: false, message: 'Target value must be a valid positive number' };
                }
                return { valid: true };
            };

            const validateUnit = (unit) => {
                if (unit === null || unit === undefined || unit === '') {
                    return { valid: true };
                }
                return { valid: true };
            };

            const validFormData = {
                title: 'Forest Conservation Program',
                description: 'This program aims to conserve forest areas through sustainable practices.',
                type: 'outcome',
                target_value: '1000',
                unit: 'hectares'
            };

            const invalidFormData = {
                title: '',
                description: 'Short',
                type: '',
                target_value: '-5',
                unit: 'a'.repeat(51)
            };

            expect(validateOutcomeForm(validFormData)).toEqual({ valid: true, errors: [] });
            expect(validateOutcomeForm(invalidFormData).valid).toBe(false);
            expect(validateOutcomeForm(invalidFormData).errors.length).toBeGreaterThan(0);
        });
    });

    describe('Outcome CRUD Operations', () => {
        test('should create outcome successfully', () => {
            const createOutcome = (outcomeData) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (outcomeData.title && outcomeData.description && outcomeData.type) {
                            resolve({
                                success: true,
                                outcome: {
                                    id: Math.floor(Math.random() * 1000),
                                    ...outcomeData,
                                    created_at: new Date().toISOString(),
                                    updated_at: new Date().toISOString()
                                }
                            });
                        } else {
                            reject(new Error('Invalid outcome data'));
                        }
                    }, 100);
                });
            };

            const outcomeData = {
                title: 'Forest Conservation Program',
                description: 'This program aims to conserve forest areas through sustainable practices.',
                type: 'outcome',
                target_value: '1000',
                unit: 'hectares'
            };

            return expect(createOutcome(outcomeData)).resolves.toMatchObject({
                success: true,
                outcome: expect.objectContaining({
                    title: 'Forest Conservation Program',
                    type: 'outcome',
                    target_value: '1000'
                })
            });
        });

        test('should handle outcome creation error', () => {
            const createOutcome = (outcomeData) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (!outcomeData.title) {
                            reject(new Error('Outcome title is required'));
                        } else {
                            resolve({ success: true, outcome: outcomeData });
                        }
                    }, 100);
                });
            };

            const invalidOutcomeData = {
                description: 'This is a description',
                type: 'outcome'
            };

            return expect(createOutcome(invalidOutcomeData)).rejects.toThrow('Outcome title is required');
        });

        test('should update outcome successfully', () => {
            const updateOutcome = (outcomeId, outcomeData) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (outcomeId && outcomeData) {
                            resolve({
                                success: true,
                                outcome: {
                                    id: outcomeId,
                                    ...outcomeData,
                                    updated_at: new Date().toISOString()
                                }
                            });
                        } else {
                            reject(new Error('Invalid outcome data'));
                        }
                    }, 100);
                });
            };

            const outcomeId = 123;
            const updateData = {
                title: 'Updated Forest Conservation Program',
                target_value: '1500'
            };

            return expect(updateOutcome(outcomeId, updateData)).resolves.toMatchObject({
                success: true,
                outcome: expect.objectContaining({
                    id: 123,
                    title: 'Updated Forest Conservation Program',
                    target_value: '1500'
                })
            });
        });

        test('should delete outcome successfully', () => {
            const deleteOutcome = (outcomeId) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (outcomeId) {
                            resolve({
                                success: true,
                                message: 'Outcome deleted successfully'
                            });
                        } else {
                            reject(new Error('Outcome ID is required'));
                        }
                    }, 100);
                });
            };

            return expect(deleteOutcome(123)).resolves.toMatchObject({
                success: true,
                message: 'Outcome deleted successfully'
            });
        });

        test('should get outcome by ID', () => {
            const getOutcomeById = (outcomeId) => {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (outcomeId) {
                            resolve({
                                success: true,
                                outcome: {
                                    id: outcomeId,
                                    title: 'Forest Conservation Program',
                                    description: 'This program aims to conserve forest areas.',
                                    type: 'outcome',
                                    target_value: '1000',
                                    unit: 'hectares',
                                    created_at: '2024-01-01T00:00:00Z'
                                }
                            });
                        } else {
                            reject(new Error('Outcome not found'));
                        }
                    }, 100);
                });
            };

            return expect(getOutcomeById(123)).resolves.toMatchObject({
                success: true,
                outcome: expect.objectContaining({
                    id: 123,
                    title: 'Forest Conservation Program',
                    type: 'outcome'
                })
            });
        });
    });

    describe('Outcome Metrics Calculation', () => {
        test('should calculate achievement percentage', () => {
            const calculateAchievementPercentage = (actual, target) => {
                if (!target || target <= 0) return 0;
                if (!actual || actual < 0) return 0;
                
                const percentage = (actual / target) * 100;
                return Math.min(percentage, 100); // Cap at 100%
            };

            expect(calculateAchievementPercentage(50, 100)).toBe(50);
            expect(calculateAchievementPercentage(100, 100)).toBe(100);
            expect(calculateAchievementPercentage(150, 100)).toBe(100); // Capped at 100%
            expect(calculateAchievementPercentage(0, 100)).toBe(0);
            expect(calculateAchievementPercentage(50, 0)).toBe(0);
            expect(calculateAchievementPercentage(-10, 100)).toBe(0);
        });

        test('should calculate progress status', () => {
            const calculateProgressStatus = (percentage) => {
                if (percentage >= 100) return 'completed';
                if (percentage >= 75) return 'on_track';
                if (percentage >= 50) return 'moderate_progress';
                if (percentage >= 25) return 'slow_progress';
                return 'not_started';
            };

            expect(calculateProgressStatus(100)).toBe('completed');
            expect(calculateProgressStatus(90)).toBe('on_track');
            expect(calculateProgressStatus(60)).toBe('moderate_progress');
            expect(calculateProgressStatus(30)).toBe('slow_progress');
            expect(calculateProgressStatus(10)).toBe('not_started');
        });

        test('should calculate trend analysis', () => {
            const calculateTrend = (currentValue, previousValue) => {
                if (!previousValue || previousValue === 0) return 'no_previous_data';
                
                const change = currentValue - previousValue;
                const percentageChange = (change / previousValue) * 100;
                
                if (percentageChange > 10) return 'significant_increase';
                if (percentageChange > 0) return 'moderate_increase';
                if (percentageChange === 0) return 'no_change';
                if (percentageChange > -10) return 'moderate_decrease';
                return 'significant_decrease';
            };

            expect(calculateTrend(110, 100)).toBe('moderate_increase');
            expect(calculateTrend(120, 100)).toBe('significant_increase');
            expect(calculateTrend(100, 100)).toBe('no_change');
            expect(calculateTrend(90, 100)).toBe('significant_decrease');
            expect(calculateTrend(80, 100)).toBe('significant_decrease');
            expect(calculateTrend(100, 0)).toBe('no_previous_data');
        });

        test('should aggregate outcome metrics', () => {
            const aggregateOutcomeMetrics = (outcomes) => {
                const metrics = {
                    total: outcomes.length,
                    byType: {},
                    totalTarget: 0,
                    totalActual: 0,
                    averageAchievement: 0
                };

                outcomes.forEach(outcome => {
                    // Count by type
                    metrics.byType[outcome.type] = (metrics.byType[outcome.type] || 0) + 1;
                    
                    // Sum targets and actuals
                    if (outcome.target_value) {
                        metrics.totalTarget += parseFloat(outcome.target_value);
                    }
                    if (outcome.actual_value) {
                        metrics.totalActual += parseFloat(outcome.actual_value);
                    }
                });

                // Calculate average achievement
                if (metrics.totalTarget > 0) {
                    metrics.averageAchievement = (metrics.totalActual / metrics.totalTarget) * 100;
                }

                return metrics;
            };

            const mockOutcomes = [
                { type: 'output', target_value: '100', actual_value: '80' },
                { type: 'outcome', target_value: '200', actual_value: '180' },
                { type: 'output', target_value: '150', actual_value: '150' }
            ];

            const result = aggregateOutcomeMetrics(mockOutcomes);

            expect(result.total).toBe(3);
            expect(result.byType.output).toBe(2);
            expect(result.byType.outcome).toBe(1);
            expect(result.totalTarget).toBe(450);
            expect(result.totalActual).toBe(410);
            expect(result.averageAchievement).toBeCloseTo(91.11, 2);
        });
    });

    describe('Outcome Reporting Functions', () => {
        test('should generate outcome summary report', () => {
            const generateOutcomeSummary = (outcomes) => {
                const summary = {
                    totalOutcomes: outcomes.length,
                    byType: {},
                    achievementRanges: {
                        completed: 0,
                        on_track: 0,
                        moderate_progress: 0,
                        slow_progress: 0,
                        not_started: 0
                    },
                    topPerformers: [],
                    needsAttention: []
                };

                outcomes.forEach(outcome => {
                    // Count by type
                    summary.byType[outcome.type] = (summary.byType[outcome.type] || 0) + 1;
                    
                    // Calculate achievement
                    const achievement = outcome.target_value && outcome.actual_value 
                        ? (outcome.actual_value / outcome.target_value) * 100 
                        : 0;
                    
                    // Categorize by achievement
                    if (achievement >= 100) summary.achievementRanges.completed++;
                    else if (achievement >= 75) summary.achievementRanges.on_track++;
                    else if (achievement >= 50) summary.achievementRanges.moderate_progress++;
                    else if (achievement >= 25) summary.achievementRanges.slow_progress++;
                    else summary.achievementRanges.not_started++;
                    
                    // Identify top performers and needs attention
                    if (achievement >= 90) {
                        summary.topPerformers.push(outcome);
                    } else if (achievement < 25) {
                        summary.needsAttention.push(outcome);
                    }
                });

                return summary;
            };

            const mockOutcomes = [
                { id: 1, title: 'High Achiever', type: 'output', target_value: 100, actual_value: 95 },
                { id: 2, title: 'Needs Help', type: 'outcome', target_value: 100, actual_value: 20 },
                { id: 3, title: 'On Track', type: 'output', target_value: 100, actual_value: 80 }
            ];

            const summary = generateOutcomeSummary(mockOutcomes);

            expect(summary.totalOutcomes).toBe(3);
            expect(summary.byType.output).toBe(2);
            expect(summary.byType.outcome).toBe(1);
            expect(summary.achievementRanges.on_track).toBe(2);
            expect(summary.achievementRanges.slow_progress).toBe(0);
            expect(summary.topPerformers).toHaveLength(1);
            expect(summary.needsAttention).toHaveLength(1);
        });

        test('should generate outcome comparison report', () => {
            const generateComparisonReport = (currentPeriod, previousPeriod) => {
                const comparison = {
                    periodComparison: {},
                    trendAnalysis: {},
                    recommendations: []
                };

                // Compare metrics between periods
                Object.keys(currentPeriod).forEach(metric => {
                    const current = currentPeriod[metric] || 0;
                    const previous = previousPeriod[metric] || 0;
                    const change = current - previous;
                    const percentageChange = previous > 0 ? (change / previous) * 100 : 0;

                    comparison.periodComparison[metric] = {
                        current,
                        previous,
                        change,
                        percentageChange
                    };
                });

                // Generate recommendations based on trends
                Object.keys(comparison.periodComparison).forEach(metric => {
                    const data = comparison.periodComparison[metric];
                    if (data.percentageChange < -10) {
                        comparison.recommendations.push(`Investigate decline in ${metric}`);
                    } else if (data.percentageChange > 20) {
                        comparison.recommendations.push(`Analyze significant increase in ${metric}`);
                    }
                });

                return comparison;
            };

            const currentPeriod = { achievement: 75, outcomes: 10, targets: 1000 };
            const previousPeriod = { achievement: 80, outcomes: 8, targets: 800 };

            const comparison = generateComparisonReport(currentPeriod, previousPeriod);

            expect(comparison.periodComparison.achievement.current).toBe(75);
            expect(comparison.periodComparison.achievement.previous).toBe(80);
            expect(comparison.periodComparison.achievement.change).toBe(-5);
            expect(comparison.recommendations.length).toBeGreaterThan(0);
        });

        test('should export outcome data to CSV', () => {
            const exportOutcomesToCSV = (outcomes) => {
                if (!outcomes || outcomes.length === 0) return '';

                const headers = ['ID', 'Title', 'Type', 'Target Value', 'Actual Value', 'Achievement %', 'Status'];
                const csvRows = [headers.join(',')];

                outcomes.forEach(outcome => {
                    const achievement = outcome.target_value && outcome.actual_value 
                        ? Math.round((outcome.actual_value / outcome.target_value) * 100)
                        : 0;
                    
                    const status = achievement >= 100 ? 'Completed' 
                        : achievement >= 75 ? 'On Track'
                        : achievement >= 50 ? 'Moderate Progress'
                        : achievement >= 25 ? 'Slow Progress'
                        : 'Not Started';

                    const row = [
                        outcome.id,
                        `"${outcome.title}"`,
                        outcome.type,
                        outcome.target_value || '',
                        outcome.actual_value || '',
                        achievement,
                        status
                    ];
                    
                    csvRows.push(row.join(','));
                });

                return csvRows.join('\n');
            };

            const mockOutcomes = [
                { id: 1, title: 'Forest Conservation', type: 'outcome', target_value: 100, actual_value: 80 },
                { id: 2, title: 'Tree Planting', type: 'output', target_value: 1000, actual_value: 950 }
            ];

            const csvData = exportOutcomesToCSV(mockOutcomes);

            expect(csvData).toContain('ID,Title,Type,Target Value,Actual Value,Achievement %,Status');
            expect(csvData).toContain('1,"Forest Conservation",outcome,100,80,80,On Track');
            expect(csvData).toContain('2,"Tree Planting",output,1000,950,95,On Track');
        });
    });

    describe('Outcome Search and Filtering', () => {
        test('should search outcomes by title and description', () => {
            const searchOutcomes = (query) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockOutcomes = [
                            { id: 1, title: 'Forest Conservation Program', description: 'Conserve forest areas' },
                            { id: 2, title: 'Tree Planting Initiative', description: 'Plant trees in urban areas' },
                            { id: 3, title: 'Wildlife Protection', description: 'Protect endangered species' }
                        ];

                        const filteredOutcomes = mockOutcomes.filter(outcome => 
                            outcome.title.toLowerCase().includes(query.toLowerCase()) ||
                            outcome.description.toLowerCase().includes(query.toLowerCase())
                        );

                        resolve({
                            success: true,
                            outcomes: filteredOutcomes,
                            count: filteredOutcomes.length
                        });
                    }, 100);
                });
            };

            return expect(searchOutcomes('forest')).resolves.toMatchObject({
                success: true,
                outcomes: expect.arrayContaining([
                    expect.objectContaining({ title: 'Forest Conservation Program' })
                ]),
                count: 1
            });
        });

        test('should filter outcomes by type', () => {
            const filterOutcomesByType = (type) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockOutcomes = [
                            { id: 1, title: 'Output 1', type: 'output' },
                            { id: 2, title: 'Outcome 1', type: 'outcome' },
                            { id: 3, title: 'Impact 1', type: 'impact' },
                            { id: 4, title: 'Output 2', type: 'output' }
                        ];

                        const filteredOutcomes = type ? mockOutcomes.filter(outcome => outcome.type === type) : mockOutcomes;

                        resolve({
                            success: true,
                            outcomes: filteredOutcomes,
                            count: filteredOutcomes.length
                        });
                    }, 100);
                });
            };

            return expect(filterOutcomesByType('output')).resolves.toMatchObject({
                success: true,
                outcomes: expect.arrayContaining([
                    expect.objectContaining({ type: 'output' }),
                    expect.objectContaining({ type: 'output' })
                ]),
                count: 2
            });
        });

        test('should filter outcomes by achievement range', () => {
            const filterOutcomesByAchievement = (minAchievement, maxAchievement) => {
                return new Promise((resolve) => {
                    setTimeout(() => {
                        const mockOutcomes = [
                            { id: 1, title: 'High Achiever', target_value: 100, actual_value: 95 },
                            { id: 2, title: 'Medium Achiever', target_value: 100, actual_value: 60 },
                            { id: 3, title: 'Low Achiever', target_value: 100, actual_value: 20 }
                        ];

                        const filteredOutcomes = mockOutcomes.filter(outcome => {
                            const achievement = outcome.target_value && outcome.actual_value 
                                ? (outcome.actual_value / outcome.target_value) * 100 
                                : 0;
                            return achievement >= minAchievement && achievement <= maxAchievement;
                        });

                        resolve({
                            success: true,
                            outcomes: filteredOutcomes,
                            count: filteredOutcomes.length
                        });
                    }, 100);
                });
            };

            return expect(filterOutcomesByAchievement(50, 100)).resolves.toMatchObject({
                success: true,
                outcomes: expect.arrayContaining([
                    expect.objectContaining({ title: 'High Achiever' }),
                    expect.objectContaining({ title: 'Medium Achiever' })
                ]),
                count: 2
            });
        });
    });

    describe('Outcome Error Handling', () => {
        test('should handle validation errors', () => {
            const handleValidationError = (errors) => {
                return {
                    success: false,
                    type: 'validation',
                    errors: errors,
                    message: 'Please correct the following errors: ' + errors.join(', ')
                };
            };

            const validationErrors = ['Outcome title is required', 'Outcome type is required'];
            const result = handleValidationError(validationErrors);

            expect(result.success).toBe(false);
            expect(result.type).toBe('validation');
            expect(result.errors).toEqual(validationErrors);
            expect(result.message).toContain('Please correct the following errors');
        });

        test('should handle data processing errors', () => {
            const handleDataProcessingError = (error, context) => {
                return {
                    success: false,
                    type: 'processing',
                    error: error.message,
                    context: context,
                    timestamp: new Date().toISOString()
                };
            };

            const error = new Error('Failed to process outcome data');
            const result = handleDataProcessingError(error, 'outcome_creation');

            expect(result.success).toBe(false);
            expect(result.type).toBe('processing');
            expect(result.error).toBe('Failed to process outcome data');
            expect(result.context).toBe('outcome_creation');
            expect(result.timestamp).toBeDefined();
        });
    });
}); 