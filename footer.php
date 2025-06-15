<footer>
    <!-- Gallery Section -->
    <div class="footer-gallery">
        <div class="gallery-container">
            <h3 class="gallery-title">Our Gallery</h3>
            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="public/img/catt9.png">
                    <div class="gallery-overlay">
                        <span>Classic Collection</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="public/img/catt11.png">
                    <div class="gallery-overlay">
                        <span>Modern Styles</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="public/img/catt6.png">
                    <div class="gallery-overlay">
                        <span>Sunglasses</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="public/img/catt2.png" alt="Designer frames">
                    <div class="gallery-overlay">
                        <span>Designer Series</span>
                    </div>
     
            </div>
        </div>
    </div>

    <!-- Footer Content -->
    <div class="footer-main">
        <div class="footer-container">
            <div class="footer-brand">
                <p class="footer-copyright">© <span class="footer-year"><?= date('Y') ?></span> Eyeonic</p>
                <p class="footer-rights">All rights reserved</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            
            <div class="footer-social">
                <p class="social-title">Follow Us</p>
                <div class="social-icons">
                    <a href="#" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="Pinterest">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"></path>
                            <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5" transform="rotate(45 12 12)"></path>
                        </svg>
                    </a>
                </div>
            </div>

              <div class="footer-brand">
                <p class="footer-copyright"> Built and Designed by: <a href="https://evansosumba.vercel.app" target="_blank"> <span class="footer-year">Evans Osumba</span></a></p>
                <!-- <p class="footer-rights">All rights reserved</p> -->
                
            </div>
            
          
        </div>
    </div>
</footer>
</body>
</html>

<style>
    footer {
        background: #111827;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }

    /* Gallery Section */
    .footer-gallery {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        padding: 4rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .gallery-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .gallery-title {
        font-size: 1.5rem;
        font-weight: 400;
        text-align: center;
        margin-bottom: 1.5rem;
        color: #f8fafc;
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }

    .gallery-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    }

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; 
    scrollbar-width: none; 
}

.gallery-item {
    position: relative;
    border-radius: 0; 
    height: 125px;
    width: 100%; 
    overflow: hidden;
    flex-shrink: 0; 
}


@media (max-width: 767px) {
    .gallery-grid {
        grid-template-columns: repeat(4, 1fr); /* 4 columns per row */
        grid-template-rows: repeat(2, 1fr); /* 2 rows */
        gap: 0;
    }
    
    .gallery-item {
        height: 100px; 
    }
}
    .gallery-grid::-webkit-scrollbar {
    display: none;
}

    .gallery-item:hover {
        transform: translateY(-5px);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    
    }

    .gallery-item:hover img {
        transform: scale(1.05);
    }

    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        padding: 1.5rem 1rem 1rem;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .gallery-overlay span {
        font-weight: 500;
        font-size: 1.1rem;
    }

    /* Footer Main Content */
    .footer-main {
        padding: 3rem 1rem;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: 3rem;
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .footer-copyright {
        font-size: 1rem;
        margin: 0;
        color: #f8fafc;
    }

    .footer-year {
        color: #facc15;
        font-weight: 600;
    }

    .footer-rights {
        font-size: 0.875rem;
        color: #94a3b8;
        margin: 0;
    }

    .footer-links {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
    }

    .footer-links a {
        color: #cbd5e1;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        color: #3b82f6;
    }

    .footer-social {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .social-title {
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
        color: #f8fafc;
    }

    .social-icons {
        display: flex;
        gap: 1.5rem;
    }

    .social-icons a {
        color: #cbd5e1;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .social-icons a:hover {
        color: white;
        background: rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
    }

    .footer-newsletter {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .newsletter-title {
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
        color: #f8fafc;
    }

    .newsletter-form {
        display: flex;
        gap: 0.5rem;
    }

    .newsletter-form input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 1px solid #334155;
        background: #1e293b;
        color: white;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .newsletter-form input::placeholder {
        color: #94a3b8;
    }

    .newsletter-form button {
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .newsletter-form button:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
    }

    .newsletter-note {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
    }

    /* Desktop Layout */
    @media (min-width: 768px) {
        .footer-container {
            grid-template-columns: repeat(3, 1fr);
        }

        .gallery-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 767px) {
        .newsletter-form {
            flex-direction: column;
        }
    }
</style>