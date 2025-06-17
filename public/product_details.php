<?php
require_once '../config/database.php';
include 'header.php';

// Start session if not already started
// if (session_status() === PHP_SESSION_NONE) {
    // session_start();
// }

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT products.*, categories.name as category_name 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id 
    WHERE products.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
    exit;
}

// Handle add to cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add product to cart or update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Set success message
    $_SESSION['cart_message'] = 'Product added to cart successfully!';
    
    // Redirect to prevent form resubmission
    header("Location: product.php?id=$id");
    exit;
}

// Fetch categories for navigation
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic - <?= htmlspecialchars($product['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>:root {
    --primary: #2a3f54;
    --primary-light: #3a516e;
    --primary-dark: #1d2b3e;
    --accent: #d4af37; /* Gold accent for premium feel */
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
}

/* Product Section */
.product-detail-section {
    max-width: 1200px;
    margin: 3rem auto;
    padding: 0 2rem;
}

/* Product Container */
.product-detail {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
    background: var(--light);
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

@media (min-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

/* Product Image */
.product-image-container {
    padding: 2rem;
    background-color: var(--light);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.product-image {
    max-width: 100%;
    height: auto;
    max-height: 450px;
    object-fit: contain;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.product-image-container:hover .product-image {
    transform: scale(1.02);
}

/* Product Details */
.product-details {
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--primary-dark);
    line-height: 1.3;
}

.product-brand {
    font-size: 1rem;
    color: var(--accent);
    margin-bottom: 1.5rem;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin: 1.5rem 0;
    position: relative;
    display: inline-block;
}

.product-price:after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 2px;
    background: var(--accent);
}

.product-description {
    font-size: 0.9375rem;
    line-height: 1.7;
    color: var(--gray-dark);
    margin-bottom: 2rem;
}

/* Product Meta */
.product-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.product-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-dark);
    background: var(--gray-light);
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.product-meta-item i {
    color: var(--accent);
}

/* Cart Form */
.cart-form {
    margin-top: auto;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.quantity-selector label {
    font-weight: 500;
    color: var(--primary-dark);
}

.quantity-input {
    width: 80px;
    padding: 0.75rem;
    border: 1px solid var(--gray-medium);
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s;
    background: var(--light);
}

.quantity-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
}

.btn-add-to-cart {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background-color: var(--primary);
    color: white;
    font-weight: 600;
    font-size: 1rem;
    padding: 1rem 2rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-add-to-cart:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(42, 63, 84, 0.2);
}

.btn-add-to-cart:active {
    transform: translateY(0);
}

/* Success Message */
.alert-success {
    background-color: #f0fdf4;
    color: #166534;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-left: 4px solid #22c55e;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.alert-success i {
    color: #22c55e;
    font-size: 1.25rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .product-detail-section {
        margin: 1.5rem auto;
        padding: 0 1rem;
    }
    
    .product-details {
        padding: 1.5rem;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .product-price {
        font-size: 1.25rem;
        margin: 1rem 0;
    }
    
    .product-meta {
        gap: 1rem;
    }
    
    .btn-add-to-cart {
        padding: 0.875rem 1.5rem;
    }
}

@media (max-width: 480px) {
    .product-image-container {
        padding: 1.5rem;
    }
    
    .product-details {
        padding: 1.25rem;
    }
    
    .product-title {
        font-size: 1.3rem;
    }
    
    .product-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .quantity-selector {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
    </style>
</head>
<body>



    <main>
        <section class="product-detail-section">
            <?php if (isset($_SESSION['cart_message'])): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['cart_message'] ?>
                    <?php unset($_SESSION['cart_message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="product-detail">
                <div class="product-image-container">
                    <img 
                        alt="<?= htmlspecialchars($product['name']) ?>" 
                        class="product-image" 
                        src="<?php echo !empty($product['image_path']) ? '../' . htmlspecialchars($product['image_path']) : '../assets/no-image.png'; ?>"
                    />
                </div>
                
                <div class="product-details">
                    <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="product-brand">By <?= htmlspecialchars($product['brand']) ?></p>
                    
                    <p class="product-price">KES <?= number_format($product['price'], 2) ?></p>
                    
                    <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    
                    <div class="product-meta">
                        <div class="product-meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?= htmlspecialchars($product['category_name']) ?></span>
                        </div>
                        <div class="product-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <form action="cart.php" method="post" class="cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input 
                                type="number" 
                                id="quantity" 
                                name="quantity" 
                                class="quantity-input" 
                                value="1" 
                                min="1" 
                                max="10"
                                required
                            >
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn-add-to-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'mobile-menu.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>