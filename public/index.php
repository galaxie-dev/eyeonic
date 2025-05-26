<?php
include 'header.php';
require_once '../config/database.php';

// Fetch featured products
$featuredStmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 4");
$featuredProducts = $featuredStmt->fetchAll();

// Fetch categories
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        /* Paste the exact CSS from the Visionary HTML here */
        body {
            font-family: 'Inter', sans-serif;
        }
        .header-logo svg {
            width: 24px;
            height: 24px;
            color: #111827;
        }
        .header-logo-text {
            font-weight: 600;
            font-size: 1.125rem;
            color: #111827;
            user-select: none;
        }
        .nav-link {
            font-weight: 500;
            font-size: 0.875rem;
            color: #4b5563;
            transition: color 0.2s;
            text-decoration: none;
            margin-left: 2rem;
        }
        .nav-link:hover {
            color: #111827;
        }
        .btn-signin {
            display: inline-block;
            background-color: #111827;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.375rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-signin:hover {
            background-color: #1f2937;
        }
        .icon-button {
            color: #4b5563;
            font-size: 1rem;
            padding: 0.25rem;
            border-radius: 0.375rem;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: color 0.2s;
        }
        .icon-button:hover,
        .icon-button:focus {
            color: #111827;
            outline: none;
            box-shadow: 0 0 0 2px #111827;
        }
        .hero-section {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
            background-color: #b07f63;
            max-width: 1200px;
            margin: 1.5rem auto 0 auto;
        }
        .hero-image {
            width: 100%;
            height: auto;
            object-fit: cover;
            display: block;
        }
        .hero-content {
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.6), transparent);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            max-width: 48rem;
            color: white;
        }
        .hero-title {
            font-weight: 800;
            font-size: 1.875rem;
            line-height: 2.25rem;
            max-width: 18rem;
            margin: 0;
        }
        @media (min-width: 640px) {
            .hero-title {
                font-size: 2.25rem;
                line-height: 2.5rem;
            }
        }
        .hero-subtitle {
            font-size: 0.875rem;
            max-width: 18rem;
            margin-top: 0.5rem;
            line-height: 1.25rem;
        }
        .btn-shop {
            margin-top: 1.5rem;
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            width: max-content;
            transition: background-color 0.2s;
        }
        .btn-shop:hover {
            background-color: #1d4ed8;
        }
        .featured-section {
            max-width: 1200px;
            margin: 2.5rem auto 0 auto;
            padding: 0 1rem;
        }
        .featured-title {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            color: #111827;
        }
        .featured-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .featured-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .product-card {
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            background: white;
        }
        .product-image {
            width: 100%;
            height: auto;
            object-fit: contain;
            padding: 1.5rem;
            display: block;
        }
        .product-bg-1 { background-color: white; }
        .product-bg-2 { background-color: #dff3f3; }
        .product-bg-3 { background-color: #f9d9c7; }
        .product-info {
            padding: 0.5rem 0.5rem 0.75rem 0.5rem;
        }
        .product-name {
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 0.125rem;
            color: #111827;
        }
        .product-desc {
            font-size: 0.5625rem;
            line-height: 1rem;
            color: #6b7280;
            margin: 0;
        }
        .product-price {
            font-size: 0.75rem;
            font-weight: 600;
            color: #111827;
            margin-top: 0.25rem;
        }
        .product-button {
            margin-top: 0.5rem;
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .product-button:hover {
            background-color: #1d4ed8;
        }
        .commitment-section {
            max-width: 768px;
            margin: 2.5rem auto 0 auto;
            padding: 0 1rem;
        }
        .commitment-small-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            color: #111827;
        }
        .commitment-title {
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        .commitment-text {
            font-size: 0.625rem;
            color: #374151;
            margin-bottom: 2rem;
            line-height: 1rem;
        }
        .commitment-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .commitment-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .commitment-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 1rem;
            font-size: 0.625rem;
            color: #6b7280;
        }
        .commitment-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #111827;
            font-weight: 600;
            font-size: 0.6875rem;
        }
        .commitment-card-header i {
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }
        footer {
            border-top: 1px solid #e5e7eb;
            margin-top: 4rem;
            padding: 1.5rem 1rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.625rem;
            color: #9ca3af;
        }
        @media (min-width: 640px) {
            footer {
                flex-direction: row;
                justify-content: space-between;
            }
        }
        .footer-links {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        @media (min-width: 640px) {
            .footer-links {
                margin-bottom: 0;
            }
        }
        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #6b7280;
        }
        .footer-social {
            display: flex;
            gap: 1.5rem;
            color: #9ca3af;
        }
        .footer-social a {
            color: inherit;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .footer-social a:hover {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- <header>
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center space-x-2 header-logo">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <span class="header-logo-text">Eyeonic</span>
            </div>
            <div class="hidden md:flex">
                <?php foreach ($categories as $category): ?>
                    <a class="nav-link" href="products.php?category=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="flex items-center space-x-3">
                <button class="btn-signin hidden sm:inline-block" type="button">Sign In</button>
                <button aria-label="Search" class="icon-button" type="button"><i class="fas fa-search"></i></button>
                <button aria-label="Menu" class="icon-button md:hidden" type="button"><i class="fas fa-bars"></i></button>
            </div>
        </nav>
    </header> -->
    <main>
        <section class="hero-section">
            <img alt="Illustration of a woman wearing glasses with a brown background" class="hero-image" height="400" src="" width="1200"/>
            <div class="hero-content">
                <h1 class="hero-title">See the world <span>in a new light</span></h1>
                <p class="hero-subtitle">Discover our curated collection of spectacles, designed for clarity, comfort, and style. Find your perfect pair today.</p>
                <button class="btn-shop" type="button">Shop Now</button>
            </div>
        </section>
        <section class="featured-section">
            <h2 class="featured-title">Featured Products</h2>
            <div class="featured-grid">
                <?php 
                $backgrounds = ['product-bg-1', 'product-bg-2', 'product-bg-3'];
                $bgIndex = 0;
                foreach ($featuredProducts as $product): 
                    $bgClass = $backgrounds[$bgIndex % count($backgrounds)];
                    $bgIndex++;
                ?>
                    <div class="product-card">
                        <img alt="<?= htmlspecialchars($product['name']) ?>" class="product-image <?= $bgClass ?>" height="300" src="<?= htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/400') ?>" width="400"/>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="product-price">KES <?= number_format($product['price'], 2) ?></p>
                            <a href="product_details.php?id=<?= $product['id'] ?>"><button class="product-button">View Details</button></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="commitment-section">
            <h3 class="commitment-small-title">Why Choose Us?</h3>
            <h2 class="commitment-title">Our Commitment to You</h2>
            <p class="commitment-text">At Eyeonic, we’re dedicated to providing exceptional quality and service. From our carefully selected materials to our customer-focused approach, we ensure your satisfaction every step of the way.</p>
            <div class="commitment-grid">
                <div class="commitment-card">
                    <div class="commitment-card-header"><i class="fas fa-truck"></i>Fast Shipping</div>
                    <p>Get your spectacles delivered quickly and reliably.</p>
                </div>
                <div class="commitment-card">
                    <div class="commitment-card-header"><i class="fas fa-shield-alt"></i>Quality Assurance</div>
                    <p>We use only the finest materials and craftsmanship.</p>
                </div>
                <div class="commitment-card">
                    <div class="commitment-card-header"><i class="fas fa-thumbs-up"></i>Satisfaction Guaranteed</div>
                    <p>Love your new spectacles or get your money back.</p>
                </div>
            </div>
        </section>
    </main>
    <!-- <footer>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Contact</a>
            <a href="#">FAQ</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <div class="footer-social">
            <a aria-label="Twitter" href="#"><i class="fab fa-twitter"></i></a>
            <a aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
            <a aria-label="Facebook" href="#"><i class="fab fa-facebook"></i></a>
        </div>
    </footer> -->
</body>
</html>

<?php include 'footer.php'; ?>