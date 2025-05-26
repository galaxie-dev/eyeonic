<?php
include 'header.php';
require_once '../config/database.php';

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
        margin: 2.5rem auto 0 auto;
        padding: 0 1rem;
        font-family: 'Poppins', sans-serif;
    }
    
    .products-title {
        font-weight: 600;
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
        color: var(--dark);
    }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .product-card {
        position: relative;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(37, 99, 235, 0.2);
    }
    
    .product-bg {
        position: absolute;
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
        transition: all 0.4s ease;
    }
    
    .product-card:hover .product-bg {
        height: 220px;
        clip-path: polygon(0 0, 100% 0, 100% 80%, 0 100%);
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
        transform: scale(1.03);
    }
    
    .product-content {
        position: relative;
        padding: 20px;
        text-align: center;
        z-index: 3;
        height: 240px;
        display: flex;
        flex-direction: column;
    }
    
    .product-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        text-decoration: none;
        display: block;
    }
    
    .product-title:hover {
        color: var(--primary);
    }
    
    .product-brand {
        font-size: 0.875rem;
        color: var(--primary);
        font-weight: 500;
        margin-bottom: 15px;
    }
    
    .product-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .price-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .current-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }
    
    .old-price {
        font-size: 0.875rem;
        color: #94a3b8;
        text-decoration: line-through;
    }
    
    .product-actions {
        display: flex;
        justify-content: space-between;
        margin-top: auto;
        gap: 10px;
    }
    
    .add-to-cart {
        flex: 1;
        padding: 12px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .add-to-cart:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .view-details {
        flex: 1;
        padding: 12px;
        background: white;
        color: var(--primary);
        border: 1px solid var(--primary);
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .view-details:hover {
        background: #f1f5ff;
        transform: translateY(-2px);
    }
    
    .wishlist {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        z-index: 4;
    }
    
    .wishlist:hover {
        color: var(--accent);
    }
    
    .wishlist svg {
        transition: all 0.3s ease;
    }
    
    .wishlist:hover svg {
        transform: scale(1.1);
    }
    
    .badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--accent);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 4;
        box-shadow: 0 4px 10px rgba(244, 63, 94, 0.3);
    }
    
    .product-features {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .feature {
        display: flex;
        align-items: center;
        font-size: 0.75rem;
        color: #64748b;
    }
    
    .feature svg {
        margin-right: 5px;
        color: var(--primary);
    }
    
    .no-products {
        text-align: center;
        color: #64748b;
        font-size: 1rem;
        padding: 2rem 0;
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="5"></circle>
                                    </svg>
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                                <div class="feature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                <a href="#" class="add-to-cart">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    Add to Cart
                                </a>
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="view-details">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
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

<?php include 'footer.php'; ?>