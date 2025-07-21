/**
 * Bento Grid Dashboard JavaScript
 * Handles interactions and animations for the Bento Grid layout
 */

class BentoDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeAnimations();
        this.setupCardInteractions();
        this.setupRefreshFunctionality();
    }

    setupEventListeners() {
        // Refresh dashboard button
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.refreshDashboard();
            });
        }

        // Include assigned programs toggle
        const includeAssignedToggle = document.getElementById('includeAssignedToggle');
        if (includeAssignedToggle) {
            includeAssignedToggle.addEventListener('change', (e) => {
                this.toggleAssignedPrograms(e.target.checked);
            });
        }

        // Card click handlers
        document.querySelectorAll('.bento-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.handleCardClick(e, card);
            });
        });
    }

    initializeAnimations() {
        // Add fade-in animation to cards
        const cards = document.querySelectorAll('.bento-card');
        cards.forEach((card, index) => {
            card.classList.add('fade-in');
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Add hover effects
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.addHoverEffect(card);
            });

            card.addEventListener('mouseleave', () => {
                this.removeHoverEffect(card);
            });
        });
    }

    setupCardInteractions() {
        // Add loading states
        document.querySelectorAll('.bento-card').forEach(card => {
            const refreshIcon = card.querySelector('.refresh-icon');
            if (refreshIcon) {
                refreshIcon.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.refreshCard(card);
                });
            }
        });

        // Add card-specific actions
        this.setupQuickActions();
        this.setupChartInteractions();
    }

    setupQuickActions() {
        const quickActionCards = document.querySelectorAll('.bento-card .btn');
        quickActionCards.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleQuickAction(e.target);
            });
        });
    }

    setupChartInteractions() {
        // Chart refresh functionality
        const chartCard = document.querySelector('.bento-card .chart-container');
        if (chartCard) {
            chartCard.addEventListener('click', () => {
                this.refreshChart();
            });
        }
    }

    setupRefreshFunctionality() {
        // Auto-refresh every 5 minutes
        setInterval(() => {
            this.autoRefresh();
        }, 5 * 60 * 1000);

        // Add refresh indicators
        this.addRefreshIndicators();
    }

    addRefreshIndicators() {
        const cards = document.querySelectorAll('.bento-card');
        cards.forEach(card => {
            const header = card.querySelector('.bento-card-header');
            if (header) {
                const refreshBtn = document.createElement('button');
                refreshBtn.className = 'btn btn-sm btn-outline-secondary refresh-icon ms-auto';
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
                refreshBtn.style.border = 'none';
                refreshBtn.style.background = 'transparent';
                refreshBtn.style.color = 'inherit';
                refreshBtn.style.opacity = '0.6';
                refreshBtn.style.transition = 'opacity 0.3s ease';
                
                refreshBtn.addEventListener('mouseenter', () => {
                    refreshBtn.style.opacity = '1';
                });
                
                refreshBtn.addEventListener('mouseleave', () => {
                    refreshBtn.style.opacity = '0.6';
                });

                header.appendChild(refreshBtn);
            }
        });
    }

    handleCardClick(e, card) {
        // Don't trigger if clicking on interactive elements
        if (e.target.closest('.btn') || e.target.closest('.refresh-icon')) {
            return;
        }

        // Add click effect
        this.addClickEffect(card);

        // Handle different card types
        const cardType = this.getCardType(card);
        this.handleCardTypeAction(cardType, card);
    }

    getCardType(card) {
        if (card.querySelector('.chart-container')) return 'chart';
        if (card.querySelector('.table')) return 'table';
        if (card.querySelector('.display-4')) return 'stat';
        if (card.querySelector('.list-group')) return 'list';
        if (card.querySelector('.d-grid')) return 'actions';
        return 'default';
    }

    handleCardTypeAction(type, card) {
        switch (type) {
            case 'chart':
                this.expandChart(card);
                break;
            case 'table':
                this.expandTable(card);
                break;
            case 'stat':
                this.showStatDetails(card);
                break;
            case 'list':
                this.expandList(card);
                break;
            case 'actions':
                // Actions cards don't need special handling
                break;
            default:
                console.log('Card clicked:', card);
        }
    }

    expandChart(card) {
        // Toggle fullscreen mode for chart
        card.classList.toggle('fullscreen');
        if (card.classList.contains('fullscreen')) {
            card.style.position = 'fixed';
            card.style.top = '0';
            card.style.left = '0';
            card.style.width = '100vw';
            card.style.height = '100vh';
            card.style.zIndex = '9999';
            card.style.borderRadius = '0';
        } else {
            card.style.position = '';
            card.style.top = '';
            card.style.left = '';
            card.style.width = '';
            card.style.height = '';
            card.style.zIndex = '';
            card.style.borderRadius = '';
        }
    }

    expandTable(card) {
        // Show table in modal
        const table = card.querySelector('.table');
        if (table) {
            this.showTableModal(table.cloneNode(true));
        }
    }

    expandList(card) {
        // Show list in modal
        const list = card.querySelector('.list-group');
        if (list) {
            this.showListModal(list.cloneNode(true));
        }
    }

    showStatDetails(card) {
        // Show detailed statistics
        const title = card.querySelector('.bento-card-title').textContent.trim();
        const value = card.querySelector('.display-4').textContent.trim();
        
        this.showStatModal(title, value);
    }

    showTableModal(table) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detailed View</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            ${table.outerHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    showListModal(list) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Recent Activity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${list.outerHTML}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    showStatModal(title, value) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title} Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="display-1 text-primary mb-3">${value}</div>
                        <p class="text-muted">Detailed information about ${title.toLowerCase()} will be displayed here.</p>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    addHoverEffect(card) {
        card.style.transform = 'translateY(-8px) scale(1.02)';
        card.style.boxShadow = '0 20px 60px rgba(0, 0, 0, 0.2)';
    }

    removeHoverEffect(card) {
        card.style.transform = '';
        card.style.boxShadow = '';
    }

    addClickEffect(card) {
        card.style.transform = 'scale(0.98)';
        setTimeout(() => {
            card.style.transform = '';
        }, 150);
    }

    refreshDashboard() {
        // Show loading state
        this.showLoadingState();
        
        // Refresh data via AJAX
        fetch(window.location.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update dashboard content
            this.updateDashboardContent(html);
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
            this.hideLoadingState();
        });
    }

    refreshCard(card) {
        // Show loading state for specific card
        card.classList.add('loading');
        
        // Simulate refresh delay
        setTimeout(() => {
            card.classList.remove('loading');
            this.showRefreshSuccess(card);
        }, 1000);
    }

    refreshChart() {
        if (window.dashboardChart && window.dashboardChart.update) {
            // Trigger chart refresh
            window.dashboardChart.update();
        }
    }

    toggleAssignedPrograms(include) {
        // Update dashboard data based on toggle
        const url = new URL(window.location);
        url.searchParams.set('include_assigned', include ? '1' : '0');
        
        // Reload with new parameters
        window.location.href = url.toString();
    }

    showLoadingState() {
        const cards = document.querySelectorAll('.bento-card');
        cards.forEach(card => {
            card.classList.add('loading');
        });
    }

    hideLoadingState() {
        const cards = document.querySelectorAll('.bento-card');
        cards.forEach(card => {
            card.classList.remove('loading');
        });
    }

    showRefreshSuccess(card) {
        const successIndicator = document.createElement('div');
        successIndicator.className = 'refresh-success';
        successIndicator.innerHTML = '<i class="fas fa-check text-success"></i>';
        successIndicator.style.position = 'absolute';
        successIndicator.style.top = '10px';
        successIndicator.style.right = '10px';
        successIndicator.style.zIndex = '10';
        
        card.appendChild(successIndicator);
        
        setTimeout(() => {
            if (successIndicator.parentNode) {
                successIndicator.parentNode.removeChild(successIndicator);
            }
        }, 2000);
    }

    updateDashboardContent(html) {
        // Parse the HTML and update specific sections
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update statistics
        const newStats = doc.querySelectorAll('.bento-card .display-4');
        const currentStats = document.querySelectorAll('.bento-card .display-4');
        
        newStats.forEach((newStat, index) => {
            if (currentStats[index]) {
                currentStats[index].textContent = newStat.textContent;
            }
        });
        
        // Update chart if needed
        if (window.programRatingChart) {
            // Trigger chart update
            this.refreshChart();
        }
        
        this.hideLoadingState();
    }

    autoRefresh() {
        // Silent refresh for data updates
        this.refreshDashboard();
    }

    handleQuickAction(button) {
        const action = button.textContent.trim();
        console.log('Quick action triggered:', action);
        
        // Add visual feedback
        button.classList.add('btn-loading');
        setTimeout(() => {
            button.classList.remove('btn-loading');
        }, 1000);
    }
}

