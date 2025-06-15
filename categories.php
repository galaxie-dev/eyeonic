<style>
    :root {
        --primary: #2563eb;
        --primary-light: #3b82f6;
        --primary-dark: #1d4ed8;
        --secondary: #e0f2fe;
        --dark: #1e293b;
        --light: #f8fafc;
    }

    .categories-section {
        padding: 1rem 0.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        position: relative;
        overflow: hidden;
    }

    .categories-container {
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
        padding: 0 1rem;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0f172a;
        text-align: center;
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 2px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #64748b;
        text-align: center;
        max-width: 600px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
    }

    .category-card {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        height: 350px;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        transform: translateY(0);
        will-change: transform;
    }

    .category-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 30px 60px rgba(37, 99, 235, 0.2);
        border-color: rgba(37, 99, 235, 0.3);
    }

    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 50%, transparent 100%);
        z-index: 1;
        opacity: 0.8;
        transition: opacity 0.4s ease;
    }

    .category-card:hover .category-overlay {
        opacity: 0.6;
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .category-card:hover .category-image {
        transform: scale(1.05);
    }

    .category-content {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 1.5rem;
        z-index: 2;
        transition: all 0.4s ease;
    }

    .category-card:hover .category-content {
        transform: translateY(-10px);
    }

    .category-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    .category-content p {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.5rem;
    }

    .category-count {
        display: inline-block;
        font-size: 0.9rem;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        margin-top: 0.5rem;
    }

    .category-hover-content {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        padding: 1.5rem;
        z-index: 3;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.4s ease;
        text-align: center;
    }

    .category-card:hover .category-hover-content {
        opacity: 1;
        transform: translateY(-50%);
    }

    .explore-btn {
        background: transparent;
        color: white;
        border: 2px solid white;
        padding: 0.6rem 1.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
        background: rgba(255, 255, 255, 0.1);
    }

    .explore-btn:hover {
        background: white;
        color: #0f172a;
        transform: translateY(-2px);
    }

    /* Background decorative elements */
    .categories-section::before {
        content: '';
        position: absolute;
        top: -200px;
        right: -200px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        z-index: 1;
    }

    .categories-section::after {
        content: '';
        position: absolute;
        bottom: -300px;
        left: -200px;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        z-index: 1;
    }



    /* Mobile-first media queries (for screens < 768px) */
@media (max-width: 768px) {
    .categories-section {
        padding: 1.5rem 0.75rem;
    }
    
    .categories-container {
        padding: 0 0.75rem;
    }
    
    .section-title {
        font-size: 1.8rem;
        margin-bottom: 0.75rem;
    }
    
    .section-title::after {
        height: 3px;
        bottom: -8px;
    }
    
    .section-subtitle {
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        max-width: 90%;
    }
    
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .category-card {
        height: 260px;
        border-radius: 12px;
    }
    
    .category-content {
        padding: 1.25rem;
    }
    
    .category-content h3 {
        font-size: 1.3rem;
        margin-bottom: 0.25rem;
    }
    
    .category-content p {
        font-size: 0.85rem;
    }
    
    .category-count {
        font-size: 0.8rem;
        padding: 0.2rem 0.6rem;
    }
    
    .category-hover-content {
        padding: 1rem;
    }
    
    .explore-btn {
        padding: 0.5rem 1.25rem;
        font-size: 0.85rem;
    }
    
    /* Adjust decorative elements for mobile */
    .categories-section::before,
    .categories-section::after {
        display: none;
    }
}

/* Extra small devices (phones, < 480px) */
@media (max-width: 480px) {
    .categories-section {
        padding: 1.25rem 0.5rem;
    }
    
    .section-title {
        font-size: 1.6rem;
    }
    
    .section-subtitle {
        font-size: 0.9rem;
        margin-bottom: 1.25rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .category-card {
        height: 220px;
    }
    
    .category-content {
        padding: 1rem;
    }
    
    .category-content h3 {
        font-size: 1.2rem;
    }
}
</style>

<section class="categories-section">
    <div class="categories-container">
        <h2 class="section-title">Our Collections</h2>
        <p class="section-subtitle">Premium eyewear curated for every style and need</p>
        
        <div class="categories-grid">
            <!-- Category 1 -->
            <div class="category-card" data-category="sunglasses">
                <div class="category-overlay"></div>
                <img src="public/img/catt7.png" class="category-image">
                <div class="category-content">
                    <h3>Sunglasses</h3>
                    <p>UV protection with prescription options</p>
                    <span class="category-count">Bask in the full summer sun</span>
                </div>
                <div class="category-hover-content">
                    <a href="products.php"><button class="explore-btn">Explore Collection</button></a>
                </div>
            </div>
            
            <!-- Category 2 -->
            <div class="category-card" data-category="eyeglasses">
                <div class="category-overlay"></div>
                <img src="public/img/catt10.png" class="category-image">
                <div class="category-content">
                    <h3>Eyeglasses</h3>
                    <p>Vision with elegance reducing eye strain</p>
                    <span class="category-count">Read Beyond the lens</span>
                </div>
                <div class="category-hover-content">
                    <a href="products.php"><button class="explore-btn">Explore Collection</button></a>
                </div>
            </div>
            
            <!-- Category 3 -->
            <div class="category-card" data-category="sports">
                <div class="category-overlay"></div>
                <img src="public/img/catt5.png" class="category-image">
                <div class="category-content">
                    <h3>Sports</h3>
                    <p>Performance eyewear, stay active for 24hrs </p>
                    <span class="category-count">Where chapions are made</span>
                </div>
                <div class="category-hover-content">
                    <a href="products.php"><button class="explore-btn">Explore Collection</button></a>
                </div>
            </div>
            
            <!-- Category 4 -->
            <div class="category-card" data-category="kids">
                <div class="category-overlay"></div>
                <img src="public/img/catt8.png" class="category-image">
                <div class="category-content">
                    <h3>Kids</h3>
                    <p>Durable & playful with resistant lenses</p>
                    <span class="category-count">Let's enjoy the moment</span>
                </div>
                <div class="category-hover-content">
                    <a href="products.php"><button class="explore-btn">Explore Collection</button></a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryCards = document.querySelectorAll('.category-card');
        
        // Add subtle parallax effect on mouse move
        categoryCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const x = e.clientX - card.getBoundingClientRect().left;
                const y = e.clientY - card.getBoundingClientRect().top;
                
                const centerX = card.offsetWidth / 2;
                const centerY = card.offsetHeight / 2;
                
                const moveX = (x - centerX) / 20;
                const moveY = (y - centerY) / 20;
                
                card.style.transform = `translateY(-10px) translateX(${moveX}px) translateY(${moveY}px)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(-10px)';
            });
            
            // Click handler
            card.addEventListener('click', function() {
                const category = this.dataset.category;
                // In a real implementation, you would navigate to the category page
                console.log(`Navigating to ${category} category`);
                // window.location.href = `products.php?category=${category}`;
            });
        });
        
        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, observerOptions);
        
        categoryCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    });
</script>