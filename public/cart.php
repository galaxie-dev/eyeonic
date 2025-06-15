<?php

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
    --primary: #2a3f54;
    --primary-light: #3a516e;
    --primary-dark: #1d2b3e;
    --accent: #d4af37; /* Gold accent color for premium feel */
    --secondary: #f5f7fa;
    --dark: #1e293b;
    --light: #ffffff;
    --gray-light: #f3f4f6;
    --gray-medium: #e5e7eb;
    --gray-dark: #6b7280;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--secondary);
    color: var(--dark);
    line-height: 1.6;
    padding-bottom: 80px; /* Space for mobile nav */
}

/* Main Container */
.cart-section {
    max-width: 1200px;
    margin: 3rem auto;
    padding: 0 2rem;
}

/* Typography */
.cart-title {
    font-weight: 600;
    font-size: 2rem;
    margin-bottom: 2rem;
    color: var(--primary);
    position: relative;
    padding-bottom: 0.5rem;
}

.cart-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--accent);
}

/* Empty Cart */
.cart-empty {
    background: var(--light);
    padding: 3rem 2rem;
    text-align: center;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    margin: 2rem 0;
}

.cart-empty p {
    font-size: 1rem;
    color: var(--gray-dark);
    margin-bottom: 1.5rem;
}

/* Cart Table */
.cart-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: var(--light);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.cart-table thead {
    background: var(--primary);
}

.cart-table th {
    padding: 1.25rem 1.5rem;
    color: var(--light);
    font-weight: 500;
    text-align: left;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.cart-table td {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--gray-medium);
    vertical-align: middle;
    font-size: 0.9375rem;
}

.cart-table tr:last-child td {
    border-bottom: 1px solid var(--gray-medium);
}

/* Cart Items */
.cart-item-img {
    width: 90px;
    height: 90px;
    object-fit: contain;
    border-radius: 4px;
    border: 1px solid var(--gray-medium);
    padding: 5px;
    background: var(--light);
}

.cart-item-name {
    font-weight: 600;
    color: var(--primary-dark);
    margin-bottom: 0.25rem;
}

.cart-item-price {
    font-size: 0.875rem;
    color: var(--gray-dark);
}

/* Quantity Input */
.quantity-input {
    width: 70px;
    padding: 0.5rem;
    border: 1px solid var(--gray-medium);
    border-radius: 4px;
    text-align: center;
    font-size: 0.9375rem;
    transition: border-color 0.3s;
}

.quantity-input:focus {
    outline: none;
    border-color: var(--primary);
}

/* Buttons */
.remove-btn {
    color: #dc2626;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    transition: color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.remove-btn:hover {
    color: #b91c1c;
}

.btn-update {
    background-color: var(--primary);
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-update:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
}

.btn-continue {
    color: var(--primary);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: 1px solid var(--primary);
    border-radius: 4px;
    transition: all 0.3s;
}

.btn-continue:hover {
    background-color: var(--primary);
    color: white;
    text-decoration: none;
}

/* Cart Total */
.cart-total {
    display: flex;
    justify-content: flex-end;
    margin-top: 2.5rem;
}

.total-box {
    background: var(--light);
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.total-label {
    font-size: 0.9375rem;
    color: var(--gray-dark);
}

.total-amount {
    font-weight: 600;
    color: var(--dark);
}

.grand-total {
    font-size: 1.125rem;
    border-top: 1px solid var(--gray-medium);
    padding-top: 1rem;
    margin-top: 1rem;
}

.grand-total .total-label {
    font-size: 1.125rem;
    color: var(--primary);
}

.grand-total .total-amount {
    font-size: 1.25rem;
    color: var(--primary);
}

.btn-checkout {
    display: block;
    width: 100%;
    background-color: var(--accent);
    color: var(--primary-dark);
    font-weight: 600;
    padding: 1rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 1.5rem;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-checkout:hover {
    background-color: #c9a227;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
}

.cart-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Mobile Styles */
@media (max-width: 768px) {
    .cart-section {
        margin: 1.5rem auto;
        padding: 0 1rem;
    }
    
    .cart-title {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
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
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: var(--light);
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .cart-table td {
        padding: 0.75rem 0;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cart-table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--primary);
        margin-right: 1rem;
    }
    
    .cart-item-img {
        width: 70px;
        height: 70px;
    }
    
    .cart-total {
        justify-content: center;
    }
    
    .total-box {
        max-width: 100%;
        padding: 1.5rem;
    }
    
    .cart-actions {
        flex-direction: column-reverse;
    }
    
    .btn-continue,
    .btn-update {
        width: 100%;
        justify-content: center;
    }
}

/* Mobile Bottom Navigation */
.mobile-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--light);
    box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 12px 0;
    border-top: 1px solid var(--gray-medium);
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
    color: var(--gray-dark);
    font-size: 0.7rem;
    padding: 5px 10px;
    transition: all 0.3s;
}

.mobile-nav-item svg {
    width: 22px;
    height: 22px;
    margin-bottom: 5px;
    stroke-width: 1.5;
}

.mobile-nav-item.active {
    color: var(--primary);
    transform: translateY(-3px);
}

.mobile-nav-item.active svg {
    stroke: var(--accent);
    fill: var(--accent);
}

@media (max-width: 768px) {
    .mobile-nav {
        display: block;
    }
}

/* Animation for cart message */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-green-100 {
    animation: fadeIn 0.5s ease-out;
    border-left: 4px solid #10b981;
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
                        <a href="place_order.php" class="btn-continue">Proceed to Order <i class="fas fa-arrow-right mr-2"></i></a> 

                        

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