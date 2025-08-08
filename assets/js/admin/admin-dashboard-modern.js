/**
 * Modern Admin Dashboard JavaScript
 * Enhanced interactions and animations for improved UX
 */

class ModernAdminDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.setupAnimations();
        this.setupInteractions();
        this.setupResponsiveHandlers();
        this.setupLoadingStates();
        this.initializeCounters();
    }

    /**
     * Setup smooth animations and transitions
     */
    setupAnimations() {
        // Add staggered fade-in animations to cards
        const cards = document.querySelectorAll('.admin-card-modern, .admin-stat-card-modern');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('admin-fade-in');
        });

        // Intersection Observer for scroll animations
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            // Observe all dashboard elements
            document.querySelectorAll('.admin-card-modern, .admin-stat-card-modern').forEach(el => {
                observer.observe(el);
            });
        }
    }

    /**
     * Setup interactive elements
     */
    setupInteractions() {
        // Subtle hover effects for action cards (removed ripple effect)
        const actionCards = document.querySelectorAll('.admin-action-card-modern');
        actionCards.forEach(card => {
            card.addEventListener('click', function() {
                console.log('Quick action accessed:', this.querySelector('.admin-action-title-modern')?.textContent);
            });
        });

        // Subtle stat card interactions (removed excessive transforms)
        const statCards = document.querySelectorAll('.admin-stat-card-modern');
        statCards.forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.admin-stat-title-modern');
                if (title) {
                    console.log('Stat card accessed:', title.textContent);
                }
            });
        });

        // Progress bar animations
        this.animateProgressBars();
    }

    /**
     * Add ripple effect to interactive elements
     */
    addRippleEffect(element) {
        const ripple = document.createElement('div');
        ripple.className = 'ripple-effect';
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            background-color: rgba(255, 255, 255, 0.2);
            width: 100px;
            height: 100px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0);
        `;

        // Add CSS keyframes if not already present
        if (!document.querySelector('#ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'ripple-styles';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: translate(-50%, -50%) scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);

        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.remove();
            }
        }, 600);
    }

    /**
     * Animate progress bars
     */
    animateProgressBars() {
        const progressBars = document.querySelectorAll('.admin-progress-bar-modern');
        progressBars.forEach(bar => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1.5s cubic-bezier(0.4, 0, 0.2, 1)';
                bar.style.width = targetWidth;
            }, 500);
        });
    }

    /**
     * Animate number counters
     */
    initializeCounters() {
        const counters = document.querySelectorAll('.admin-stat-value-modern');
        
        counters.forEach(counter => {
            const text = counter.textContent.trim();
            const numbers = text.match(/\d+/g);
            
            if (numbers && numbers.length > 0) {
                // Animate the first number found
                const targetValue = parseInt(numbers[0]);
                if (targetValue <= 1000) { // Only animate reasonable numbers
                    this.animateCounter(counter, 0, targetValue, text);
                }
            }
        });
    }

    /**
     * Animate individual counter
     */
    animateCounter(element, start, end, originalText) {
        const duration = 2000;
        const increment = end / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }

            // Replace the number in the original text
            const newText = originalText.replace(/\d+/, Math.floor(current).toString());
            element.textContent = newText;
        }, 16);
    }

    /**
     * Setup responsive handlers
     */
    setupResponsiveHandlers() {
        // Handle mobile interactions
        if (window.matchMedia('(max-width: 768px)').matches) {
            // Reduce animation delays on mobile
            const cards = document.querySelectorAll('.admin-fade-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
            });
        }

        // Handle resize events
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
    }

    /**
     * Handle window resize
     */
    handleResize() {
        // Recalculate any dynamic layouts if needed
        const bentoGrid = document.querySelector('.admin-dashboard-bento');
        if (bentoGrid) {
            // Trigger reflow for grid items
            bentoGrid.style.display = 'none';
            bentoGrid.offsetHeight; // Force reflow
            bentoGrid.style.display = '';
        }
    }

    /**
     * Setup loading states
     */
    setupLoadingStates() {
        // Show loading states for async content
        const chartContainers = document.querySelectorAll('[id*="Chart"], [id*="Graphs"]');
        chartContainers.forEach(container => {
            if (!container.hasChildNodes()) {
                const loader = this.createLoader();
                container.appendChild(loader);

                // Remove loader when content is added
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length > 1) {
                            loader.remove();
                            observer.disconnect();
                        }
                    });
                });

                observer.observe(container, {
                    childList: true
                });
            }
        });
    }

    /**
     * Create loading spinner
     */
    createLoader() {
        const loader = document.createElement('div');
        loader.className = 'admin-loading-modern';
        loader.innerHTML = `
            <div class="admin-loading-spinner"></div>
            <span>Loading data...</span>
        `;
        return loader;
    }

    /**
     * Add smooth scroll behavior to internal links
     */
    setupSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Initialize tooltips for interactive elements
     */
    initializeTooltips() {
        // Add simple tooltip functionality
        const elementsWithTitles = document.querySelectorAll('[title]');
        elementsWithTitles.forEach(element => {
            const title = element.getAttribute('title');
            element.removeAttribute('title'); // Prevent default tooltip

            element.addEventListener('mouseenter', (e) => {
                const tooltip = document.createElement('div');
                tooltip.className = 'modern-tooltip';
                tooltip.textContent = title;
                tooltip.style.cssText = `
                    position: absolute;
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 12px;
                    white-space: nowrap;
                    z-index: 1000;
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.2s;
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
                
                setTimeout(() => tooltip.style.opacity = '1', 10);
                
                element._tooltip = tooltip;
            });

            element.addEventListener('mouseleave', () => {
                if (element._tooltip) {
                    element._tooltip.remove();
                    element._tooltip = null;
                }
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ModernAdminDashboard();
});

// Also initialize if script loads after DOM
if (document.readyState !== 'loading') {
    new ModernAdminDashboard();
}