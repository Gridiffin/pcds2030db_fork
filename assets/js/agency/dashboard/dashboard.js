/**
 * Agency Dashboard - Main JavaScript Entry Point
 * 
 * This file imports all dashboard-related JS modules
 * and provides the entry point for Vite bundling
 */

// Import CSS (Vite will extract this)
import '../../../css/agency/dashboard/dashboard.css';

// Import JavaScript modules
import { DashboardChart } from './chart.js';
import { DashboardLogic } from './logic.js';
import { InitiativeCarousel } from './initiatives.js';
import { ProgramsTable } from './programs.js';

/**
 * Dashboard main class
 */
class AgencyDashboard {
    constructor() {
        this.chart = null;
        this.logic = null;
        this.carousel = null;
        this.programsTable = null;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
        } else {
            this.initializeComponents();
        }
    }
    
    initializeComponents() {
        try {
            // Initialize chart component
            this.chart = new DashboardChart();
            
            // Initialize dashboard logic
            this.logic = new DashboardLogic();
            
            // Initialize initiative carousel
            this.carousel = new InitiativeCarousel();
            
            // Initialize programs table
            this.programsTable = new ProgramsTable();
            
            console.log('✅ Agency Dashboard initialized successfully');
        } catch (error) {
            console.error('❌ Error initializing Agency Dashboard:', error);
        }
    }
    
    /**
     * Refresh all dashboard components
     */
    refresh() {
        if (this.chart) this.chart.refresh();
        if (this.logic) this.logic.refresh();
        if (this.carousel) this.carousel.refresh();
        if (this.programsTable) this.programsTable.refresh();
    }
}

// Initialize dashboard when loaded
const dashboard = new AgencyDashboard();

// Make dashboard globally accessible for debugging
window.AgencyDashboard = dashboard;

export default AgencyDashboard;
