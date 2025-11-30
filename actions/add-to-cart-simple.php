<?php
session_start();

// Log everything for debugging
error_log("Add to cart called");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

// Try to get data from multiple sources
$input = file_get_contents('php://input');
error_log("Raw input: " . $input);

// Parse JSON if sent as JSON
$jsonData = json_decode($input, true);

// Get data from POST, GET, or JSON
$productId = null;
$size = null;

if (isset($_POST['productId'])) {
    $productId = intval($_POST['productId']);
    $size = $_POST['size'];
} elseif (isset($_GET['productId'])) {
    $productId = intval($_GET['productId']);
    $size = $_GET['size'];
} elseif ($jsonData) {
    $productId = intval($jsonData['productId']);
    $size = $jsonData['size'];
}

error_log("Product ID: $productId, Size: $size");

// Validate
if (!$productId || !$size) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Missing product ID or size',
        'debug' => [
            'post' => $_POST,
            'get' => $_GET,
            'json' => $jsonData
        ]
    ]);
    exit;
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Create cart key
$cartKey = 'product_' . $productId . '_' . $size;

// Check if item exists
if (isset($_SESSION['cart'][$cartKey])) {
    $_SESSION['cart'][$cartKey]['quantity']++;
} else {
    // Try to fetch product details from database
    $productName = 'Product ' . $productId;
    $productPrice = 600.00;
    $productImage = 'placeholder.jpg';
    $productCategory = 'Sets';
    
    // Try to get real product data
    try {
        require_once dirname(__FILE__) . '/../controllers/product_controller.php';
        $product = get_product_ctr($productId);
        if ($product) {
            $productName = $product['product_title'];
            $productPrice = floatval($product['product_price']);
            $productImage = $product['product_image'] ?? 'placeholder.jpg';
            $productCategory = $product['cat_name'] ?? 'Product';
        }
    } catch (Exception $e) {
        error_log("Could not fetch product: " . $e->getMessage());
    }
    
    // Add new item
    $_SESSION['cart'][$cartKey] = [
        'product_id' => $productId,
        'size' => $size,
        'quantity' => 1,
        'cart_key' => $cartKey,
        'product_name' => $productName,
        'product_price' => $productPrice,
        'product_image' => $productImage,
        'product_category' => $productCategory
    ];
}

// Calculate total
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += $item['quantity'];
}

error_log("Cart updated. Total items: $totalItems");
error_log("Cart contents: " . print_r($_SESSION['cart'], true));

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'cartCount' => $totalItems,
    'cartKey' => $cartKey,
    'debug' => [
        'cartContents' => $_SESSION['cart']
    ]
]);
?>

