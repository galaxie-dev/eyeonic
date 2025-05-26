<?php
include 'header.php';
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $_SESSION['cart'][$productId] = $quantity;
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
        body {
            font-family: 'Inter', sans-serif;
        }
        .header-logo svg {
            width: 24px;
            height: 24px;
            color: #111827;
        }
        .header-logo-text {
            font-weight: 600;
            font-size: 1.125rem;
            color: #111827;
            user-select: none;
        }
        .nav-link {
            font-weight: 500;
            font-size: 0.875rem;
            color: #4b5563;
            transition: color 0.2s;
            text-decoration: none;
            margin-left: 2rem;
        }
        .nav-link:hover {
            color: #111827;
        }
        .btn-signin {
            display: inline-block;
            background-color: #111827;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.375rem 1rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-signin:hover {
            background-color: #1f2937;
        }
        .icon-button {
            color: #4b5563;
            font-size: 1rem;
            padding: 0.25rem;
            border-radius: 0.375rem;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: color 0.2s;
        }
        .icon-button:hover,
        .icon-button:focus {
            color: #111827;
            outline: none;
            box-shadow: 0 0 0 2px #111827;
        }
        .cart-section {
            max-width: 1200px;
            margin: 2.5rem auto 0 auto;
            padding: 0 1rem;
        }
        .cart-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #111827;
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
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .cart-item {
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            padding: 1rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .cart-item {
                grid-template-columns: 2fr 1fr 1fr;
                align-items: center;
            }
        }
        .cart-item-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: #111827;
            margin: 0;
        }
        .cart-item-details {
            font-size: 0.75rem;
            color: #6b7280;
            margin: 0.5rem 0 0 0;
        }
        .cart-item-subtotal {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        .cart-total {
            font-weight: 600;
            font-size: 1.125rem;
            color: #111827;
            text-align: right;
            margin-top: 1.5rem;
        }
        .btn-checkout {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 1rem;
            text-decoration: none;
            width: max-content;
            float: right;
        }
        .btn-checkout:hover {
            background-color: #1d4ed8;
        }
        footer {
            border-top: 1px solid #e5e7eb;
            margin-top: 4rem;
            padding: 1.5rem 1rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.625rem;
            color: #9ca3af;
        }
        @media (min-width: 640px) {
            footer {
                flex-direction: row;
                justify-content: space-between;
            }
        }
        .footer-links {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        @media (min-width: 640px) {
            .footer-links {
                margin-bottom: 0;
            }
        }
        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #6b7280;
        }
        .footer-social {
            display: flex;
            gap: 1.5rem;
            color: #9ca3af;
        }
        .footer-social a {
            color: inherit;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .footer-social a:hover {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- <header>
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center space-x-2 header-logo">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <span class="header-logo-text">Eyeonic</span>
            </div>
            <div class="hidden md:flex">
                <?php foreach ($categories as $category): ?>
                    <a class="nav-link" href="products.php?category=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a>
                <?php endforeach; ?>
                <a class="nav-link" href="products.php">All Products</a>
            </div>
            <div class="flex items-center space-x-3">
                <button class="btn-signin hidden sm:inline-block" type="button">Sign In</button>
                <button aria-label="Search" class="icon-button" type="button"><i class="fas fa-search"></i></button>
                <button aria-label="Menu" class="icon-button md:hidden" type="button"><i class="fas fa-bars"></i></button>
            </div>
        </nav>
    </header> -->
    <main>
        <section class="cart-section">
            <h2 class="cart-title">Your Cart</h2>
            <?php if (empty($cart)): ?>
                <p class="cart-empty">Your cart is empty.</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php
                    $total = 0;
                    foreach ($cart as $id => $qty):
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $product = $stmt->fetch();
                        $subtotal = $product['price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <div class="cart-item">
                            <h3 class="cart-item-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="cart-item-details">Qty: <?= $qty ?> | Price: KES <?= number_format($product['price'], 2) ?></p>
                            <p class="cart-item-subtotal">Subtotal: KES <?= number_format($subtotal, 2) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <h3 class="cart-total">Total: KES <?= number_format($total, 2) ?></h3>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
            <?php endif; ?>
        </section>
    </main>
  
</body>
</html>
  <?php include 'footer.php'; ?>
