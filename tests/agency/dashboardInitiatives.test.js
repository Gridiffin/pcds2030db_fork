/**
 * Dashboard Initiative Carousel Component Unit Tests
 * Tests the initiative carousel functionality
 */

import { InitiativeCarousel } from '../../assets/js/agency/dashboard/initiatives.js';

describe('InitiativeCarousel', () => {
    let carouselComponent;

    beforeEach(() => {
        // Reset DOM with carousel structure
        document.body.innerHTML = `
            <div id="programCarouselCard">
                <div id="initiativeCarouselInner">
                    <div class="carousel-item">Slide 1</div>
                    <div class="carousel-item">Slide 2</div>
                    <div class="carousel-item">Slide 3</div>
                </div>
                <button id="carouselPrevBtn">Previous</button>
                <button id="carouselNextBtn">Next</button>
                <div id="carouselIndicators"></div>
            </div>
        `;
        
        // Mock timers
        jest.useFakeTimers();
    });

    afterEach(() => {
        if (carouselComponent) {
            carouselComponent.destroy();
        }
        jest.useRealTimers();
    });

    describe('Initialization', () => {
        test('should initialize carousel with correct slide count', () => {
            carouselComponent = new InitiativeCarousel();
            
            expect(carouselComponent.totalSlides).toBe(3);
            expect(carouselComponent.currentSlide).toBe(0);
        });

        test('should create indicators for all slides', () => {
            carouselComponent = new InitiativeCarousel();
            
            const indicators = document.querySelectorAll('#carouselIndicators button');
            expect(indicators.length).toBe(3);
            expect(indicators[0].classList.contains('active')).toBe(true);
        });

        test('should show first slide as active', () => {
            carouselComponent = new InitiativeCarousel();
            
            const slides = document.querySelectorAll('.carousel-item');
            expect(slides[0].classList.contains('active')).toBe(true);
            expect(slides[1].classList.contains('active')).toBe(false);
            expect(slides[2].classList.contains('active')).toBe(false);
        });

        test('should handle empty carousel gracefully', () => {
            document.getElementById('initiativeCarouselInner').innerHTML = '';
            const consoleSpy = jest.spyOn(console, 'warn').mockImplementation();
            
            carouselComponent = new InitiativeCarousel();
            
            expect(consoleSpy).toHaveBeenCalledWith('⚠️ No initiative carousel slides found');
            expect(carouselComponent.totalSlides).toBe(0);
            
            consoleSpy.mockRestore();
        });
    });

    describe('Navigation Controls', () => {
        beforeEach(() => {
            carouselComponent = new InitiativeCarousel();
        });

        test('should navigate to next slide', () => {
            carouselComponent.nextSlide();
            
            expect(carouselComponent.currentSlide).toBe(1);
            
            const slides = document.querySelectorAll('.carousel-item');
            expect(slides[0].classList.contains('active')).toBe(false);
            expect(slides[1].classList.contains('active')).toBe(true);
        });

        test('should navigate to previous slide', () => {
            carouselComponent.goToSlide(1); // Start at slide 1
            carouselComponent.previousSlide();
            
            expect(carouselComponent.currentSlide).toBe(0);
            
            const slides = document.querySelectorAll('.carousel-item');
            expect(slides[0].classList.contains('active')).toBe(true);
            expect(slides[1].classList.contains('active')).toBe(false);
        });

        test('should wrap around at end (next)', () => {
            carouselComponent.goToSlide(2); // Go to last slide
            carouselComponent.nextSlide();
            
            expect(carouselComponent.currentSlide).toBe(0); // Should wrap to first
        });

        test('should wrap around at beginning (previous)', () => {
            carouselComponent.previousSlide(); // From slide 0
            
            expect(carouselComponent.currentSlide).toBe(2); // Should wrap to last
        });

        test('should handle button clicks', () => {
            const nextBtn = document.getElementById('carouselNextBtn');
            const prevBtn = document.getElementById('carouselPrevBtn');
            
            nextBtn.click();
            expect(carouselComponent.currentSlide).toBe(1);
            
            prevBtn.click();
            expect(carouselComponent.currentSlide).toBe(0);
        });

        test('should handle indicator clicks', () => {
            const indicators = document.querySelectorAll('#carouselIndicators button');
            
            indicators[2].click();
            expect(carouselComponent.currentSlide).toBe(2);
            
            const slides = document.querySelectorAll('.carousel-item');
            expect(slides[2].classList.contains('active')).toBe(true);
        });
    });

    describe('Autoplay Functionality', () => {
        beforeEach(() => {
            carouselComponent = new InitiativeCarousel();
        });

        test('should start autoplay on initialization', () => {
            expect(carouselComponent.autoplayInterval).toBeTruthy();
        });

        test('should advance slide automatically', () => {
            expect(carouselComponent.currentSlide).toBe(0);
            
            // Fast-forward autoplay timer
            jest.advanceTimersByTime(8000);
            
            expect(carouselComponent.currentSlide).toBe(1);
        });

        test('should pause autoplay on hover', () => {
            const carouselCard = document.getElementById('programCarouselCard');
            
            carouselCard.dispatchEvent(new Event('mouseenter'));
            
            expect(carouselComponent.autoplayInterval).toBeNull();
        });

        test('should resume autoplay on mouse leave', () => {
            const carouselCard = document.getElementById('programCarouselCard');
            
            // Pause first
            carouselCard.dispatchEvent(new Event('mouseenter'));
            expect(carouselComponent.autoplayInterval).toBeNull();
            
            // Resume
            carouselCard.dispatchEvent(new Event('mouseleave'));
            expect(carouselComponent.autoplayInterval).toBeTruthy();
        });

        test('should restart autoplay timer on manual navigation', () => {
            const originalInterval = carouselComponent.autoplayInterval;
            
            carouselComponent.goToSlide(1);
            
            expect(carouselComponent.autoplayInterval).not.toBe(originalInterval);
            expect(carouselComponent.autoplayInterval).toBeTruthy();
        });

        test('should not start autoplay with single slide', () => {
            // Reset with single slide
            document.getElementById('initiativeCarouselInner').innerHTML = '<div class="carousel-item">Single slide</div>';
            
            carouselComponent.destroy();
            carouselComponent = new InitiativeCarousel();
            
            expect(carouselComponent.autoplayInterval).toBeNull();
        });
    });

    describe('Keyboard Navigation', () => {
        beforeEach(() => {
            carouselComponent = new InitiativeCarousel();
            
            // Mock isCarouselInView to return true
            jest.spyOn(carouselComponent, 'isCarouselInView').mockReturnValue(true);
        });

        test('should navigate with arrow keys when carousel is in view', () => {
            // Right arrow - next slide
            const rightEvent = new KeyboardEvent('keydown', { key: 'ArrowRight' });
            document.dispatchEvent(rightEvent);
            
            expect(carouselComponent.currentSlide).toBe(1);
            
            // Left arrow - previous slide
            const leftEvent = new KeyboardEvent('keydown', { key: 'ArrowLeft' });
            document.dispatchEvent(leftEvent);
            
            expect(carouselComponent.currentSlide).toBe(0);
        });

        test('should not navigate when carousel is not in view', () => {
            jest.spyOn(carouselComponent, 'isCarouselInView').mockReturnValue(false);
            
            const rightEvent = new KeyboardEvent('keydown', { key: 'ArrowRight' });
            document.dispatchEvent(rightEvent);
            
            expect(carouselComponent.currentSlide).toBe(0); // Should not change
        });

        test('should ignore non-arrow keys', () => {
            const enterEvent = new KeyboardEvent('keydown', { key: 'Enter' });
            document.dispatchEvent(enterEvent);
            
            expect(carouselComponent.currentSlide).toBe(0); // Should not change
        });
    });

    describe('Slide Indicators', () => {
        beforeEach(() => {
            carouselComponent = new InitiativeCarousel();
        });

        test('should update indicators when slide changes', () => {
            carouselComponent.goToSlide(2);
            
            const indicators = document.querySelectorAll('#carouselIndicators button');
            expect(indicators[0].classList.contains('active')).toBe(false);
            expect(indicators[1].classList.contains('active')).toBe(false);
            expect(indicators[2].classList.contains('active')).toBe(true);
        });

        test('should handle out of bounds slide index', () => {
            const initialSlide = carouselComponent.currentSlide;
            
            carouselComponent.goToSlide(10); // Invalid index
            
            expect(carouselComponent.currentSlide).toBe(initialSlide); // Should not change
        });

        test('should handle negative slide index', () => {
            const initialSlide = carouselComponent.currentSlide;
            
            carouselComponent.goToSlide(-1); // Invalid index
            
            expect(carouselComponent.currentSlide).toBe(initialSlide); // Should not change
        });
    });

    describe('Viewport Detection', () => {
        beforeEach(() => {
            carouselComponent = new InitiativeCarousel();
        });

        test('should detect when carousel is in viewport', () => {
            // Mock getBoundingClientRect
            const mockRect = { top: 100, bottom: 200 };
            document.getElementById('programCarouselCard').getBoundingClientRect = jest.fn(() => mockRect);
            
            // Mock window height
            Object.defineProperty(window, 'innerHeight', { value: 800, configurable: true });
            
            const isInView = carouselComponent.isCarouselInView();
            expect(isInView).toBe(true);
        });

        test('should detect when carousel is out of viewport', () => {
            const mockRect = { top: -100, bottom: -50 }; // Above viewport
            document.getElementById('programCarouselCard').getBoundingClientRect = jest.fn(() => mockRect);
            
            Object.defineProperty(window, 'innerHeight', { value: 800, configurable: true });
            
            const isInView = carouselComponent.isCarouselInView();
            expect(isInView).toBe(false);
        });
    });

    describe('Cleanup', () => {
        test('should destroy autoplay on cleanup', () => {
            carouselComponent = new InitiativeCarousel();
            const interval = carouselComponent.autoplayInterval;
            
            carouselComponent.destroy();
            
            expect(carouselComponent.autoplayInterval).toBeNull();
        });

        test('should refresh carousel properly', () => {
            carouselComponent = new InitiativeCarousel();
            
            // Go to slide 2
            carouselComponent.goToSlide(2);
            expect(carouselComponent.currentSlide).toBe(2);
            
            // Refresh should reset to slide 0
            carouselComponent.refresh();
            expect(carouselComponent.currentSlide).toBe(0);
        });
    });
});
