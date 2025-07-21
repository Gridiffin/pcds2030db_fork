/**
 * Jest setup file for dashboard tests
 * Creates mock implementations for dashboard components
 */

// Mock implementations for dashboard components
class MockDashboardChart {
    constructor() {
        this.chart = null;
        this.chartData = null;
    }
    
    init() {
        this.chartData = { labels: [], data: [] };
    }
    
    createChart() {
        this.chart = { update: jest.fn(), destroy: jest.fn() };
    }
    
    updateChart(data) {
        if (this.chart) {
            this.chart.data = data;
            this.chart.update();
        }
    }
    
    refresh() {
        this.createChart();
    }
    
    refreshData() {
        this.init();
        this.updateChart(this.chartData);
    }
    
    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }
}

class MockDashboardLogic {
    constructor() {
        this.stats = {};
    }
    
    initialize() {}
    
    updateStatCards(stats) {
        this.stats = stats;
    }
    
    loadAjaxData() {
        return Promise.resolve({ success: true });
    }
    
    refresh() {
        this.loadAjaxData();
    }
    
    destroy() {}
}

class MockInitiativeCarousel {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.autoplayInterval = null;
    }
    
    initialize() {
        this.totalSlides = document.querySelectorAll('.carousel-item').length;
        this.autoplayInterval = setInterval(() => {}, 8000);
    }
    
    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
    }
    
    previousSlide() {
        this.currentSlide = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
    }
    
    goToSlide(index) {
        if (index >= 0 && index < this.totalSlides) {
            this.currentSlide = index;
        }
    }
    
    isCarouselInView() {
        return true; // Mock implementation
    }
    
    refresh() {
        this.initialize();
        this.currentSlide = 0;
    }
    
    destroy() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
}

class MockProgramsTable {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.sortBy = 'title';
        this.sortOrder = 'asc';
        this.allPrograms = [];
        this.filteredPrograms = [];
        this.totalPages = 1;
    }
    
    initialize() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {}
    
    loadPrograms() {
        return Promise.resolve({ success: true, programs: [] });
    }
    
    renderTable(programs) {
        const tbody = document.querySelector('#programsTable tbody');
        if (tbody) {
            tbody.innerHTML = programs.length > 0 ? 
                programs.map(p => `<tr><td>${p.title || 'N/A'}</td></tr>`).join('') :
                '<tr><td colspan="4">No programs found</td></tr>';
        }
    }
    
    handleSearch() {
        const searchInput = document.getElementById('programSearchInput');
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        
        this.filteredPrograms = this.allPrograms.filter(program => 
            (program.title && program.title.toLowerCase().includes(searchTerm)) ||
            (program.program_number && program.program_number.toLowerCase().includes(searchTerm))
        );
    }
    
    applyFilters() {
        const statusFilter = document.getElementById('statusFilter');
        const status = statusFilter ? statusFilter.value : '';
        
        this.filteredPrograms = status ? 
            this.allPrograms.filter(p => p.status === status) : 
            [...this.allPrograms];
    }
    
    applySorting() {
        this.filteredPrograms.sort((a, b) => {
            const aVal = a[this.sortBy] || '';
            const bVal = b[this.sortBy] || '';
            const compare = aVal.toString().localeCompare(bVal.toString(), undefined, { numeric: true });
            return this.sortOrder === 'asc' ? compare : -compare;
        });
    }
    
    updatePagination() {
        this.totalPages = Math.ceil(this.filteredPrograms.length / this.itemsPerPage);
        
        const nextBtn = document.getElementById('nextPage');
        const prevBtn = document.getElementById('prevPage');
        const pageInfo = document.getElementById('pageInfo');
        
        if (nextBtn) nextBtn.disabled = this.currentPage >= this.totalPages;
        if (prevBtn) prevBtn.disabled = this.currentPage <= 1;
        if (pageInfo) pageInfo.textContent = `Page ${this.currentPage} of ${this.totalPages}`;
    }
    
    getCurrentPageItems() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredPrograms.slice(start, end);
    }
    
    generateViewLink(program) {
        return program.id ? `?page=agency_program_view&id=${program.id}` : '#';
    }
    
    generateEditLink(program) {
        return program.id ? `?page=agency_programs_edit&id=${program.id}` : '#';
    }
    
    refresh() {
        this.loadPrograms();
    }
    
    destroy() {}
}

// Set up global mocks for Jest
global.DashboardChart = MockDashboardChart;
global.DashboardLogic = MockDashboardLogic;
global.InitiativeCarousel = MockInitiativeCarousel;
global.ProgramsTable = MockProgramsTable;

// Export for ES6 modules
export { 
    MockDashboardChart as DashboardChart,
    MockDashboardLogic as DashboardLogic, 
    MockInitiativeCarousel as InitiativeCarousel,
    MockProgramsTable as ProgramsTable
};
