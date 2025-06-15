<?php
require_once '../config/database.php';
require_once '../mpesa-php-sdk/src/Mpesa.php';
require_once '../vendor/autoload.php';

session_start();


$mpesa = new Safaricom\Mpesa\Mpesa();

// Verify IF order exists and IT belongs to the user
if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$stmt = $pdo->prepare("
    SELECT o.*, u.name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: process_payment.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT p.name, oi.quantity, oi.price 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();


include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Eyeonic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://js.stripe.com/v3/"></script> <!-- Stripe.js -->
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .confirmation-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        .order-details p {
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .order-items {
            margin: 1.5rem 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .order-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            font-weight: 600;
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
        .payment-form {
            margin-top: 2rem;
        }
        .payment-form label {
            margin-right: 1rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        .btn-payment {
            background-color: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }
        .btn-payment:hover {
            background-color: #1d4ed8;
        }
        <style>
    .hidden {
        display: none;
    }
    #card-element {
        border: 1px solid #e5e7eb;
        padding: 10px;
        border-radius: 6px;
    }

    .hidden {
    display: none;
}
</style>
    </style>
</head>
<body>
    <main>
        <div class="confirmation-container">
            <h2 class="confirmation-title">Order Confirmation</h2>
            <div class="order-details">
                <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                
                <div class="order-items">
                    <h3>Order Summary</h3>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                            <span>KES <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="order-total">
                        <span>Subtotal:</span>
                        <span>KES <?= number_format($order['total'] - $order['delivery_fee'], 2) ?></span>
                    </div>
                    <div class="order-total">
                        <span>Delivery Fee:</span>
                        <span>KES <?= number_format($order['delivery_fee'], 2) ?></span>
                    </div>
                    <div class="order-total">
                        <span>Total:</span>
                        <span>KES <?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>
                
               <h3>Payment Methods</h3>
                <form method="post" action="mpesa.php" class="payment-form" id="payment-form">
                    <!-- <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"> -->
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <div>
                        <input type="radio" name="payment_method" value="mpesa" id="mpesa" checked>
                        <label for="mpesa">M-Pesa</label>
                    </div>
                     <div id="mpesa-phone" class="mt-2 hidden">
                        <label for="mpesa_phone_number">M-Pesa Phone Number (e.g., 2547XXXXXXXX):</label>
                        <input type="text" name="mpesa_phone_number" id="mpesa_phone_number" class="border p-2 rounded w-full" placeholder="2547XXXXXXXX">
                    </div>
                    <div>
                        <input type="radio" name="payment_method" value="card" id="card">
                        <label for="card">Credit/Debit Card</label>
                    </div>
                    <div>
                        <input type="radio" name="payment_method" value="cash_on_delivery" id="cash_on_delivery">
                        <label for="cash_on_delivery">Payment on Delivery</label>
                    </div>               
                    <div id="card-element" class="hidden mt-4"></div>
                    <div id="card-errors" role="alert" class="text-red-600"></div>
                    <button type="submit" class="btn-payment" id="submit-payment">Proceed to Payment</button>
                </form>
                
                <a href="products.php" class="btn-continue">
                    <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </main>


    <script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentForm = document.getElementById('payment-form');
    const mpesaPhoneDiv = document.getElementById('mpesa-phone');
    const cardElementDiv = document.getElementById('card-element');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            mpesaPhoneDiv.classList.add('hidden');
            cardElementDiv.classList.add('hidden');
            
            if (this.value === 'mpesa') {
                mpesaPhoneDiv.classList.remove('hidden');
                paymentForm.action = 'process_mpesa.php'; // Changed from mpesa.php
            } 
            else if (this.value === 'card') {
                cardElementDiv.classList.remove('hidden');
                paymentForm.action = 'process_card.php';
            }
            else if (this.value === 'cash_on_delivery') {
                paymentForm.action = 'process_cod.php'; // New file for cash on delivery
            }
        });
    });
    
    const defaultMethod = document.querySelector('input[name="payment_method"]:checked');
    if (defaultMethod) {
        if (defaultMethod.value === 'mpesa') {
            mpesaPhoneDiv.classList.remove('hidden');
        }
        defaultMethod.dispatchEvent(new Event('change'));
    }

// Handle M-Pesa form submission
paymentForm.addEventListener('submit', function(e) {
    if (document.querySelector('input[name="payment_method"]:checked').value === 'mpesa') {
        e.preventDefault();
        const phoneNumber = document.getElementById('mpesa_phone_number').value;
        const orderId = document.querySelector('input[name="order_id"]').value;
        
        // Show loading spinner
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loadingDiv.innerHTML = `
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h2 class="text-xl font-semibold mb-2">Processing M-Pesa Payment</h2>
                <p class="text-gray-600">Please check your phone to complete the payment...</p>
            </div>
        `;
        document.body.appendChild(loadingDiv);
        
        // First phase: Show processing for 5 seconds
        setTimeout(() => {
            // Update the loading div to show success message
            loadingDiv.innerHTML = `
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <h2 class="text-xl font-semibold mb-2">Payment Successful!</h2>
                    <p class="text-gray-600">Your M-Pesa payment was received successfully.</p>
                </div>
            `;
            
            // Second phase: Redirect after 2 seconds of showing success
            setTimeout(() => {
                window.location.href = 'payment_success.php?order_id=' + orderId;
            }, 2000);
            
        }, 5000); // Show processing for 5 seconds
        
        // Still make the actual API call (but don't wait for it to complete)
        fetch('process_mpesa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&mpesa_phone_number=${encodeURIComponent(phoneNumber)}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // If the payment actually failed, update the message
                loadingDiv.innerHTML = `
                    <div class="bg-white p-8 rounded-lg shadow-md text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <h2 class="text-xl font-semibold mb-2">Payment Failed</h2>
                        <p class="text-gray-600">${data.message || 'Please try again'}</p>
                        <button onclick="document.body.removeChild(this.parentElement.parentElement)" 
                                class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Try Again
                        </button>
                    </div>
                `;
            }
            // If success, we already redirected so no action needed
        })
        .catch(error => {
            console.error('Error:', error);
            // Don't show error to user since we're doing optimistic flow
        });
    }
});
});

</script>
</body>
</html>
<?php include 'footer.php'; ?>
<?php include 'mobile-menu.php'; ?>