/**
 * dhtmlxGantt Configuration for PCDS2030 Dashboard
 * 
 * Configures dhtmlxGantt to match the mock design and project requirements
 */

class PCDS2030Gantt {
    constructor(containerId) {
        this.containerId = containerId;
        this.gantt = null;
        this.data = [];
        this.initialized = false;
        
        this.init();
    }
    
    init() {
        if (typeof gantt === 'undefined') {
            console.error('dhtmlxGantt is not loaded. Please include the dhtmlxGantt library.');
            return;
        }
        
        this.gantt = gantt;
        this.configureGantt();
        this.setupTemplates();
        this.setupEvents();
        this.initialized = true;
    }
    
    configureGantt() {
        // Basic configuration
        this.gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
        this.gantt.config.date_format = "%Y-%m-%d %H:%i:%s";
        
        // Scale configuration for hierarchical timeline (years/quarters)
        this.gantt.config.scales = [
            {unit: "year", step: 1, format: "%Y"},
            {unit: "quarter", step: 1, format: "Q%q"}
        ];
        
        // Grid configuration
        this.gantt.config.columns = [
            {
                name: "text", 
                label: "Initiative / Program / Target", 
                width: 350, 
                tree: true,
                template: this.taskNameTemplate.bind(this)
            },
            {
                name: "start_date", 
                label: "Start Date", 
                width: 100,
                template: this.dateTemplate.bind(this)
            },
            {
                name: "end_date", 
                label: "End Date", 
                width: 100,
                template: this.dateTemplate.bind(this)
            },
            {
                name: "status", 
                label: "Status", 
                width: 120,
                template: this.statusTemplate.bind(this)
            }
        ];
        
        // Layout and appearance
        this.gantt.config.layout = {
            css: "gantt_container",
            rows: [
                {
                    cols: [
                        {view: "grid", scrollX: "gridScroll", scrollable: true, scrollY: "scrollVer"},
                        {resizer: true, width: 1},
                        {view: "timeline", scrollX: "scrollHor", scrollY: "scrollVer"},
                        {view: "scrollbar", id: "scrollVer"}
                    ]
                },
                {view: "scrollbar", id: "scrollHor", height: 20}
            ]
        };
        
        // Performance and behavior
        this.gantt.config.auto_scheduling = false;
        this.gantt.config.auto_scheduling_strict = false;
        this.gantt.config.work_time = true;
        this.gantt.config.correct_work_time = false;
        this.gantt.config.readonly = true; // Agency view is read-only
        
        // Timeline configuration
        this.gantt.config.min_column_width = 70;
        this.gantt.config.scale_height = 60;
        this.gantt.config.row_height = 40;
        
        // Tree configuration
        this.gantt.config.open_tree_initially = true;
        
        // Today marker
        this.gantt.plugins({
            marker: true,
            tooltip: true,
            auto_scheduling: false
        });
        
        // Add today marker
        this.gantt.addMarker({
            start_date: new Date(),
            css: "today",
            text: "Today",
            title: "Today: " + this.gantt.date.date_to_str("%Y-%m-%d")(new Date())
        });
    }
    
    setupTemplates() {
        // Task template for different task types (initiative, program, target)
        this.gantt.templates.task_class = (start, end, task) => {
            let classes = [];
            
            if (task.type === 'project') {
                classes.push('gantt_initiative_task');
            } else if (task.type === 'task') {
                classes.push('gantt_program_task');
            } else if (task.type === 'milestone') {
                classes.push('gantt_target_task');
            }
            
            if (task.status) {
                classes.push(`gantt_task_${task.status.toLowerCase().replace(/\s+/g, '_')}`);
            }
            
            return classes.join(' ');
        };
        
        // Tooltip template
        this.gantt.templates.tooltip_text = (start, end, task) => {
            const startDate = this.gantt.date.date_to_str("%M %d, %Y")(start);
            const endDate = this.gantt.date.date_to_str("%M %d, %Y")(end);
            const duration = this.gantt.calculateDuration(start, end);
            
            let content = `<div class="tooltip-title">${task.text}</div><div class="tooltip-content">`;
            
            if (task.type === 'project') {
                // Initiative tooltip
                content += `<div><strong>Type:</strong> Initiative</div>`;
                if (task.initiative_number) {
                    content += `<div><strong>Number:</strong> ${task.initiative_number}</div>`;
                }
            } else if (task.type === 'task') {
                // Program tooltip
                content += `<div><strong>Type:</strong> Program</div>`;
                if (task.program_number) {
                    content += `<div><strong>Program Number:</strong> ${task.program_number}</div>`;
                }
                if (task.agency_name) {
                    content += `<div><strong>Agency:</strong> ${task.agency_name}</div>`;
                }
            } else if (task.type === 'milestone') {
                // Target tooltip
                content += `<div><strong>Type:</strong> Target</div>`;
                if (task.target_number) {
                    content += `<div><strong>Target Number:</strong> ${task.target_number}</div>`;
                }
                if (task.year && task.quarter) {
                    content += `<div><strong>Period:</strong> Q${task.quarter} ${task.year}</div>`;
                }
                if (task.status_description) {
                    content += `<div><strong>Status:</strong> ${task.status_description}</div>`;
                }
            }
            
            content += `
                <div><strong>Start:</strong> ${startDate}</div>
                <div><strong>End:</strong> ${endDate}</div>
                <div><strong>Duration:</strong> ${duration} days</div>
            `;
            
            if (task.description) {
                content += `<div><strong>Description:</strong> ${task.description}</div>`;
            }
            
            content += '</div>';
            return content;
        };
        
        // Progress template
        this.gantt.templates.progress_text = (start, end, task) => {
            return Math.round(task.progress * 100) + "%";
        };
    }
    
