<?php
session_start();

$action = $_GET['action'] ?? '';
$productId = $_GET['productId'] ?? '';
$size = $_GET['size'] ?? '';
$cartKey = $_GET['cartKey'] ?? '';

if ($action === 'add' && $productId && $size) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $key = 'product_' . $productId . '_' . $size;
    
    // Try to get product details
    $productName = 'Product ' . $productId;
    $productPrice = 600.00;
    $productImage = 'placeholder.jpg';
    $productCategory = 'Sets';
    
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
        // Use defaults
    }
    
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity']++;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'size' => $size,
            'quantity' => 1,
            'cart_key' => $key,
            'product_name' => $productName,
            'product_price' => $productPrice,
            'product_image' => $productImage,
            'product_category' => $productCategory
        ];
    }
    
    header('Location: ../view/cart.php?success=added');
    exit;
}

if ($action === 'remove' && $cartKey) {
    // Ensure cart exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Remove the item from session
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
    
    // Also remove from database cart
    try {
        require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
        [$customer_id, $ip_address] = cart_context();
        
        // Extract product ID from cart key (format: product_ID_SIZE)
        if (preg_match('/product_(\d+)_/', $cartKey, $matches)) {
            $product_id = intval($matches[1]);
            if ($product_id > 0) {
                remove_from_cart_ctr($product_id, $customer_id, $ip_address);
            }
        }
    } catch (Exception $e) {
        error_log("Error removing from database cart: " . $e->getMessage());
    }
    
    // Clean up empty cart
    if (empty($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Force session write
    session_write_close();
    session_start();
    
    header('Location: ../view/cart.php?success=removed&removed=' . urlencode($cartKey));
    exit;
}

header('Location: ../view/cart.php');
exit;
?>

