document.querySelector('.menu-toggle').addEventListener('click', () => {
    document.querySelector('.mobile-menu').classList.toggle('active');
});

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