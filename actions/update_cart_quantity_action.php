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
if (!$data || !isset($data['cartKey']) || !isset($data['quantity'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid data. Cart key and quantity are required.'
    ]);
    exit;
}

$cartKey = htmlspecialchars($data['cartKey']);
$quantity = intval($data['quantity']);

// Validate quantity
if ($quantity < 1) {
    echo json_encode([
        'success' => false,
        'error' => 'Quantity must be at least 1.'
    ]);
    exit;
}

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

// Update quantity
$_SESSION['cart'][$cartKey]['quantity'] = $quantity;

// Calculate new item subtotal
$itemSubtotal = $_SESSION['cart'][$cartKey]['product_price'] * $quantity;

// Calculate cart totals
$subtotal = 0;
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['product_price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$shipping = 20.00;
$tax = $subtotal * 0.1;
$total = $subtotal + $shipping + $tax;

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Quantity updated successfully',
    'quantity' => $quantity,
    'itemSubtotal' => number_format($itemSubtotal, 2),
    'cartSubtotal' => number_format($subtotal, 2),
    'cartTax' => number_format($tax, 2),
    'cartTotal' => number_format($total, 2),
    'cartCount' => $totalItems
]);
?>

