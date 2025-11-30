<?php
/**
 * Process Checkout Action
 * 
 * CORRECT LOGIC:
 * Step 1: Read all cart items for the current user
 * Step 2: Insert a new order into the orders table
 * Step 3: Retrieve the new order_id
 * Step 4: Insert one row per cart item into orderdetails
 * Step 5: Clear the cart for that customer
 * Step 6: Redirect to order summary page
 */

session_start();
require_once dirname(__FILE__) . '/../settings/core.php';
require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
require_once dirname(__FILE__) . '/../controllers/order_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../view/login.php?redirect=checkout');
    exit();
}

$customer_id = getLoggedInUserId();

error_log("=== PROCESS CHECKOUT START ===");
error_log("Customer ID: $customer_id");

// ============================================================
// STEP 1: READ ALL CART ITEMS FOR THE CURRENT USER
// ============================================================

// Initialize session cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Read cart items from session (primary source)
$cart_items_to_order = [];

if (!empty($_SESSION['cart'])) {
    // Use session cart
    foreach ($_SESSION['cart'] as $cartKey => $item) {
        $cart_items_to_order[] = [
            'product_id' => $item['product_id'],
            'qty' => $item['quantity'] ?? 1,
            'product_price' => $item['product_price'] ?? 0,
            'product_name' => $item['product_name'] ?? '',
            'size' => $item['size'] ?? null
        ];
    }
    error_log("Read " . count($cart_items_to_order) . " items from session cart");
} else {
    // Fallback to database cart
    [$customer_id_db, $ip_address] = cart_context();
    $cart_summary = get_user_cart_summary_ctr($customer_id_db, $ip_address);
    $db_cart_items = $cart_summary['items'] ?? [];
    
    foreach ($db_cart_items as $item) {
        $cart_items_to_order[] = [
            'product_id' => $item['product_id'] ?? $item['p_id'] ?? null,
            'qty' => $item['qty'] ?? 1,
            'product_price' => $item['product_price'] ?? 0,
            'product_name' => $item['product_title'] ?? '',
            'size' => $item['size'] ?? null
        ];
    }
    error_log("Read " . count($cart_items_to_order) . " items from database cart");
}

// Validate cart is not empty
if (empty($cart_items_to_order)) {
    error_log("ERROR: Cart is empty");
    header('Location: ../view/checkout.php?error=empty_cart');
    exit();
}

error_log("Cart items to order: " . json_encode($cart_items_to_order));

// ============================================================
// STEP 2: INSERT A NEW ORDER INTO THE ORDERS TABLE
// ============================================================

$order = new Order();
$order->beginTransaction();

try {
    // Generate invoice number as integer (database expects int)
    // Format: YYMMDD + 3-digit random (e.g., 250101123 = 250,101,123)
    $date_part = date('ymd'); // YYMMDD format (e.g., 250101)
    $random_part = rand(100, 999); // 3-digit random (100-999)
    $invoice_no = intval($date_part . $random_part); // e.g., 250101123
    $order_date = date('Y-m-d');
    $order_status = 'Paid';
    
    error_log("Attempting to create order - Customer ID: $customer_id, Invoice: $invoice_no");
    
    // Insert order into orders table
    $order_id = create_order_ctr($customer_id, $invoice_no, $order_status);
    
    if (!$order_id || $order_id <= 0) {
        error_log("ERROR: Order creation failed - order_id is 0 or negative");
        throw new Exception("Failed to create order in database. Order ID returned: $order_id");
    }
    
    error_log("Order created successfully - ID: $order_id, Invoice: $invoice_no");
    
    // ============================================================
    // STEP 3: RETRIEVE THE NEW ORDER_ID (already have it from create_order_ctr)
    // ============================================================
    
    // $order_id is already available from Step 2
    
    // ============================================================
    // STEP 4: INSERT ONE ROW PER CART ITEM INTO ORDERDETAILS
    // ============================================================
    
    $order_items_created = 0;
    foreach ($cart_items_to_order as $item) {
        $product_id = $item['product_id'] ?? null;
        $qty = intval($item['qty'] ?? 1);
        $unit_price = floatval($item['product_price'] ?? 0);
        
        if (!$product_id || $product_id <= 0) {
            error_log("Skipping invalid product_id in cart item");
            continue;
        }
        
        // Insert order detail
        $detail_result = add_order_details_ctr($order_id, $product_id, $qty, $unit_price);
        
        if (!$detail_result) {
            error_log("ERROR: Failed to add order detail for product ID: $product_id");
            throw new Exception("Failed to add order detail for product ID: $product_id");
        }
        
        $order_items_created++;
        error_log("Order detail created - Product: $product_id, Qty: $qty, Price: $unit_price");
    }
    
    if ($order_items_created === 0) {
        error_log("ERROR: No order items were created");
        throw new Exception("No order items were created. Order cannot be empty.");
    }
    
    error_log("Created $order_items_created order items for order $order_id");
    
    // ============================================================
    // STEP 5: CLEAR THE CART FOR THAT CUSTOMER
    // ============================================================
    
    // Clear session cart
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
        error_log("Session cart cleared");
    }
    
    // Clear database cart
    [$customer_id_db, $ip_address] = cart_context();
    $cart_emptied = empty_cart_ctr($customer_id_db, $ip_address);
    
    if (!$cart_emptied) {
        error_log("Warning: Database cart may not have been fully cleared");
    } else {
        error_log("Database cart cleared for customer: $customer_id_db");
    }
    
    // Verify cart is empty
    $remaining_cart = get_user_cart_summary_ctr($customer_id_db, $ip_address);
    if (!empty($remaining_cart['items'])) {
        error_log("WARNING: Cart still contains items after clearing: " . count($remaining_cart['items']));
        // Force delete all remaining items
        foreach ($remaining_cart['items'] as $remaining_item) {
            $product_id = $remaining_item['product_id'] ?? $remaining_item['p_id'] ?? null;
            if ($product_id) {
                remove_from_cart_ctr($product_id, $customer_id_db, $ip_address);
            }
        }
        error_log("Force-cleared remaining cart items");
    }
    
    // Commit database transaction
    $order->commit();
    error_log("Database transaction committed successfully");
    
    // ============================================================
    // STEP 6: REDIRECT TO ORDER SUMMARY PAGE
    // ============================================================
    
    error_log("Redirecting to order summary - Order ID: $order_id");
    header("Location: ../view/order_summary.php?order_id=$order_id");
    exit();
    
} catch (Exception $e) {
    // Rollback database transaction on error
    $order->rollBack();
    error_log("ERROR: Database transaction rolled back: " . $e->getMessage());
    
    // Redirect back to checkout with error
    header('Location: ../view/checkout.php?error=' . urlencode($e->getMessage()));
    exit();
}
