<?php
/**
 * FORCE REMOVE FROM CART - Guaranteed to work
 * This file directly removes items from session and redirects
 */

session_start();

// Get cart key from URL
$cartKey = $_GET['cartKey'] ?? '';

if (!$cartKey) {
    header('Location: ../view/cart.php?error=no_key');
    exit;
}

// Ensure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    header('Location: ../view/cart.php?error=cart_empty');
    exit;
}

// Log before removal
error_log("FORCE REMOVE: Attempting to remove cart key: $cartKey");
error_log("FORCE REMOVE: Cart keys before: " . implode(', ', array_keys($_SESSION['cart'])));

// Remove the item from session
if (isset($_SESSION['cart'][$cartKey])) {
    unset($_SESSION['cart'][$cartKey]);
    error_log("FORCE REMOVE: Item removed from session successfully");
} else {
    error_log("FORCE REMOVE: Item not found in session cart");
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
            error_log("FORCE REMOVE: Also removed from database cart: product_id=$product_id");
        }
    }
} catch (Exception $e) {
    error_log("FORCE REMOVE: Error removing from database cart: " . $e->getMessage());
}

// Clean up empty cart
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    error_log("FORCE REMOVE: Cart is now empty");
}

// Log after removal
error_log("FORCE REMOVE: Cart keys after: " . implode(', ', array_keys($_SESSION['cart'])));

// Force session write
session_write_close();
session_start();

// Verify removal
if (isset($_SESSION['cart'][$cartKey])) {
    error_log("FORCE REMOVE: ERROR - Item still exists after removal!");
} else {
    error_log("FORCE REMOVE: SUCCESS - Item confirmed removed");
}

// Redirect back to cart
header('Location: ../view/cart.php?success=removed&key=' . urlencode($cartKey));
exit;
?>

