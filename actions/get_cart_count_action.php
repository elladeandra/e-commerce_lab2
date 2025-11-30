<?php
session_start();
header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    // Count total items in cart
    foreach ($_SESSION['cart'] as $item) {
        $count += isset($item['quantity']) ? $item['quantity'] : 1;
    }
}

echo json_encode(['count' => $count]);
?>