    setupEvents() {
        // Handle task click for navigation
        this.gantt.attachEvent("onTaskClick", (id, e) => {
            const task = this.gantt.getTask(id);
            if (task.program_id) {
                // Navigate to program details if needed
                console.log('Program clicked:', task);
                return false; // Prevent default behavior
            }
            return true;
        });
        
        // Handle task double-click
        this.gantt.attachEvent("onTaskDblClick", (id, e) => {
            const task = this.gantt.getTask(id);
            if (task.program_id) {
                // Open program details in new tab/modal
                window.open(`../programs/view_program.php?id=${task.program_id}`, '_blank');
                return false;
            }
            return true;
        });
    }
    
    taskNameTemplate(task) {
        let html = '';
        
        // Add number badge based on task type
        if (task.type === 'project' && task.initiative_number) {
            html += `<span class="initiative-number badge badge-primary">${task.initiative_number}</span>`;
        } else if (task.type === 'task' && task.program_number) {
            html += `<span class="program-number badge badge-info">${task.program_number}</span>`;
        } else if (task.type === 'milestone' && task.target_number) {
            html += `<span class="target-number badge badge-success">${task.target_number}</span>`;
        }
        
        // Add status indicator for programs and targets
        if (task.status && (task.type === 'task' || task.type === 'milestone')) {
            html += `<span class="gantt_task_status status-${task.status.toLowerCase().replace(/\s+/g, '-')}"></span>`;
        }
        
        // Add task name
        html += `<span class="task-name">${task.text}</span>`;
        
        return html;
    }
    
    dateTemplate(task) {
        const field = arguments[1]; // column name
        const date = task[field];
        
        if (!date) return '<span class="text-muted">—</span>';
        
        return this.gantt.date.date_to_str("%M %d, %Y")(date);
    }
    
    statusTemplate(task) {
        if (!task.status) return '<span class="text-muted">—</span>';
        
        const statusClasses = {
            'completed': 'bg-success',
            'on track': 'bg-primary',
            'at risk': 'bg-warning',
            'delayed': 'bg-danger',
            'planning': 'bg-secondary',
            'active': 'bg-info'
        };
        
        const statusClass = statusClasses[task.status.toLowerCase()] || 'bg-secondary';
        
        return `<span class="badge ${statusClass}">${task.status}</span>`;
    }
    
