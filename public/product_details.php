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
            background-color: #f9fafb;
            color: var(--dark);
        }
        
        /* Header styles */
        .header-logo svg {
            width: 24px;
            height: 24px;
            color: var(--dark);
        }
        
        .header-logo-text {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--dark);
            user-select: none;
        }
        
        .nav-link {
            font-weight: 500;
            font-size: 0.875rem;
            color: #4b5563;
            transition: color 0.2s;
            text-decoration: none;
            margin-left: 1rem;
        }
        
        .nav-link:hover {
            color: var(--dark);
        }
        
        /* Product section */
        .product-detail-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .product-image-container {
            padding: 1.5rem;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 0.375rem;
        }
        
        .product-details {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .product-brand {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .product-description {
            font-size: 0.9375rem;
            line-height: 1.5;
            color: #475569;
            margin-bottom: 1.5rem;
        }
        
        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .product-meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #64748b;
        }
        
        .product-meta-item i {
            color: var(--primary);
        }
        
        /* Cart form */
        .cart-form {
            margin-top: auto;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .quantity-input {
            width: 70px;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            text-align: center;
            font-weight: 500;
        }
        
        .btn-add-to-cart {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 0.9375rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn-add-to-cart:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-add-to-cart:active {
            transform: translateY(0);
        }
        
        /* Success message */
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .product-details {
                padding: 1rem;
            }
            
            .product-title {
                font-size: 1.25rem;
            }
            
            .product-price {
                font-size: 1.1rem;
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