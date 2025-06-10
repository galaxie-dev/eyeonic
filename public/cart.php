<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

include 'header.php';
require_once '../config/database.php';

// Handle "Add to Cart" action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

    // Verify product exists
    $stmt = $pdo->prepare("SELECT id, price, discount_price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity; // Update quantity if already in cart
        } else {
            $_SESSION['cart'][$productId] = $quantity; // Add new item to cart
        }
    }

    // Optional: Add a success message (can be displayed in the UI)
    $_SESSION['cart_message'] = "Product added to cart successfully!";
}

// Handle cart updates (existing code)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            $quantity = max(1, (int)$quantity); // Ensure quantity is at least 1
            $_SESSION['cart'][$productId] = $quantity;
        }
    } elseif (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        $productId = $_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
    }
}

$cart = $_SESSION['cart'] ?? [];

// Fetch categories for navigation
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic - Your Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #e0f2fe;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            padding-bottom: 70px; /* Space for mobile nav */
        }
        
        .cart-section {
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 0 1rem;
        }
        
        .cart-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .cart-empty {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        }
        
        /* Desktop table */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            overflow: hidden;
            display: table;
        }
        
        /* Mobile table */
        @media (max-width: 768px) {
            .cart-table {
                display: block;
            }
            
            .cart-table thead {
                display: none;
            }
            
            .cart-table tbody, 
            .cart-table tr, 
            .cart-table td {
                display: block;
                width: 100%;
            }
            
            .cart-table tr {
                margin-bottom: 1rem;
                padding: 1rem;
                background: white;
                border-radius: 0.375rem;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            }
            
            .cart-table td {
                padding: 0.5rem 0;
                border: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .cart-table td:before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--dark);
                margin-right: 1rem;
            }
        }
        
        .cart-table th {
            text-align: left;
            padding: 1rem;
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .cart-table td {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 0.25rem;
        }
        
        .cart-item-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark);
        }
        
        .cart-item-price {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .quantity-input {
            width: 60px;
            padding: 0.375rem;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
            text-align: center;
        }
        
        .remove-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .remove-btn:hover {
            text-decoration: underline;
        }
        
        .cart-total {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        .total-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            width: 100%;
            max-width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .total-amount {
            font-weight: 600;
            color: var(--dark);
        }
        
        .grand-total {
            font-size: 1.125rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }
        
        .btn-checkout {
            display: block;
            width: 100%;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 1rem;
            text-align: center;
        }
        
        .btn-checkout:hover {
            background-color: var(--primary-dark);
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .btn-update {
            background-color: #f3f4f6;
            color: var(--dark);
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-update:hover {
            background-color: #e5e7eb;
        }
        
        .btn-continue {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
        }
        
        .btn-continue:hover {
            text-decoration: underline;
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
        
        @media (max-width: 768px) {
            .mobile-nav {
                display: block;
            }
            
            .cart-section {
                margin: 1.5rem auto;
            }
            
            .cart-total {
                justify-content: center;
            }
            
            .total-box {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <main>
        <section class="cart-section">
            <h2 class="cart-title">Your Shopping Cart</h2>
            <?php if (isset($_SESSION['cart_message'])): ?>
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['cart_message']) ?>
                </div>
                <?php unset($_SESSION['cart_message']); ?>
            <?php endif; ?>
            
            <?php if (empty($cart)): ?>
                <div class="cart-empty">
                    <p>Your cart is currently empty.</p>
                    <a href="products.php" class="btn-continue" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($cart as $id => $qty):
                                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                                $stmt->execute([$id]);
                                $product = $stmt->fetch();
                                $subtotal = $product['price'] * $qty;
                                $total += $subtotal;
                                
                                // FIXED: Using image_path instead of image
                                $imagePath = !empty($product['image_path']) ? '../' . $product['image_path'] : '../assets/no-image.png';
                            ?>
                                <tr>
                                    <!-- Product Column -->
                                    <td data-label="Product">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="cart-item-img">
                                            <div>
                                                <div class="cart-item-name"><?= htmlspecialchars($product['name']) ?></div>
                                                <div class="cart-item-price">KES <?= number_format($product['price'], 2) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Price Column -->
                                    <td data-label="Price">KES <?= number_format($product['price'], 2) ?></td>
                                    
                                    <!-- Quantity Column -->
                                    <td data-label="Quantity">
                                        <input type="number" name="quantity[<?= $id ?>]" value="<?= $qty ?>" min="1" class="quantity-input">
                                    </td>
                                    
                                    <!-- Subtotal Column -->
                                    <td data-label="Subtotal">KES <?= number_format($subtotal, 2) ?></td>
                                    
                                    <!-- Remove Column -->
                                    <td data-label="Action">
                                        <button type="submit" name="remove_item" value="1" class="remove-btn">
                                            <input type="hidden" name="product_id" value="<?= $id ?>">
                                            <i class="fas fa-trash"></i> <span class="hidden md:inline">Remove</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn-continue">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="btn-update">
                            <i class="fas fa-sync-alt mr-2"></i> Update Cart
                        </button>
                    </div>
                </form>
                
                <div class="cart-total">
                    <div class="total-box">
                        <div class="total-row">
                            <span class="total-label">Subtotal</span>
                            <span class="total-amount">KES <?= number_format($total, 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">Shipping</span>
                            <span class="total-amount">Calculated at checkout</span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="total-label">Total</span>
                            <span class="total-amount">KES <?= number_format($total, 2) ?></span>
                        </div>
                        <a href="place_order.php" class="btn-checkout">Proceed Order</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="index.php" class="mobile-nav-item">
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
            <a href="cart.php" class="mobile-nav-item active">
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
    
    <?php include 'footer.php'; ?>

    <script>
        // Show mobile nav on mobile devices
        if (window.innerWidth <= 768) {
            document.querySelector('.mobile-nav').style.display = 'block';
            
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
        }







           
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
            <?php if(!$isLoggedIn): ?>
                showNotification('Please login to add items to wishlist');
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
            <?php else: ?>
                const heart = element.querySelector('svg');
                const isInWishlist = heart.getAttribute('fill') !== 'none';
                
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
                        }
                        updateWishlistCount();
                    } else {
                        showNotification(data.message || 'Error updating wishlist');
                    }
                });
            <?php endif; ?>
        }
    
    </script>
</body>
<?php include 'mobile-menu.php'; ?>
</html>