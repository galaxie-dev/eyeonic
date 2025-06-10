<?php
session_start();
require_once '../config/database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN wishlists w ON p.id = w.product_id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();

// Helper functions
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
    <title>Eyeonic - My Wishlist</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        /* Add your existing styles here */
        .wishlist-empty {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <main>
        <section class="products-section">
            <h2 class="products-title">My Wishlist</h2>
            <?php if (empty($wishlistItems)): ?>
                <div class="wishlist-empty">
                    <p>Your wishlist is currently empty.</p>
                    <a href="products.php" class="btn-continue" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left mr-2"></i> Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($wishlistItems as $product): 
                        $imagePath = getValidImagePath($product['image_url'] ?? $product['image_path']);
                        $hasDiscount = $product['discount_price'] && $product['discount_price'] < $product['price'];
                        $discountPercentage = $hasDiscount ? calculateDiscountPercentage($product['price'], $product['discount_price']) : 0;
                    ?>
                        <div class="product-card">
                            <div class="product-bg"></div>
                            
                            <?php if ($hasDiscount && $discountPercentage > 0): ?>
                                <span class="badge"><?php echo $discountPercentage; ?>% OFF</span>
                            <?php endif; ?>
                            
                            <div class="wishlist" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="red" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                            
                            <div class="product-image-container">
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            </div>
                            
                            <div class="product-content">
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo htmlspecialchars($product['name']); ?></a>
                                <p class="product-brand">by <?php echo htmlspecialchars($product['brand']); ?></p>
                                
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

    <script>
        // Reuse the same functions from index.php
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function toggleWishlist(productId, element) {
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
                        // Remove the card from view if on wishlist page
                        if (window.location.pathname.includes('wishlist.php')) {
                            element.closest('.product-card').remove();
                            // If no items left, show empty message
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
            });
        }

        function addToCart(productId) {
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
                    document.querySelectorAll('.wishlist-count').forEach(el => {
                        el.textContent = data.count;
                        el.style.display = data.count > 0 ? 'flex' : 'none';
                    });
                });
        }
    </script>
</body>
<?php include 'mobile-menu.php'; ?>
</html>

<?php include 'footer.php'; ?>