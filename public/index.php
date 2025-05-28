<?php
// include 'header.php';
require_once '../config/database.php';

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
            background: linear-gradient(to right, rgba(0,0,0,0.6), transparent);
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
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
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
            color: #111827;
            margin-bottom: 6px;
            text-decoration: none;
            display: block;
        }
        .product-title:hover {
            color: #2563eb;
        }
        .product-brand {
            font-size: 0.75rem;
            color: #2563eb;
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
            color: #2563eb;
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
            background: #2563eb;
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
            background: #1d4ed8;
        }
        .view-details {
            flex: 1;
            padding: 8px;
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
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
            color: #f43f5e;
        }
        .badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #f43f5e;
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
        .no-products {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            padding: 1.5rem 0;
        }
        
        /* Commitment Section */
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
    </style>
</head>
<body>

    <main>
        <!-- Hero Carousel -->
        <section class="splide" aria-label="Eyeonic Hero Carousel">
            
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <?php include 'header.php'; ?>                    
                        <img alt="Illustration of a woman wearing glasses with a brown background" class="hero-image"  src="img/eyebg2b.jpg"/>
                        <div class="hero-content">
                            <h1 class="hero-title">See the world <span>in a new light</span></h1>
                            <p class="hero-subtitle">Discover our curated collection of spectacles, designed for clarity, comfort, and style. Find your perfect pair today.</p>
                            <a href="products.php"><button class="btn-shop" type="button">Shop Now</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <?php include 'header.php'; ?> 
                        <img alt="Collection of modern eyeglasses" class="hero-image" src="img/eyebg3.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Premium Eyewear <span>for Everyone</span></h1>
                            <p class="hero-subtitle">From classic designs to modern trends, we have frames to suit every face and style.</p>
                             <a href="products.php"><button class="btn-shop" type="button">Browse Collection</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">
                        <?php include 'header.php'; ?> 
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
    </main>

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