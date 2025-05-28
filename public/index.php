<?php
// include 'header.php';
require_once '../config/database.php';
 include 'header.php'; 

$categoryId = $_GET['category'] ?? null;

// Fetch only featured products
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = TRUE
");
$stmt->execute();
$products = $stmt->fetchAll();

// Fetch categories for navigation
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();

// Helper function to validate image path
function getValidImagePath($imageUrl) {
    $basePath = '../';
    $defaultImage = '../assets/no-image.png';
    
    if (empty($imageUrl)) {
        return $defaultImage;
    }
    
    $fullPath = $basePath . $imageUrl;
    if (file_exists($fullPath) && is_readable($fullPath)) {
        return $fullPath;
    }
    
    return $defaultImage;
}

// Helper function to calculate discount percentage
function calculateDiscountPercentage($originalPrice, $discountPrice) {
    if ($originalPrice <= 0 || $discountPrice >= $originalPrice) {
        return 0;
    }
    return round((($originalPrice - $discountPrice) / $originalPrice) * 100);
}
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
    <!-- Splide Carousel CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <style>

          
    :root {
        --primary: #2563eb;
        --primary-light: #3b82f6;
        --primary-dark: #1d4ed8;
        --secondary: #e0f2fe;
        --dark: #1e293b;
        --light: #f8fafc;
        --accent: #f43f5e;
        --success: #10b981;
    }
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Hero Carousel Styles */
        .splide__slide {
            position: relative;
            height: auto;
        }
        .hero-content {
            position: absolute;
            inset: 0;
            background-color: transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            /* max-width: 48rem; */
            width: 100%;
            color: white;
              height: auto;
        }
        .hero-title {
            font-weight: 800;
            font-size: 1.875rem;
            line-height: 2.25rem;
            max-width: 18rem;
            margin: 0;
        }
        .hero-image{
            width: 100%;
            height: auto;
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
        
        /* Product Grid Styles */
        .products-section {
            max-width: 1200px;
            margin: 1.5rem auto 4rem auto;
            padding: 0 1rem;
        }
        .products-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            color: #111827;
        }


        
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.25rem;
    }
    
    .product-card {
        position: relative;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.15);
    }
    
    .product-bg {
        position: absolute;
        width: 100%;
        height: 150px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
        transition: all 0.3s ease;
    }
    
    .product-card:hover .product-bg {
        height: 160px;
    }
    
    .product-image-container {
        position: relative;
        width: 100%;
        height: 50%; /* This makes it take exactly half the card */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2;
        overflow: hidden;
        background-color: white; /* Ensures consistent background */
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover; /* This makes the image cover the container */
        object-position: center; /* Centers the image */
        transition: all 0.4s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.02);
    }
    
    .product-content {
        position: relative;
        padding: 16px;
        text-align: center;
        z-index: 3;
        height: auto;
        display: flex;
        flex-direction: column;
    }
    
    .product-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 6px;
        text-decoration: none;
        display: block;
    }
    
    .product-title:hover {
        color: var(--primary);
    }
    
    .product-brand {
        font-size: 0.75rem;
        color: var(--primary);
        font-weight: 500;
        margin-bottom: 10px;
    }
    
    .product-description {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 15px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .price-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .current-price {
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary);
    }
    
    .old-price {
        font-size: 0.75rem;
        color: #94a3b8;
        text-decoration: line-through;
    }
    
    .product-actions {
        display: flex;
        justify-content: space-between;
        margin-top: auto;
        gap: 8px;
    }
    
    .add-to-cart {
        flex: 1;
        padding: 8px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 200;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        font-size: 0.75rem;
        height: 50px;
    }
    
    .add-to-cart:hover {
        background: var(--primary-dark);
    }
    
    .view-details {
        flex: 1;
        padding: 8px;
        background: white;
        color: var(--primary);
        border: 1px solid var(--primary);
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        font-size: 0.75rem;
    }
    
    .view-details:hover {
        background: #f1f5ff;
    }
    
    .wishlist {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        z-index: 4;
    }
    
    .wishlist:hover {
        color: var(--accent);
    }
    
    .wishlist svg {
        width: 16px;
        height: 16px;
        transition: all 0.2s ease;
    }
    
    .badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--accent);
        color: white;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 0.65rem;
        font-weight: 600;
        z-index: 4;
        box-shadow: 0 2px 8px rgba(244, 63, 94, 0.2);
    }
    
    .product-features {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    
    .feature {
        display: flex;
        align-items: center;
        font-size: 0.65rem;
        color: #64748b;
    }
    
    .feature svg {
        width: 12px;
        height: 12px;
        margin-right: 4px;
        color: var(--primary);
    }
    
    .no-products {
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
        padding: 1.5rem 0;
    }

    /* Mobile Bottom Navigation */
    .mobile-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        padding: 8px 0;
    }

    .mobile-nav-items {
        display: flex;
        justify-content: space-around;
        align-items: center;
    }

    .mobile-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #64748b;
        font-size: 0.7rem;
        padding: 5px;
    }

    .mobile-nav-item svg {
        width: 20px;
        height: 20px;
        margin-bottom: 4px;
    }

    .mobile-nav-item.active {
        color: var(--primary);
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .products-section {
            margin-bottom: 70px;
        }
        
        .product-card {
            border-radius: 12px;
        }
        
        .product-image-container {
            height: 140px;
        }
        
        .product-bg {
            height: 120px;
        }
        
        .product-card:hover .product-bg {
            height: 130px;
        }
        
        .product-content {
            padding: 12px;
        }
        
        .product-title {
            font-size: 0.9rem;
        }
        
        .product-brand {
            font-size: 0.7rem;
        }
        
        .product-description {
            font-size: 0.65rem;
            margin-bottom: 10px;
        }
        
        .price-container {
            margin-bottom: 12px;
        }
        
        .current-price {
            font-size: 0.9rem;
        }
        
        .old-price {
            font-size: 0.65rem;
        }
        
        .add-to-cart, .view-details {
            font-size: 0.65rem;
            padding: 6px;
        }
        
        .mobile-nav {
            display: block;
        }
    }

    @media (max-width: 480px) {
        .products-title {
            font-size: 1.25rem;
        }
        
        .product-image-container {
            height: 120px;
        }
        
        .product-content {
            padding: 10px;
        }
    }






     /* Eyewear Advice Section */
    .eyewear-advice {
        background-color: #f8fafc;
        padding: 4rem 1rem;
        margin-top: 3rem;
    }
    
    .advice-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
        color: #64748b;
        text-align: center;
        margin-bottom: 2.5rem;
    }
    
    .advice-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }
    
    .advice-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .advice-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }
    
    .advice-icon {
        font-size: 2rem;
        color: #2563eb;
        margin-bottom: 1rem;
    }
    
    .advice-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }
    
    .advice-card ul {
        list-style-type: none;
        padding-left: 0;
        margin-bottom: 1.5rem;
    }
    
    .advice-card ul li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }
    
    .advice-card ul li:before {
        content: "•";
        color: #2563eb;
        font-weight: bold;
        display: inline-block;
        width: 1em;
        margin-left: -1em;
    }
    
    .learn-more {
        color: #2563eb;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    
    .learn-more:hover {
        text-decoration: underline;
    }
    
    /* Testimonials Section */
    .testimonials {
        padding: 4rem 1rem;
        background-color: white;
    }
    
    .testimonial-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .testimonial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .testimonial-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    .rating {
        color: #f59e0b;
        margin-bottom: 1rem;
    }
    
    .testimonial-card p {
        font-style: italic;
        color: #4b5563;
        margin-bottom: 1.5rem;
    }
    
    .customer {
        display: flex;
        align-items: center;
    }
    
    .customer img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
    }
    
    .customer span {
        font-weight: 600;
        color: #111827;
    }
    
    /* Services Section */
    .services {
        background-color: #f8fafc;
        padding: 4rem 1rem;
    }
    
    .services-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .service-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
    }
    
    .service-icon {
        font-size: 2.5rem;
        color: #2563eb;
        margin-bottom: 1rem;
    }
    
    .service-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }
    
    .service-card p {
        color: #64748b;
        line-height: 1.5;
    }
    
    @media (max-width: 768px) {
        .section-title {
            font-size: 1.5rem;
        }
        
        .advice-grid,
        .testimonial-grid,
        .services-grid {
            grid-template-columns: 1fr;
        }
    }
    /* Eyewear service end */





    /* Commitment Section Styling */
