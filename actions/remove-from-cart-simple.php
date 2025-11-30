<?php
session_start();

// Log for debugging
error_log("Remove from cart called");

// Try to get cart key from multiple sources
$input = file_get_contents('php://input');
error_log("Raw input: " . $input);

$jsonData = json_decode($input, true);

$cartKey = null;

if (isset($_POST['cartKey'])) {
    $cartKey = $_POST['cartKey'];
} elseif (isset($_GET['cartKey'])) {
    $cartKey = $_GET['cartKey'];
} elseif ($jsonData && isset($jsonData['cartKey'])) {
    $cartKey = $jsonData['cartKey'];
}

error_log("Cart Key: $cartKey");

if (!$cartKey) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Missing cart key',
        'debug' => [
            'post' => $_POST,
            'get' => $_GET,
            'json' => $jsonData
        ]
    ]);
    exit;
}

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Cart is empty'
    ]);
    exit;
}

// Check if item exists
if (!isset($_SESSION['cart'][$cartKey])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Item not found in cart',
        'debug' => [
            'cartKey' => $cartKey,
            'availableKeys' => array_keys($_SESSION['cart'])
        ]
    ]);
    exit;
}

// Remove item
if (isset($_SESSION['cart'][$cartKey])) {
    unset($_SESSION['cart'][$cartKey]);
    error_log("Item removed from session cart: $cartKey");
} else {
    error_log("Item not found in session cart: $cartKey");
    error_log("Available cart keys: " . implode(', ', array_keys($_SESSION['cart'] ?? [])));
}

// Also remove from database cart if it exists
try {
    require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
    [$customer_id, $ip_address] = cart_context();
    
    // Extract product ID from cart key (format: product_ID_SIZE)
    if (preg_match('/product_(\d+)_/', $cartKey, $matches)) {
        $product_id = intval($matches[1]);
        if ($product_id > 0) {
            remove_from_cart_ctr($product_id, $customer_id, $ip_address);
            error_log("Also removed from database cart: product_id=$product_id");
        }
    }
} catch (Exception $e) {
    error_log("Error removing from database cart: " . $e->getMessage());
}

// Force session write
session_write_close();
session_start();

// Recalculate cart after removal
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate total
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += isset($item['quantity']) ? intval($item['quantity']) : 1;
}

error_log("Item removed. Total items: $totalItems");
error_log("Cart contents: " . print_r($_SESSION['cart'], true));
error_log("Cart keys after removal: " . implode(', ', array_keys($_SESSION['cart'])));

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Item removed',
    'cartCount' => $totalItems,
    'cartEmpty' => empty($_SESSION['cart']),
    'debug' => [
        'removedKey' => $cartKey,
        'remainingKeys' => array_keys($_SESSION['cart']),
        'cartCount' => $totalItems
    ]
]);
?>

