document.querySelector('.menu-toggle').addEventListener('click', () => {
    document.querySelector('.mobile-menu').classList.toggle('active');
});

let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;
const slider = document.querySelector('.slider');

function updateSlider() {
    slider.style.transform = `translateX(-${currentSlide * 100 / totalSlides}%)`;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlider();
}

document.querySelector('.next-btn').addEventListener('click', nextSlide);
document.querySelector('.prev-btn').addEventListener('click', prevSlide);

setInterval(nextSlide, 5000);

window.addEventListener('scroll', () => {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        if (cardTop < windowHeight) {
            card.style.transform = 'translateX(0)';
            card.style.opacity = '1';
        } else {
            card.style.transform = 'translateX(-100%)';
            card.style.opacity = '0';
        }
    });
});

// Accessibility: Ensure keyboard navigation
document.querySelectorAll('a, button').forEach(element => {
    element.setAttribute('tabindex', '0');
});