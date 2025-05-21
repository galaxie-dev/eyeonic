<?php
require_once '../config/mpesa_config.php';

function getMpesaAccessToken() {
    $url = MPESA_BASE_URL . "/oauth/v1/generate?grant_type=client_credentials";
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $credentials"
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($response);
    return $json->access_token ?? null;
}
