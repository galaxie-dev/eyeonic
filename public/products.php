<?php
include 'header.php';
require_once '../config/database.php';
session_start();

$categoryId = $_GET['category'] ?? null;

// Fetch products with category name
if ($categoryId) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ?
    ");
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
    ");
    $stmt->execute();
}
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
    // Check if file exists and is readable
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

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
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
    
    .products-section {
        max-width: 1200px;
        margin: 1.5rem auto 4rem auto;
        padding: 0 1rem;
        font-family: 'Poppins', sans-serif;
    }
    
    .products-title {
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
        color: var(--dark);
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
</style>

<main>
    <section class="products-section">
        <h2 class="products-title"><?php echo $categoryId ? htmlspecialchars($categories[array_search($categoryId, array_column($categories, 'id'))]['name'] ?? 'Products') : 'All Products'; ?></h2>
        <?php if (empty($products)): ?>
            <p class="no-products">No products found.</p>
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
</main>

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

<script>
    // Hide/show mobile nav on scroll
    let lastScroll = 0;
    const mobileNav = document.querySelector('.mobile-nav');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            mobileNav.style.bottom = '0';
            return;
        }
        
        if (currentScroll > lastScroll) {
            // Scrolling down
            mobileNav.style.bottom = '-70px';
        } else {
            // Scrolling up
            mobileNav.style.bottom = '0';
        }
        
        lastScroll = currentScroll;
    });
    
    // Show nav briefly when page loads
    setTimeout(() => {
        mobileNav.style.transition = 'bottom 0.3s ease';
    }, 1000);
</script>

<?php include 'footer.php'; ?>