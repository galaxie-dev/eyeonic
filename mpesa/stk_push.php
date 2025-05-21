<?php
require_once '../config/mpesa_config.php';
require_once 'access_token.php';

$phone = $_POST['phone']; // e.g., 2547XXXXXXXX
$amount = $_POST['amount'];

$access_token = getMpesaAccessToken();
if (!$access_token) {
    die("Failed to generate access token");
}

$timestamp = date('YmdHis');
$password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

$payload = [
    "BusinessShortCode" => MPESA_SHORTCODE,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => MPESA_SHORTCODE,
    "PhoneNumber" => $phone,
    "CallBackURL" => MPESA_CALLBACK_URL,
    "AccountReference" => "EyewearStore",
    "TransactionDesc" => "Eyewear Purchase"
];

$url = MPESA_BASE_URL . "/mpesa/stkpush/v1/processrequest";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