.commitment-section {
    padding: 4rem 2rem;
    background-color: var(--secondary);
    text-align: center;
    margin: 2rem 0;
}

.commitment-small-title {
    color: var(--primary);
    font-size: 1.2rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.commitment-title {
    color: var(--dark);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.commitment-text {
    color: var(--dark);
    font-size: 1.1rem;
    max-width: 800px;
    margin: 0 auto 3rem;
    line-height: 1.6;
    opacity: 0.9;
}

.commitment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.commitment-card {
    background-color: var(--light);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.commitment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.commitment-card-header {
    color: var(--primary-dark);
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.commitment-card p {
    color: var(--dark);
    line-height: 1.6;
    opacity: 0.8;
}

.commitment-card i {
    font-size: 1.5rem;
    color: var(--primary);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .commitment-section {
        padding: 3rem 1.5rem;
    }
    
    .commitment-title {
        font-size: 2rem;
    }
    
    .commitment-grid {
        grid-template-columns: 1fr;
    }
}
    /* Commitment Section Styling */
    </style>
</head>
<body>

    <main>
        <!-- Hero Carousel -->
        <section class="splide" aria-label="Eyeonic Hero Carousel">
         
            
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">                                         
                        <img alt="Illustration of a woman wearing glasses with a brown background" class="hero-image"  src="img/eyebg2b.jpg"/>
                        <div class="hero-content">
                            <h1 class="hero-title">See the world <span>in a new light</span></h1>
                            <p class="hero-subtitle">Discover our curated collection of spectacles, designed for clarity, comfort, and style. Find your perfect pair today.</p>
                            <a href="products.php"><button class="btn-shop" type="button">Shop Now</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">                     
                        <img alt="Collection of modern eyeglasses" class="hero-image" src="img/eyebg3.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Premium Eyewear <span>for Everyone</span></h1>
                            <p class="hero-subtitle">From classic designs to modern trends, we have frames to suit every face and style.</p>
                             <a href="products.php"><button class="btn-shop" type="button">Browse Collection</button></a>
                        </div>
                    </li>
                       <li class="splide__slide">                     
                        <img alt="Happy customer with new glasses" class="hero-image"  src="img/johncena.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Can You <span>See Me?</span></h1>
                            <p class="hero-subtitle">Limited time offer on selected frames. Don't miss out on these amazing deals!</p>
                             <a href="products.php"><button class="btn-shop" type="button">Lets see how you can see</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">                     
                        <img alt="Happy customer with new glasses" class="hero-image"  src="img/twoppl2.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Summer Sale <span>Up to 50% Off</span></h1>
                            <p class="hero-subtitle">Limited time offer on selected frames. Don't miss out on these amazing deals!</p>
                             <a href="products.php"><button class="btn-shop" type="button">View Lasting offers</button></a>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="products-section">
            <h2 class="products-title">Featured Products</h2>
            <?php if (empty($products)): ?>
                <p class="no-products">No featured products found.</p>
            <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): 
                    $imagePath = getValidImagePath($product['image_url'] ?? $product['image_path']);
                    $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['price'];
                    $discountPercentage = $hasDiscount ? calculateDiscountPercentage($product['price'], $product['discount_price']) : 0;
                ?>
                    <div class="product-card">
                        <div class="product-bg"></div>
                        
                        <?php if ($hasDiscount && $discountPercentage > 0): ?>
                            <span class="badge"><?php echo $discountPercentage; ?>% OFF</span>
                        <?php endif; ?>
                        
                        <div class="wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        
                        <div class="product-image-container">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        </div>
                        
                        <div class="product-content">
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo htmlspecialchars($product['name']); ?></a>
                            <p class="product-brand">by <?php echo htmlspecialchars($product['brand']); ?></p>
                            
                            <div class="product-features">
                                <div class="feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="5"></circle>
                                    </svg>
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                                <div class="feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 18a5 5 0 0 0-10 0"></path>
                                    </svg>
                                    <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="price-container">
                                <span class="current-price">KES <?php echo number_format($hasDiscount ? $product['discount_price'] : $product['price'], 2); ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span class="old-price">KES <?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <a href="cart.php?id=<?php echo $product['id']; ?>" class="add-to-cart">                                  
                                    Add to Cart
                                </a>
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="view-details">                                  
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>





        <!-- Eyewear Advice Section -->
<section class="eyewear-advice">
    <div class="advice-container">
        <h2 class="section-title">Eyewear Buying Guide</h2>
        <p class="section-subtitle">Expert advice to help you find your perfect pair</p>
        
        <div class="advice-grid">
            <!-- Frame Selection Advice -->
            <div class="advice-card">
                <div class="advice-icon">
                    <i class="fas fa-glasses"></i>
                </div>
                <h3>Choosing the Right Frames</h3>
                <ul>
                    <li>Match frame shape to your face shape</li>
                    <li>Consider your skin tone for color selection</li>
                    <li>Ensure proper bridge fit for comfort</li>
                    <li>Think about your lifestyle needs</li>
                </ul>
                <a href="#" class="learn-more">Read More</a>
            </div>
            
            <!-- Lens Selection Advice -->
            <div class="advice-card">
                <div class="advice-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Lens Options Explained</h3>
                <ul>
                    <li>Single vision vs. progressive lenses</li>
                    <li>Anti-reflective coating benefits</li>
                    <li>Blue light blocking technology</li>
                    <li>Photochromic (transition) lenses</li>
                </ul>
                <a href="#" class="learn-more">Read More</a>
            </div>
            
            <!-- Eye Health Tips -->
            <div class="advice-card">
                <div class="advice-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>Eye Health Tips</h3>
                <ul>
                    <li>Get regular eye exams</li>
                    <li>Follow the 20-20-20 rule for digital strain</li>
                    <li>Wear UV-protective sunglasses outdoors</li>
                    <li>Keep lenses clean for optimal vision</li>
                </ul>
                <a href="#" class="learn-more">Read More</a>
            </div>
        </div>
    </div>
</section>

<!-- Customer Testimonials -->
<section class="testimonials">
    <div class="testimonial-container">
        <h2 class="section-title">What Our Customers Say</h2>
        
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"The most comfortable glasses I've ever worn. Perfect for my active lifestyle!"</p>
                <div class="customer">
                    <img src="img/customer1.jpg" alt="Sarah J.">
                    <span>Sarah J.</span>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Great selection of frames and the blue light lenses have reduced my eye strain significantly."</p>
                <div class="customer">
                    <img src="img/customer2.jpg" alt="Michael T.">
                    <span>Michael T.</span>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p>"Fast shipping and excellent customer service when I needed an adjustment."</p>
                <div class="customer">
                    <img src="img/customer3.jpg" alt="Priya K.">
                    <span>Priya K.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Eye Care Services -->
<section class="services">
    <div class="services-container">
        <h2 class="section-title">Our Eye Care Services</h2>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-eye-dropper"></i>
                </div>
                <h3>Prescription Lenses</h3>
                <p>Precision-crafted lenses tailored to your vision needs with the latest optical technology.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-sun"></i>
                </div>
                <h3>Sunglasses</h3>
                <p>100% UV protection with prescription options available in stylish designer frames.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-desktop"></i>
                </div>
                <h3>Computer Glasses</h3>
                <p>Specialized lenses to reduce digital eye strain and block harmful blue light.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-child"></i>
                </div>
                <h3>Kids' Eyewear</h3>
                <p>Durable, comfortable frames designed for active children with impact-resistant lenses.</p>
            </div>
        </div>
    </div>
</section>
    </main>

            <section class="commitment-section">
            <h3 class="commitment-small-title">Why Choose Us?</h3>
            <h2 class="commitment-title">Our Commitment to You</h2>
            <p class="commitment-text">At Eyeonic, we're dedicated to providing exceptional quality and service. From our carefully selected materials to our customer-focused approach, we ensure your satisfaction every step of the way.</p>
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

    <!-- Splide Carousel JS -->
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize hero carousel
            new Splide('.splide', {
                type: 'loop',
                autoplay: true,
                interval: 5000,
                pauseOnHover: false,
                arrows: false,
                pagination: false,
                speed: 1000,
            }).mount();
        });
    </script>

</body>
</html>

<?php include 'footer.php'; ?>