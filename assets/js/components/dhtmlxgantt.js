/**
 * dhtmlxGantt Configuration for PCDS2030 Dashboard
 * 
 * This file configures dhtmlxGantt for displaying initiative programs and targets
 * with a custom timeline (years/quarters) and status-based coloring.
 */

class PCDSGanttChart {
    constructor(containerId, apiUrl) {
        this.containerId = containerId;
        this.apiUrl = apiUrl;
        this.gantt = gantt;
        this.statusColors = {
            'on-target': '#28a745',      // Green
            'at-risk': '#ffc107',        // Yellow/Orange
            'off-target': '#dc3545',     // Red
            'not-started': '#6c757d',    // Gray
            'completed': '#17a2b8'       // Blue
        };
        this.init();
    }

    init() {
        this.configureGantt();
        this.setupEventHandlers();
        this.loadData();
    }

    configureGantt() {
        // Configure timeline scales
        this.gantt.config.scales = [
            {
                unit: "year",
                step: 1,
                format: "%Y"
            },
            {
                unit: "quarter",
                step: 1,
                format: function(date) {
                    return "Q" + Math.floor((date.getMonth()) / 3 + 1);
                }
            }
        ];

        // General configuration
        this.gantt.config.date_format = "%Y-%m-%d";
        this.gantt.config.xml_date = "%Y-%m-%d";
        this.gantt.config.autosize = "y";
        this.gantt.config.fit_tasks = true;
        this.gantt.config.grid_width = 300;
        this.gantt.config.task_height = 25;
        this.gantt.config.row_height = 30;

        // Grid columns configuration
        this.gantt.config.columns = [
            {
                name: "number",
                label: "Number",
                width: 80,
                align: "center"
            },
            {
                name: "text",
                label: "Item",
                width: 220,
                tree: true
            }
        ];

        // Task template for custom styling
        this.gantt.templates.task_class = (start, end, task) => {
            if (task.type === 'target' && task.status) {
                return `gantt-task-${task.status}`;
            }
            if (task.type === 'program') {
                return 'gantt-task-program';
            }
            return '';
        };

        // Grid text template
        this.gantt.templates.grid_text = (task, column) => {
            if (column === 'number') {
                return task.number || '';
            }
            return task[column] || '';
        };

        // Tooltip template
        this.gantt.templates.tooltip_text = (start, end, task) => {
            if (task.type === 'target') {
                return `
                    <strong>${task.text}</strong><br>
                    Number: ${task.number || 'N/A'}<br>
                    Status: ${task.status || 'Unknown'}<br>
                    Period: ${this.gantt.templates.tooltip_date_format(start)} - ${this.gantt.templates.tooltip_date_format(end)}
                `;
            } else {
                return `
                    <strong>${task.text}</strong><br>
                    Number: ${task.number || 'N/A'}<br>
                    Type: Program
                `;
            }
        };

        // Initialize gantt
        this.gantt.init(this.containerId);
    }

    setupEventHandlers() {
        // Handle task loading
        this.gantt.attachEvent("onTaskLoading", (task) => {
            // Ensure proper date parsing
            if (task.start_date && typeof task.start_date === 'string') {
                task.start_date = this.gantt.date.parseDate(task.start_date, "xml_date");
            }
            if (task.end_date && typeof task.end_date === 'string') {
                task.end_date = this.gantt.date.parseDate(task.end_date, "xml_date");
            }
            return true;
        });

        // Handle double click events
        this.gantt.attachEvent("onTaskDblClick", (id, e) => {
            const task = this.gantt.getTask(id);
            if (task.type === 'target' && task.target_id) {
                // Could navigate to target details or show modal
                console.log('Target clicked:', task);
            }
            return false; // Prevent default edit dialog
        });
    }

    async loadData() {
        try {
            console.log('Loading data from:', this.apiUrl);
            
            const response = await fetch(this.apiUrl);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API response data:', data);
            
            if (data.success) {
                const ganttData = this.transformDataForGantt(data.data);
                console.log('Transformed gantt data:', ganttData);
                
                this.gantt.parse({ data: ganttData });
                console.log('dhtmlxGantt data loaded successfully');
            } else {
                console.error('API returned error:', data.message);
                this.showError('Failed to load gantt data: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading gantt data:', error);
            this.showError('Failed to load gantt data: ' + error.message);
        }
    }

    transformDataForGantt(apiData) {
        const tasks = [];
        let taskId = 1;

        console.log('Transforming API data:', apiData);

        // Get initiative date range for default dates
        const initiative = apiData.initiative || {};
        const initiativeStart = initiative.start_date || '2024-01-01';
        const initiativeEnd = initiative.end_date || '2025-12-31';

        console.log('Initiative dates:', { start: initiativeStart, end: initiativeEnd });

        // Ensure programs is an array
        const programs = Array.isArray(apiData.programs) ? apiData.programs : [];
        console.log('Programs to process:', programs.length);

        programs.forEach((program, programIndex) => {
            console.log(`Processing program ${programIndex}:`, program);
            
            // Add program as parent task
            const programTask = {
                id: taskId++,
                text: program.program_name || `Program ${program.program_id || programIndex + 1}`,
                number: program.program_number || '',
                type: 'program',
                start_date: initiativeStart,
                end_date: initiativeEnd,
                open: true,
                readonly: true
            };
            tasks.push(programTask);

            // Add targets as child tasks
            const targets = Array.isArray(program.targets) ? program.targets : [];
            console.log(`  Processing ${targets.length} targets for program ${programIndex}`);
            
            targets.forEach((target, targetIndex) => {
                console.log(`    Processing target ${targetIndex}:`, target);
                
                // Get the most recent status from status_by_period
                let targetStatus = 'not-started';
                if (target.status_by_period && typeof target.status_by_period === 'object') {
                    const statuses = Object.values(target.status_by_period);
                    if (statuses.length > 0) {
                        targetStatus = statuses[statuses.length - 1]; // Use latest status
                    }
                }
                
                const targetTask = {
                    id: taskId++,
                    text: target.target_text || `Target ${target.target_id || targetIndex + 1}`,
                    number: target.target_number || '',
                    type: 'target',
                    target_id: target.id || target.target_id,
                    status: targetStatus,
                    start_date: target.start_date || initiativeStart,
                    end_date: target.end_date || initiativeEnd,
                    parent: programTask.id,
                    readonly: true
                };
                tasks.push(targetTask);
            });
        });

        console.log('Final transformed tasks:', tasks);
        return tasks;
    }

    showError(message) {
        const container = document.getElementById(this.containerId);
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${message}
                </div>
            `;
        }
    }

    refresh() {
        this.loadData();
    }

    destroy() {
        if (this.gantt) {
            this.gantt.clearAll();
        }
    }
}
