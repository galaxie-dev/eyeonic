    <footer>
        <div class="footer-container">
            <p class="footer-copyright">© <span class="footer-year"><?= date('Y') ?></span> Eyeonic | All rights reserved</p>
            <div class="footer-social">
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>

<style>
    footer {
        background-color: #1d4ed8;
        padding: 1.5rem 1rem;
        margin-top: 2rem;
    }
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        color: #ffffff;
        font-size: 0.75rem;
    }
    .footer-copyright {
        margin: 0;
    }
    .footer-year {
        color: #facc15;
        font-weight: 600;
    }
    .footer-social {
        display: flex;
        gap: 1rem;
    }
    .footer-social a {
        color: #ffffff;
        font-size: 1.25rem;
        text-decoration: none;
        transition: color 0.2s;
    }
    .footer-social a:hover {
        color: #facc15;
    }
    @media (min-width: 768px) {
        .footer-container {
            flex-direction: row;
            justify-content: space-between;
        }
    }
</style>