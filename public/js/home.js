
class StatsCarousel {
    carousel;
    constructor(selector, options = {}) {
        this.carousel = document.querySelector(selector);
        if (!this.carousel) return;

        this.slides = this.carousel.querySelectorAll('.stat-slide');
        this.dots = this.carousel.querySelectorAll('.dot');
        this.prevBtn = this.carousel.querySelector('.prev');
        this.nextBtn = this.carousel.querySelector('.next');

        this.currentSlide = 0;
        this.autoSlideDelay = options.autoSlideDelay || 5000;
        this.autoSlideInterval = null;
        this.isPlaying = true;

        this.init();
    }

    init() {
        if (this.slides.length === 0) return;

        this.showSlide(0);

        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.changeSlide(-1));
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.changeSlide(1));
        }

        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });

        this.carousel.addEventListener('mouseenter', () => this.pause());
        this.carousel.addEventListener('mouseleave', () => this.play());

        document.addEventListener('keydown', (e) => this.handleKeyboard(e));

        this.setupTouchEvents();

        this.play();
    }

    showSlide(index) {
        if (index >= this.slides.length) {
            this.currentSlide = 0;
        } else if (index < 0) {
            this.currentSlide = this.slides.length - 1;
        } else {
            this.currentSlide = index;
        }

        this.slides.forEach(slide => {
            slide.classList.remove('active');
            slide.setAttribute('aria-hidden', 'true');
        });
        this.dots.forEach(dot => dot.classList.remove('active'));

        this.slides[this.currentSlide].classList.add('active');
        this.slides[this.currentSlide].setAttribute('aria-hidden', 'false');
        this.dots[this.currentSlide]?.classList.add('active');
    }

    changeSlide(direction) {
        this.showSlide(this.currentSlide + direction);
    }

    goToSlide(index) {
        this.showSlide(index);
        this.resetAutoSlide();
    }

    play() {
        if (this.isPlaying) return;
        this.isPlaying = true;
        this.autoSlideInterval = setInterval(() => {
            this.changeSlide(1);
        }, this.autoSlideDelay);
    }

    pause() {
        if (!this.isPlaying) return;
        this.isPlaying = false;
        clearInterval(this.autoSlideInterval);
    }

    resetAutoSlide() {
        this.pause();
        this.play();
    }

    handleKeyboard(e) {
        if (!this.isInViewport()) return;

        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                this.changeSlide(-1);
                this.resetAutoSlide();
                break;
            case 'ArrowRight':
                e.preventDefault();
                this.changeSlide(1);
                this.resetAutoSlide();
                break;
            case ' ':
                e.preventDefault();
                this.isPlaying ? this.pause() : this.play();
                break;
        }
    }

    setupTouchEvents() {
        let touchStartX = 0;
        let touchEndX = 0;

        this.carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        this.carousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        }, { passive: true });
    }

    handleSwipe(startX, endX) {
        const threshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.changeSlide(1);
            } else {
                this.changeSlide(-1);
            }
            this.resetAutoSlide();
        }
    }

    isInViewport() {
        const rect = this.carousel.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    destroy() {
        this.pause();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const mainCarousel = new StatsCarousel('.stats-carousel', {
        autoSlideDelay: 5000
    });
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = StatsCarousel;
}