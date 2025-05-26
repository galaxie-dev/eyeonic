<?php
require_once '../config/database.php';
include 'header.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<p>Product not found.</p>";
    include 'footer.php';
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
        .product-detail-section {
            max-width: 1200px;
            margin: 2.5rem auto 0 auto;
            padding: 0 1rem;
        }
        .product-detail-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #111827;
        }
        .product-detail {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            padding: 1.5rem;
        }
        @media (min-width: 640px) {
            .product-detail {
                grid-template-columns: 1fr 1fr;
            }
        }
        .product-image {
            width: 100%;
            height: auto;
            object-fit: contain;
            padding: 1.5rem;
            background-color: white;
            border-radius: 0.375rem;
        }
        .details {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .product-price {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1rem;
        }
        .product-desc {
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .product-form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .product-form label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }
        .product-form input[type="number"] {
            width: 100px;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        .product-form button {
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            width: max-content;
        }
        .product-form button:hover {
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

    <main>
        <section class="product-detail-section">
            <h2 class="product-detail-title"><?= htmlspecialchars($product['name']) ?></h2>
            <div class="product-detail">
                <img alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" height="400" src="<?php echo !empty($product['image_path']) ? '../' . htmlspecialchars($product['image_path']) : '../assets/no-image.png'; ?>" width="400"/>
                <div class="details">
                    <p class="product-price">KES <?= number_format($product['price'], 2) ?></p>
                    <p class="product-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <form action="cart.php" method="post" class="product-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <label for="qty">Quantity:</label>
                        <input type="number" name="quantity" id="qty" value="1" min="1" required>
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

</body>
</html>

<?php include 'footer.php'; ?>