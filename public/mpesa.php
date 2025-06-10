<?php
require_once '../config/database.php';

// Manually load required classes if not using Composer's autoload
// require_once '../path-to-your-classes/Mpesa.php'; // Example if you have custom classes

session_start();

// M-Pesa configuration - Direct configuration (not recommended for production)
$config = [
    'shortcode' => '174379', // Direct value instead of $_ENV
    'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
    'consumer_key' => '2qofzV2WBO3USA7mxSWyK7gAaIgtcUiLuW9F99an56edpOGB',
    'consumer_secret' => 'LQunlMylY4UmxM5kNDmFttT2l14u4kK8hmIUHJaw4uMnx3fQvI8tIxGY1cGQKBZ5',
    'callback_url' => 'https://your-ngrok-url.ngrok.io/mpesa_callback.php',
    'env' => 'sandbox' // or 'production'
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: checkout.php?error=invalid_request');
    exit;
}

// Get order details from database
$orderId = (int)$_POST['order_id'];
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: login.php?redirect=checkout.php?order_id='.$orderId);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: checkout.php?error=invalid_order');
    exit;
}

// Validate payment method and phone number
$paymentMethod = $_POST['payment_method'] ?? '';
$mpesaPhone = '';

if ($paymentMethod === 'mpesa') {
    if (empty($_POST['mpesa_phone_number'])) {
        header('Location: checkout.php?order_id='.$orderId.'&error=phone_required');
        exit;
    }
    
    $mpesaPhone = formatPhoneNumber($_POST['mpesa_phone_number']);
    if (empty($mpesaPhone)) {
        header('Location: checkout.php?order_id='.$orderId.'&error=invalid_phone');
        exit;
    }
} else {
    header('Location: checkout.php?order_id='.$orderId.'&error=invalid_method');
    exit;
}

// Set the total amount from the order
$totalAmount = $order['total'];

// Generate access token
$accessToken = generateAccessToken($config['consumer_key'], $config['consumer_secret'], $config['env']);

if (!$accessToken) {
    error_log("Failed to generate access token");
    header('Location: checkout.php?order_id='.$orderId.'&error=auth_failed');
    exit;
}

// Prepare STK Push request
$timestamp = date('YmdHis');
$password = base64_encode($config['shortcode'] . $config['passkey'] . $timestamp);

$requestData = [
    'BusinessShortCode' => $config['shortcode'],
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $totalAmount,
    'PartyA' => $mpesaPhone,
    'PartyB' => $config['shortcode'],
    'PhoneNumber' => $mpesaPhone,
    'CallBackURL' => $config['callback_url'],
    'AccountReference' => 'Order#' . $orderId,
    'TransactionDesc' => 'Payment for Order #' . $orderId,
];

// Make the API request
$response = makeMpesaRequest($accessToken, $requestData, $config['env']);

if ($response === false) {
    error_log("Failed to make M-Pesa request for order $orderId");
    header('Location: checkout.php?order_id='.$orderId.'&error=api_error');
    exit;
}

$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Invalid JSON response: " . $response);
    header('Location: checkout.php?order_id='.$orderId.'&error=invalid_response');
    exit;
}

if (!isset($responseData['ResponseCode']) || $responseData['ResponseCode'] != "0") {
    $errorMsg = $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'Payment failed';
    error_log("M-Pesa error: " . $errorMsg);
    header('Location: checkout.php?order_id='.$orderId.'&error='.urlencode($errorMsg));
    exit;
}

// Record transaction in database
try {
    $stmt = $pdo->prepare("
        INSERT INTO mpesa_transactions 
        (order_id, checkout_request_id, phone, amount, status, created_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $orderId,
        $responseData['CheckoutRequestID'],
        $mpesaPhone,
        $totalAmount
    ]);
} catch (PDOException $e) {
    error_log("Failed to record transaction: " . $e->getMessage());
    // Continue anyway since the payment was initiated
}

// On success
$_SESSION['checkout_request_id'] = $responseData['CheckoutRequestID'];
$_SESSION['mpesa_payment_order'] = $orderId;

// Display success page
showSuccessPage($orderId, $mpesaPhone);

// Helper functions
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Handle 07... numbers (10 digits)
    if (strlen($phone) === 10 && $phone[0] === '0') {
        return '254' . substr($phone, 1);
    }
    
    // Handle 7... numbers (9 digits)
    if (strlen($phone) === 9 && $phone[0] === '7') {
        return '254' . $phone;
    }
    
    // Handle 254... numbers (12 digits)
    if (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
        return $phone;
    }
    
    return false;
}

function generateAccessToken($consumerKey, $consumerSecret, $env = 'sandbox') {
    $url = ($env === 'production') 
        ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for development!
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Access token request failed with HTTP code: $httpCode");
        return false;
    }
    
    $data = json_decode($response, true);
    return $data['access_token'] ?? false;
}

function makeMpesaRequest($accessToken, $requestData, $env = 'sandbox') {
    $url = ($env === 'production')
        ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for development!
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log("CURL error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("API request failed with HTTP code: $httpCode");
        return false;
    }
    
    return $response;
}

function showSuccessPage($orderId, $phone) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Initiated - Eyeonic</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="text-center mb-6">
                <i class="fas fa-mobile-alt text-5xl text-blue-500 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800">Payment Initiated</h1>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-600 mb-2">Order #<?= htmlspecialchars($orderId) ?></p>
                <p class="text-gray-600 mb-4">Please check your phone <strong><?= htmlspecialchars($phone) ?></strong> to complete the M-Pesa payment.</p>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-blue-800"><i class="fas fa-info-circle mr-2"></i> You should receive an STK Push prompt shortly.</p>
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <a href="track.php?order_id=<?= $orderId ?>" class="text-blue-500 hover:text-blue-700 font-medium">
                    <i class="fas fa-receipt mr-2"></i> View Order
                </a>
                <a href="index.php" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
            </div>
        </div>
        
        <script>
            // Auto-check payment status every 10 seconds
            setTimeout(() => {
                window.location.href = 'check_payment.php?order_id=<?= $orderId ?>';
            }, 10000);
        </script>
    </body>
    </html>
    <?php
    exit();
}