<?php
// include 'header.php';
require_once 'config/database.php';
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
    $basePath = '';
    $defaultImage = 'assets/no-image.png';
    
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
    <link href="public/style.css" rel="stylesheet">
</head>
<body>

    <main>
        <!-- Hero Carousel -->
        <section class="splide" aria-label="Eyeonic Hero Carousel">
         
            
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">                                         
                        <img alt="Illustration of a woman wearing glasses with a brown background" class="hero-image"  src="public/img/eyebg2b.jpg"/>
                        <div class="hero-content">
                            <h1 class="hero-title">See the world <span>in a new light</span></h1>
                            <p class="hero-subtitle">Discover our curated collection of spectacles, designed for clarity, comfort, and style. Find your perfect pair today.</p>
                            <a href="public/products.php"><button class="btn-shop" type="button">Shop Now</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">                     
                        <img alt="Collection of modern eyeglasses" class="hero-image" src="public/img/eyebg3.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Premium Eyewear <span>for Everyone</span></h1>
                            <p class="hero-subtitle">From classic designs to modern trends, we have frames to suit every face and style.</p>
                             <a href="public/products.php"><button class="btn-shop" type="button">Browse Collection</button></a>
                        </div>
                    </li>
                       <li class="splide__slide">                     
                        <img alt="Happy customer with new glasses" class="hero-image"  src="public/img/johncena.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Can You <span>See Me?</span></h1>
                            <p class="hero-subtitle">Limited time offer on selected frames. Don't miss out on these amazing deals!</p>
                             <a href="public/products.php"><button class="btn-shop" type="button">Lets see how you can see</button></a>
                        </div>
                    </li>
                    <li class="splide__slide">                     
                        <img alt="Happy customer with new glasses" class="hero-image"  src="public/img/twoppl2.jpg" />
                        <div class="hero-content">
                            <h1 class="hero-title">Summer Sale <span>Up to 50% Off</span></h1>
                            <p class="hero-subtitle">Limited time offer on selected frames. Don't miss out on these amazing deals!</p>
                             <a href="public/products.php"><button class="btn-shop" type="button">View Lasting offers</button></a>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="products-section">
            <?php include 'public/search-bar.php'; ?>
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
                            <a href="public/cart.php?id=<?php echo $product['id']; ?>" class="add-to-cart">
                                <span class="text">Add to Cart</span>
                            </a>
                            <a href="public/product_details.php?id=<?php echo $product['id']; ?>" class="view-details">
                                <span class="text">Details</span>
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


        <!-- Mobile Bottom Navigation -->
<div class="mobile-nav">
    <div class="mobile-nav-items">
        <a href="public/index.php" class="mobile-nav-item active">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <a href="public/products.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Shop
        </a>
        <a href="public/cart.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            Cart
        </a>
        <a href="dashboard.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Account
        </a>
    </div>
</div>

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
<?php include 'public/mobile-menu.php'; ?>

<?php include 'public/footer.php'; ?>