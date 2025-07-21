/**
 * Agency Dashboard - Initiative Carousel Component
 * 
 * Handles the initiative carousel functionality
 */

export class InitiativeCarousel {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.autoplayInterval = null;
        this.autoplayDelay = 8000; // 8 seconds
        
        this.init();
    }
    
    init() {
        this.setupCarousel();
        this.setupEventListeners();
        this.startAutoplay();
    }
    
    setupCarousel() {
        const carouselInner = document.getElementById('initiativeCarouselInner');
        if (!carouselInner) return;
        
        const slides = carouselInner.querySelectorAll('.carousel-item');
        this.totalSlides = slides.length;
        
        if (this.totalSlides === 0) {
            console.warn('⚠️ No initiative carousel slides found');
            return;
        }
        
        // Setup indicators
        this.setupIndicators();
        
        // Show first slide
        this.showSlide(0);
        
        console.log(`🎠 Initiative carousel initialized with ${this.totalSlides} slides`);
    }
    
    setupIndicators() {
        const indicatorsContainer = document.getElementById('carouselIndicators');
        if (!indicatorsContainer) return;
        
        indicatorsContainer.innerHTML = '';
        
        for (let i = 0; i < this.totalSlides; i++) {
            const indicator = document.createElement('button');
            indicator.setAttribute('aria-label', `Slide ${i + 1}`);
            indicator.addEventListener('click', () => this.goToSlide(i));
            
            if (i === 0) {
                indicator.classList.add('active');
            }
            
            indicatorsContainer.appendChild(indicator);
        }
    }
    
    setupEventListeners() {
        // Previous button
        const prevBtn = document.getElementById('carouselPrevBtn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousSlide());
        }
        
        // Next button
        const nextBtn = document.getElementById('carouselNextBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextSlide());
        }
        
        // Pause autoplay on hover
        const carouselCard = document.getElementById('programCarouselCard');
        if (carouselCard) {
            carouselCard.addEventListener('mouseenter', () => this.pauseAutoplay());
            carouselCard.addEventListener('mouseleave', () => this.startAutoplay());
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.isCarouselInView()) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.previousSlide();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.nextSlide();
                }
            }
        });
    }
    
    showSlide(index) {
        const carouselInner = document.getElementById('initiativeCarouselInner');
        if (!carouselInner) return;
        
        const slides = carouselInner.querySelectorAll('.carousel-item');
        const indicators = document.querySelectorAll('#carouselIndicators button');
        
        // Hide all slides
        slides.forEach(slide => slide.classList.remove('active'));
        
        // Show target slide
        if (slides[index]) {
            slides[index].classList.add('active');
        }
        
        // Update indicators
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
        
        this.currentSlide = index;
    }
    
    nextSlide() {
        const nextIndex = (this.currentSlide + 1) % this.totalSlides;
        this.goToSlide(nextIndex);
    }
    
    previousSlide() {
        const prevIndex = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.goToSlide(prevIndex);
    }
    
    goToSlide(index) {
        if (index >= 0 && index < this.totalSlides) {
            this.showSlide(index);
            this.restartAutoplay(); // Restart autoplay timer
        }
    }
    
    startAutoplay() {
        if (this.totalSlides <= 1) return;
        
        this.pauseAutoplay(); // Clear any existing interval
        this.autoplayInterval = setInterval(() => {
            this.nextSlide();
        }, this.autoplayDelay);
    }
    
    pauseAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
    
    restartAutoplay() {
        this.startAutoplay();
    }
    
    isCarouselInView() {
        const carousel = document.getElementById('programCarouselCard');
        if (!carousel) return false;
        
        const rect = carousel.getBoundingClientRect();
        return rect.top >= 0 && rect.bottom <= window.innerHeight;
    }
    
    refresh() {
        // Refresh carousel by reinitializing
        this.pauseAutoplay();
        this.currentSlide = 0;
        this.setupCarousel();
        this.startAutoplay();
    }
    
    destroy() {
        this.pauseAutoplay();
    }
}