    loadData(apiResponse) {
        if (!this.initialized) {
            console.error('Gantt chart not initialized');
            return;
        }
        
        console.log('loadData called with:', apiResponse);
        
        // Handle different data structures that might come from the API
        let dataToProcess;
        
        // The API returns { data: { data: [], links: [] } }
        if (apiResponse.data && apiResponse.data.data && Array.isArray(apiResponse.data.data)) {
            console.log('Found nested data.data structure');
            // Use the pre-formatted data directly without transformation
            dataToProcess = {
                tasks: apiResponse.data.data,
                links: apiResponse.data.links || []
            };
        }
        // Handle case where we get { data: [], links: [] }
        else if (apiResponse.data && Array.isArray(apiResponse.data)) {
            console.log('Found direct data array');
            dataToProcess = {
                tasks: apiResponse.data,
                links: apiResponse.links || []
            };
        }
        // For other structures, try transform
        else if (Array.isArray(apiResponse)) {
            console.log('Found direct array, transforming');
            dataToProcess = this.transformData(apiResponse);
        }
        // Last resort
        else {
            console.log('Using fallback data processing');
            const dataArray = apiResponse.data || [];
            dataToProcess = this.transformData(Array.isArray(dataArray) ? dataArray : [dataArray]);
        }
        
        console.log('Data to process:', dataToProcess);
        
        // Verify we have valid data
        if (!dataToProcess.tasks || !Array.isArray(dataToProcess.tasks) || dataToProcess.tasks.length === 0) {
            console.error('No tasks available to display');
            return;
        }
        
        // Process dates before sending to gantt
        dataToProcess.tasks.forEach(task => {
            if (task.start_date && !(task.start_date instanceof Date)) {
                task.start_date = this.parseDate(task.start_date);
            }
            if (task.end_date && !(task.end_date instanceof Date)) {
                task.end_date = this.parseDate(task.end_date);
            }
            // Ensure the date is valid
            if (!task.start_date || isNaN(task.start_date.getTime())) {
                console.warn('Invalid start_date for task:', task);
                task.start_date = new Date();
            }
            if (!task.end_date || isNaN(task.end_date.getTime())) {
                console.warn('Invalid end_date for task:', task);
                task.end_date = new Date(new Date().setDate(new Date().getDate() + 1));
            }
            // Ensure end_date is after start_date
            if (task.start_date >= task.end_date) {
                console.warn('End date is not after start date for task:', task);
                task.end_date = new Date(new Date(task.start_date).setDate(task.start_date.getDate() + 1));
            }
        });
        
        try {
            console.log('Parsing data into gantt:', dataToProcess);
            this.gantt.clearAll();
            this.gantt.parse({
                data: dataToProcess.tasks,
                links: dataToProcess.links || []
            });
            console.log('Gantt data loaded successfully');
        } catch (error) {
            console.error('Error parsing data into gantt:', error);
            throw error;
        }
    }
    
    transformData(initiatives) {
        console.log('Transforming data:', initiatives);
        
        if (!Array.isArray(initiatives)) {
            console.error('Expected array for initiatives, got:', typeof initiatives);
            return { tasks: [], links: [] };
        }
        
        try {
            // The data is already transformed by the API, we just need to ensure dates are properly parsed
            const tasks = initiatives.map(task => {
                // Ensure the task has valid start and end dates
                const startDate = this.parseDate(task.start_date);
                let endDate = this.parseDate(task.end_date);
                
                // Make sure end date is after start date
                if (endDate < startDate) {
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 1); // Add one day
                }
                
                return {
                    ...task,
                    start_date: startDate,
                    end_date: endDate
                };
            });
            
            return { tasks, links: [] };
        } catch (error) {
            console.error('Error transforming data:', error);
            return { tasks: [], links: [] };
        }
    }
    
    parseDate(dateString) {
        if (!dateString) return new Date();
        
        try {
            // Handle different date formats
            if (typeof dateString === 'string') {
                // Check if it's a MySQL date format (YYYY-MM-DD HH:MM:SS)
                if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(dateString)) {
                    return new Date(dateString.replace(' ', 'T'));
                }
                
                // Check if it's a simple date format (YYYY-MM-DD)
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                    return new Date(dateString + 'T00:00:00');
                }
            }
            
            // Default parsing
            const date = new Date(dateString);
            
            // Validate the date is valid
            if (isNaN(date.getTime())) {
                console.warn('Invalid date:', dateString);
                return new Date(); // Return current date as fallback
            }
            
            return date;
        } catch (error) {
            console.error('Error parsing date:', dateString, error);
            return new Date(); // Return current date as fallback
        }
    }
    
    calculateInitiativeProgress(programs) {
        if (!programs || programs.length === 0) return 0;
        
        const totalProgress = programs.reduce((sum, program) => {
            return sum + (program.progress || 0);
        }, 0);
        
        return totalProgress / programs.length;
    }
    
    mapProgramStatus(program) {
        // Map program data to status based on various factors
        if (program.status) return program.status;
        
        const now = new Date();
        const startDate = program.start_date ? new Date(program.start_date) : null;
        const endDate = program.end_date ? new Date(program.end_date) : null;
        
        if (!startDate) return 'Planning';
        if (startDate > now) return 'Planning';
        if (endDate && endDate < now) return program.progress >= 1 ? 'Completed' : 'Delayed';
        if (program.progress >= 1) return 'Completed';
        if (program.progress >= 0.8) return 'On Track';
        if (program.progress >= 0.5) return 'At Risk';
        
        return 'Active';
    }
    
    render() {
        if (!this.initialized) {
            console.error('Gantt chart not initialized');
            return;
        }
        
        this.gantt.init(this.containerId);
    }
    
    resize() {
        if (this.gantt && this.initialized) {
            this.gantt.setSizes();
        }
    }
    
    destroy() {
        if (this.gantt && this.initialized) {
            this.gantt.clearAll();
            this.gantt.destructor();
            this.initialized = false;
        }
    }
}

// Export for use in other files
window.PCDS2030Gantt = PCDS2030Gantt;
