<?php
session_start();

$count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
echo json_encode(['count' => $count]);