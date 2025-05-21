<?php
define('MPESA_ENV', 'sandbox'); // change to 'production' when live
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');

define('MPESA_SHORTCODE', '174379'); // Test Paybill
define('MPESA_PASSKEY', 'YOUR_PASSKEY');
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/mpesa/callback_url.php');

define('MPESA_BASE_URL', MPESA_ENV === 'sandbox'
    ? 'https://sandbox.safaricom.co.ke'
    : 'https://api.safaricom.co.ke');
