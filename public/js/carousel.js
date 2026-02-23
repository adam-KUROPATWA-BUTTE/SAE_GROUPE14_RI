
    let currentSlide = 0;
    let autoSlideInterval;

    function showSlide(n) {
    const slides = document.querySelectorAll('.stat-slide');
    const dots = document.querySelectorAll('.dot');

    if (n >= slides.length) currentSlide = 0;
    if (n < 0) currentSlide = slides.length - 1;

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

    function changeSlide(direction) {
    currentSlide += direction;
    showSlide(currentSlide);
    resetAutoSlide();
}

    function goToSlide(n) {
    currentSlide = n;
    showSlide(currentSlide);
    resetAutoSlide();
}

    function autoSlide() {
    currentSlide++;
    showSlide(currentSlide);
}

    function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    autoSlideInterval = setInterval(autoSlide, 5000);
}

    document.addEventListener('DOMContentLoaded', () => {
    autoSlideInterval = setInterval(autoSlide, 5000);

    const carousel = document.querySelector('.stats-carousel');
    if (carousel) {
    carousel.addEventListener('mouseenter', () => {
    clearInterval(autoSlideInterval);
});

    carousel.addEventListener('mouseleave', () => {
    resetAutoSlide();
});
}
});

