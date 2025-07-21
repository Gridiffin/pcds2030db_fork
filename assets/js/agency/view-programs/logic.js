/**
 * View Programs - Pure Logic Functions
 * Contains validation, data processing, and utility functions
 */

export class ViewProgramsLogic {
    constructor() {
        this.currentUserRole = window.currentUserRole || '';
    }
    
    /**
     * Validate program data
     */
    validateProgramData(program) {
        if (!program || typeof program !== 'object') {
            return false;
        }
        
        return program.program_id && program.program_name;
    }
    
    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return 'Not set';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        } catch (error) {
            console.warn('Invalid date string:', dateString);
            return 'Invalid date';
        }
    }
    
    /**
     * Get rating badge configuration
     */
    getRatingConfig(rating) {
        const ratingMap = {
            'not_started': {
                label: 'Not Started',
                class: 'secondary',
                icon: 'fas fa-hourglass-start',
                order: 4
            },
            'on_track_for_year': {
                label: 'On Track for Year',
                class: 'warning',
                icon: 'fas fa-calendar-check',
                order: 2
            },
            'monthly_target_achieved': {
                label: 'Monthly Target Achieved',
                class: 'success',
                icon: 'fas fa-check-circle',
                order: 1
            },
            'severe_delay': {
                label: 'Severe Delays',
                class: 'danger',
                icon: 'fas fa-exclamation-triangle',
                order: 3
            }
        };
        
        return ratingMap[rating] || ratingMap['not_started'];
    }
    
    /**
     * Check user permissions for program actions
     */
    canEditProgram(program) {
        if (!program) return false;
        
        // This should match the PHP logic
        return this.currentUserRole === 'focal' || 
               this.currentUserRole === 'admin' ||
               (program.created_by && program.created_by == window.currentUserId);
    }
    
    canDeleteProgram(program) {
        if (!program) return false;
        
        return this.currentUserRole === 'focal' || 
               this.currentUserRole === 'admin' ||
               (program.created_by && program.created_by == window.currentUserId);
    }
    
    /**
     * Filter programs by search term
     */
    filterBySearch(programs, searchTerm) {
        if (!searchTerm || searchTerm.trim() === '') {
            return programs;
        }
        
        const term = searchTerm.toLowerCase().trim();
        
        return programs.filter(program => {
            const programName = (program.program_name || '').toLowerCase();
            const programNumber = (program.program_number || '').toLowerCase();
            const initiativeName = (program.initiative_name || '').toLowerCase();
            
            return programName.includes(term) || 
                   programNumber.includes(term) || 
                   initiativeName.includes(term);
        });
    }
    
    /**
     * Filter programs by rating
     */
    filterByRating(programs, rating) {
        if (!rating || rating === '') {
            return programs;
        }
        
        // Convert filter value to database rating
        const ratingMap = {
            'target-achieved': 'monthly_target_achieved',
            'on-track-yearly': 'on_track_for_year',
            'severe-delay': 'severe_delay',
            'not-started': 'not_started'
        };
        
        const dbRating = ratingMap[rating] || rating;
        
        return programs.filter(program => {
            return (program.rating || 'not_started') === dbRating;
        });
    }
    
    /**
     * Filter programs by type
     */
    filterByType(programs, type) {
        if (!type || type === '') {
            return programs;
        }
        
        return programs.filter(program => {
            const isAssigned = program.is_assigned ? 'assigned' : 'created';
            return isAssigned === type;
        });
    }
    
    /**
     * Filter programs by initiative
     */
    filterByInitiative(programs, initiativeId) {
        if (!initiativeId || initiativeId === '') {
            return programs;
        }
        
        if (initiativeId === 'no-initiative') {
            return programs.filter(program => !program.initiative_id);
        }
        
        return programs.filter(program => {
            return program.initiative_id == initiativeId;
        });
    }
    
    /**
     * Sort programs by column
     */
    sortPrograms(programs, column, direction = 'asc') {
        const sortedPrograms = [...programs];
        
        sortedPrograms.sort((a, b) => {
            let valueA, valueB;
            
            switch (column) {
                case 'name':
                    valueA = (a.program_name || '').toLowerCase();
                    valueB = (b.program_name || '').toLowerCase();
                    break;
                    
                case 'initiative':
                    valueA = (a.initiative_name || 'zzz_no_initiative').toLowerCase();
                    valueB = (b.initiative_name || 'zzz_no_initiative').toLowerCase();
                    break;
                    
                case 'rating':
                    const ratingA = this.getRatingConfig(a.rating || 'not_started');
                    const ratingB = this.getRatingConfig(b.rating || 'not_started');
                    valueA = ratingA.order;
                    valueB = ratingB.order;
                    break;
                    
                case 'date':
                    valueA = new Date(a.updated_at || a.created_at || 0).getTime();
                    valueB = new Date(b.updated_at || b.created_at || 0).getTime();
                    break;
                    
                default:
                    return 0;
            }
            
            if (valueA < valueB) return direction === 'asc' ? -1 : 1;
            if (valueA > valueB) return direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        return sortedPrograms;
    }
    
    /**
     * Calculate program statistics
     */
    calculateStats(programs) {
        const total = programs.length;
        const byRating = {};
        const byType = {};
        
        programs.forEach(program => {
            const rating = program.rating || 'not_started';
            const type = program.is_assigned ? 'assigned' : 'created';
            
            byRating[rating] = (byRating[rating] || 0) + 1;
            byType[type] = (byType[type] || 0) + 1;
        });
        
        return { total, byRating, byType };
    }
    
    /**
     * Truncate text for display
     */
    truncateText(text, maxLength = 50) {
        if (!text || text.length <= maxLength) {
            return text || '';
        }
        
        return text.substring(0, maxLength - 3) + '...';
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}
