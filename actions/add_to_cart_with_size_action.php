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
if (!$data || !isset($data['productId']) || !isset($data['size'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid data. Product ID and size are required.'
    ]);
    exit;
}

$productId = intval($data['productId']);
$size = htmlspecialchars(trim($data['size']));
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

// Validate size
$validSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
if (!in_array($size, $validSizes)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid size selected.'
    ]);
    exit;
}

// Database connection
require_once dirname(__FILE__) . '/../controllers/product_controller.php';
require_once dirname(__FILE__) . '/../controllers/category_controller.php';

try {
    // Fetch product details from database
    $product = get_product_ctr($productId);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'error' => 'Product not found.'
        ]);
        exit;
    }

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Create unique cart key (product_id + size)
    $cartKey = 'product_' . $productId . '_' . $size;

    // Check if item already exists in cart
    if (isset($_SESSION['cart'][$cartKey])) {
        // Increment quantity
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        // Add new item to cart
        $_SESSION['cart'][$cartKey] = [
            'cart_key' => $cartKey,
            'product_id' => $productId,
            'product_name' => $product['product_title'],
            'product_price' => floatval($product['product_price']),
            'product_image' => $product['product_image'] ?? '',
            'product_category' => $product['cat_name'] ?? 'Product',
            'product_keywords' => $product['product_keywords'] ?? '',
            'size' => $size,
            'quantity' => $quantity,
            'added_at' => date('Y-m-d H:i:s')
        ];
    }

    // Calculate total items
    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalItems += $item['quantity'];
    }

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cartCount' => $totalItems,
        'cartKey' => $cartKey
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

