<?php
session_start();
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Eyeonic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .success-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }
        .btn-continue {
            display: inline-block;
            margin-top: 1rem;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-continue:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main>
        <div class="success-container">
            <h2 class="success-title">Payment Successful!</h2>
            <p>Thank you for your purchase. Your order has been successfully processed.</p>
            <a href="products.php" class="btn-continue">
                <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
            </a>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>