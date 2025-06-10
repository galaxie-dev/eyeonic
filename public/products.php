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




// Handle search query
$search = $_GET['q'] ?? '';

if ($search) {
    // Search for products that match name, description, or category name
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE ? 
           OR p.description LIKE ?
           OR c.name LIKE ?
        ORDER BY p.created_at DESC
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
} else {
    // Default query when no search term
    $stmt = $pdo->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
    ");
}

$products = $stmt->fetchAll();

// Display results
if (empty($products)) {
    echo '<p class="empty-message">No products found' . ($search ? ' matching "' . htmlspecialchars($search) . '"' : '') . '.</p>';
} else {
    // Display your products table here
    // ...
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

<main>
    <section class="products-section">
             <?php include 'search-bar.php'; ?>
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
                                <span class="text">Add to Cart</span>
                            </a>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="view-details">
                                <span class="text">Details</span>
                            </a>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

</div>
</body>
</html>
<?php include 'mobile-menu.php'; ?>

<?php include 'public/footer.php'; ?>