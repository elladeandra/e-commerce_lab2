<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['cartKey'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid data. Cart key is required.'
    ]);
    exit;
}

$cartKey = htmlspecialchars($data['cartKey']);

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Cart is empty.'
    ]);
    exit;
}

// Check if item exists in cart
if (!isset($_SESSION['cart'][$cartKey])) {
    echo json_encode([
        'success' => false,
        'error' => 'Item not found in cart.'
    ]);
    exit;
}

// Remove item from cart
unset($_SESSION['cart'][$cartKey]);

// Calculate total items
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += $item['quantity'];
}

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Product removed from cart successfully',
    'cartCount' => $totalItems,
    'cartEmpty' => empty($_SESSION['cart'])
]);
?>
