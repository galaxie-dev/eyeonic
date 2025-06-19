<?php
// include 'header.php';
require_once '../config/database.php';
 include 'header.php'; 

$categoryId = $_GET['category'] ?? null;


// Initialize wishlistItems array
$wishlistItems = [];

// Fetch wishlist items if user is logged in
if (isset($_SESSION['user_id'])) {
    $wishlistStmt = $pdo->prepare("
        SELECT product_id 
        FROM wishlists 
        WHERE user_id = ?
    ");
    $wishlistStmt->execute([$_SESSION['user_id']]);
    $wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

// Fetch only featured products
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_featured = TRUE
");
$stmt->execute();
$products = $stmt->fetchAll();

// Initialize wishlistItems array
$wishlistItems = [];

// Fetch wishlist items if user is logged in
if (isset($_SESSION['user_id'])) {
    $wishlistStmt = $pdo->prepare("
        SELECT product_id 
        FROM wishlists 
        WHERE user_id = ?
    ");
    $wishlistStmt->execute([$_SESSION['user_id']]);
    $wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

$categoryId = $_GET['category'] ?? null;




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
        <link href="style.css" rel="stylesheet">
</head>
<body>

      <!-- Notification element -->
    <div id="notification" class="notification"></div>

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

        <?php include 'categories.php'; ?>
      
        <!-- Featured Products Section -->
        <section class="products-section">
            <?php include 'search-bar.php'; ?>
            <h2 class="products-title">Featured Products</h2>
            <?php if (empty($products)): ?>
                <p class="no-products">No featured products found.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): 
                        $imagePath = getValidImagePath($product['image_url'] ?? $product['image_path']);
                        $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['price'];
                        $discountPercentage = $hasDiscount ? calculateDiscountPercentage($product['price'], $product['discount_price']) : 0;
                        $isInWishlist = in_array($product['id'], $wishlistItems);
                    ?>
                        <div class="product-card">
                            <div class="product-bg"></div>
                            
                            <?php if ($hasDiscount && $discountPercentage > 0): ?>
                                <span class="badge"><?php echo $discountPercentage; ?>% OFF</span>
                            <?php endif; ?>
                            
                            <div class="wishlist" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $isInWishlist ? 'red' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                        <?php echo htmlspecialchars(substr($product['category_name'], 0, 10 ?? 'Uncategorized')); ?>

                                       
                                    </div>
                                    <div class="feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date(' M j, Y', strtotime($product[ 'created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="price-container">
                                    <span class="current-price">KES <?php echo number_format($hasDiscount ? $product['discount_price'] : $product['price'], 2); ?></span>
                                    <?php if ($hasDiscount): ?>
                                        <span class="old-price">KES <?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="javascript:void(0);" onclick="addToCart(<?php echo $product['id']; ?>)" class="add-to-cart">
                                        <span class="text">Add to Cart</span>
                                    </a>
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="view-details">
                                        <span class="text">View Details</span>
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
            <h2 class="section-title">Guide Your Eye</h2>
            <p class="section-subtitle">Expert advice to help you find your perfect pair</p>
           
        
        
        <div class="advice-grid">
            <!-- Frame Selection Advice -->
            <div class="advice-card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="4"></circle>
                        <line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line>
                        <line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line>
                        <line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line>
                        <line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line>
                    </svg>
                </div>
                <h3>Choosing the Right Frames</h3>
                <ul>
                    <li><span class="bullet-icon">→</span> Match frame shape to your face shape</li>
                    <li><span class="bullet-icon">→</span> Consider your skin tone for color selection</li>
                    <li><span class="bullet-icon">→</span> Ensure proper bridge fit for comfort</li>
                    <li><span class="bullet-icon">→</span> Think about your lifestyle needs</li>
                </ul>
                <a href="products.php" class="learn-more">Discover your frame style <span class="arrow">→</span></a>
            </div>
            
            <!-- Lens Selection Advice -->
            <div class="advice-card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                        <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                        <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                        <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                    </svg>
                </div>
                <h3>Lens Options Explained</h3>
                <ul>
                    <li><span class="bullet-icon">→</span> Single vision vs. progressive lenses</li>
                    <li><span class="bullet-icon">→</span> Anti-reflective coating benefits</li>
                    <li><span class="bullet-icon">→</span> Blue light blocking technology</li>
                    <li><span class="bullet-icon">→</span> Photochromic (transition) lenses</li>
                </ul>
                <a href="products.php" class="learn-more">Explore lens technology <span class="arrow">→</span></a>
            </div>
            
            <!-- Eye Health Tips -->
            <div class="advice-card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </div>
                <h3>Eye Health Tips</h3>
                <ul>
                    <li><span class="bullet-icon">→</span> Get regular eye exams</li>
                    <li><span class="bullet-icon">→</span> Follow the 20-20-20 rule for digital strain</li>
                    <li><span class="bullet-icon">→</span> Wear UV-protective sunglasses outdoors</li>
                    <li><span class="bullet-icon">→</span> Keep lenses clean for optimal vision</li>
                </ul>
                <a href="products.php" class="learn-more">Learn eye care essentials <span class="arrow">→</span></a>
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
                    <img src="img/catt7.png" alt="Sarah">
                    <span>Sarah</span>
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
                    <img src="img/catt6.png" alt="Michael">
                    <span>Michael</span>
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
                    <img src="img/catt5.png" alt="Monique">
                    <span>Monique</span>
                </div>
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
        <a href="index.php" class="mobile-nav-item active">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <a href="products.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Shop
        </a>
        <a href="cart.php" class="mobile-nav-item">
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

    <script>
        //initialise splide caurosell
        document.addEventListener('DOMContentLoaded', function() {
            new Splide('.splide', {
                type: 'loop',
                autoplay: true,
                interval: 5000,
                pauseOnHover: false,
                arrows: true,
                pagination: true,
                breakpoints: {
                    768: {
                        arrows: true,
                        pagination: true,
                        height: '50vh'
                    }
                }
            }).mount();
        });
</script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
        // Update cart and wishlist counts on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            updateWishlistCount();
        });

        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.style.display = 'block';
            notification.style.animation = 'slideIn 0.5s, fadeOut 0.5s 2.5s';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = data.count;
                        el.style.display = data.count > 0 ? 'flex' : 'none';
                    });
                });
        }

        function updateWishlistCount() {
            fetch('get_wishlist_count.php')
                .then(response => response.json())
                .then(data => {
                    if(data.count !== undefined) {
                        document.querySelectorAll('.wishlist-count').forEach(el => {
                            el.textContent = data.count;
                            el.style.display = data.count > 0 ? 'flex' : 'none';
                        });
                    }
                });
        }

        function addToCart(productId) {
            <?php if(!$isLoggedIn): ?>
                showNotification('Please login to add items to cart');
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php else: ?>
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showNotification('Product added to cart!');
                        updateCartCount();
                    } else {
                        showNotification(data.message || 'Error adding to cart');
                    }
                });
            <?php endif; ?>
        }


        function toggleWishlist(productId, element) {
    <?php if(!isset($_SESSION['user_id'])): ?>
        showNotification('Please login to add items to wishlist');
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
        return;
    <?php endif; ?>
    
    const heart = element.querySelector('svg');
    
    fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if(data.action === 'added') {
                heart.setAttribute('fill', 'red');
                showNotification('Added to wishlist!');
            } else {
                heart.setAttribute('fill', 'none');
                showNotification('Removed from wishlist');
                // Special handling for wishlist page
                if (window.location.pathname.includes('wishlist.php')) {
                    element.closest('.product-card').remove();
                    // Show empty message if no items left
                    if (document.querySelectorAll('.product-card').length === 0) {
                        document.querySelector('.product-grid').innerHTML = `
                            <div class="wishlist-empty">
                                <p>Your wishlist is currently empty.</p>
                                <a href="products.php" class="btn-continue" style="margin-top: 1rem;">
                                    <i class="fas fa-arrow-left mr-2"></i> Browse Products
                                </a>
                            </div>
                        `;
                    }
                }
            }
            updateWishlistCount();
        } else {
            showNotification(data.message || 'Error updating wishlist');
        }
    })
    .catch(error => {
        showNotification('Network error. Please try again.');
        console.error('Error:', error);
    });
}
    </script>

</body>
</html>
<?php include 'mobile-menu.php'; ?>
<?php include 'footer.php'; ?>