// === Program Details Carousel Card Logic ===
function initProgramCarousel() {
    const carousel = document.getElementById('programCarouselCard');
    if (!carousel) return;
    const items = carousel.querySelectorAll('.carousel-item');
    const prevBtn = document.getElementById('carouselPrevBtn');
    const nextBtn = document.getElementById('carouselNextBtn');
    const indicators = document.getElementById('carouselIndicators');
    let current = 0;
    const total = items.length;

    function show(index) {
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
            item.style.display = i === index ? '' : 'none';
        });
        // Update indicators
        if (indicators) {
            indicators.innerHTML = '';
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('span');
                dot.className = 'carousel-dot' + (i === index ? ' active' : '');
                dot.style.display = 'inline-block';
                dot.style.width = '10px';
                dot.style.height = '10px';
                dot.style.margin = '0 4px';
                dot.style.borderRadius = '50%';
                dot.style.background = i === index ? '#007bff' : '#ccc';
                dot.style.cursor = 'pointer';
                dot.addEventListener('click', () => {
                    current = i;
                    show(current);
                });
                indicators.appendChild(dot);
            }
        }
    }

    function prev() {
        current = (current - 1 + total) % total;
        show(current);
    }
    function next() {
        current = (current + 1) % total;
        show(current);
    }

    if (prevBtn) prevBtn.onclick = prev;
    if (nextBtn) nextBtn.onclick = next;

    // Swipe support for mobile
    let startX = null;
    carousel.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            startX = e.touches[0].clientX;
        }
    });
    carousel.addEventListener('touchend', function(e) {
        if (startX !== null && e.changedTouches.length === 1) {
            const endX = e.changedTouches[0].clientX;
            if (endX - startX > 40) prev();
            else if (startX - endX > 40) next();
            startX = null;
        }
    });

    // Hide arrows/indicators if only one item
    if (total <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (indicators) indicators.style.display = 'none';
    } else {
        if (prevBtn) prevBtn.style.display = '';
        if (nextBtn) nextBtn.style.display = '';
        if (indicators) indicators.style.display = '';
    }

    // Show the first item
    show(current);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new BentoDashboard();
    initProgramCarousel();
});

// Add CSS for loading states
const style = document.createElement('style');
style.textContent = `
    .btn-loading {
        position: relative;
        pointer-events: none;
    }
    
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .refresh-success {
        animation: fadeInOut 2s ease-in-out;
    }
    
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    
    .bento-card.fullscreen {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style); 