<?php
/**
 * Paystack Payment Verification
 * Handles payment verification after customer returns from Paystack gateway
 * 
 * SEQUENCE:
 * 1. Payment is confirmed
 * 2. Read the user's cart
 * 3. Create a new order in the orders table
 * 4. Create order_items linked to that new order
 * 5. Clear the user's cart
 * 6. Redirect to the success/order page
 */

header('Content-Type: application/json');
session_start();

require_once dirname(__FILE__) . '/../settings/core.php';
require_once dirname(__FILE__) . '/../settings/paystack_config.php';
require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
require_once dirname(__FILE__) . '/../controllers/order_controller.php';

error_log("=== PAYSTACK VERIFY PAYMENT ===");
error_log("Server: " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
error_log("Session ID: " . session_id());
error_log("User logged in: " . (isLoggedIn() ? 'YES' : 'NO'));
error_log("User ID: " . (getLoggedInUserId() ?? 'NULL'));

// Check if user is logged in
if (!isLoggedIn()) {
    error_log("ERROR: User not logged in during payment verification");
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.',
        'session_id' => session_id(),
        'debug' => [
            'session_status' => session_status(),
            'has_user_id' => isset($_SESSION['user_id']),
            'session_data' => array_keys($_SESSION ?? [])
        ]
    ]);
    exit();
}

// Get verification reference from POST data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;
$cart_items = isset($input['cart_items']) ? $input['cart_items'] : null;
$total_amount = isset($input['total_amount']) ? floatval($input['total_amount']) : 0;

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment reference provided'
    ]);
    exit();
}

try {
    error_log("Verifying Paystack transaction - Reference: $reference");
    
    // ============================================================
    // STEP 1: PAYMENT IS CONFIRMED
    // ============================================================
    
    // Verify transaction with Paystack
    error_log("Calling Paystack API to verify reference: $reference");
    $verification_response = paystack_verify_transaction($reference);
    
    if (!$verification_response) {
        error_log("ERROR: No response from Paystack API");
        throw new Exception("No response from Paystack verification API. Please check your internet connection and try again.");
    }
    
    error_log("Paystack verification response: " . json_encode($verification_response));
    
    // Check if verification was successful
    if (!isset($verification_response['status']) || $verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        $error_code = $verification_response['data']['gateway_response'] ?? 'Unknown error';
        error_log("Payment verification failed: $error_msg (Code: $error_code)");
        error_log("Full Paystack response: " . json_encode($verification_response));
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg . ($error_code !== 'Unknown error' ? ' (' . $error_code . ')' : ''),
            'verified' => false,
            'paystack_response' => $verification_response
        ]);
        exit();
    }
    
    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0; // Convert from pesewas
    $customer_email = $transaction_data['customer']['email'] ?? '';
    $authorization = $transaction_data['authorization'] ?? [];
    $authorization_code = $authorization['authorization_code'] ?? '';
    $payment_method = $authorization['channel'] ?? 'card';
    
    error_log("Transaction status: $payment_status, Amount: $amount_paid GHS");
    
    // Validate payment status - ONLY proceed if payment is successful
    if ($payment_status !== 'success') {
        error_log("Payment status is not successful: $payment_status");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_status),
            'verified' => false,
            'payment_status' => $payment_status
        ]);
        exit();
    }
    
    // Payment is confirmed! Now proceed with order creation
    
    // ============================================================
    // STEP 2: READ THE USER'S CART
    // ============================================================
    
    $customer_id = getLoggedInUserId();
    
    // Read cart items from session (primary source)
    $cart_items_to_order = [];
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
        throw new Exception("Cart is empty. Cannot create order without items.");
    }
    
    // Calculate total from cart items
    $calculated_subtotal = 0.00;
    foreach ($cart_items_to_order as $item) {
        $calculated_subtotal += floatval($item['product_price']) * intval($item['qty']);
    }
    
    // Add shipping and tax
    $shipping = 20.00;
    $tax = $calculated_subtotal * 0.1;
    $expected_total = $calculated_subtotal + $shipping + $tax;
    
    if ($total_amount <= 0) {
        $total_amount = round($expected_total, 2);
    }
    
    error_log("Cart subtotal: $calculated_subtotal GHS, Expected total: $total_amount GHS");
    
    // Verify amount matches (with 1 pesewa tolerance)
    if (abs($amount_paid - $total_amount) > 0.01) {
        error_log("Amount mismatch - Expected: $total_amount GHS, Paid: $amount_paid GHS");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match order total',
            'verified' => false,
            'expected' => number_format($total_amount, 2),
            'paid' => number_format($amount_paid, 2)
        ]);
        exit();
    }
    
    // ============================================================
    // STEP 3 & 4: CREATE ORDER AND ORDER ITEMS
    // ============================================================
    
    $order = new Order();
    $order->beginTransaction();
    
    try {
        // Generate invoice number as integer (database expects int)
        // MySQL INT max value: 2,147,483,647 (signed) or 4,294,967,295 (unsigned)
        // Format: YYMMDD + 3-digit random (e.g., 250101123 = 250,101,123)
        // This ensures we stay well within INT limits
        $date_part = date('ymd'); // YYMMDD format (e.g., 250101)
        $random_part = rand(100, 999); // 3-digit random (100-999)
        $invoice_no = intval($date_part . $random_part); // e.g., 250101123
        $order_date = date('Y-m-d');
        
        // STEP 3: Create order in database
        error_log("Attempting to create order - Customer ID: $customer_id, Invoice: $invoice_no");
        $order_id = create_order_ctr($customer_id, $invoice_no, 'Paid');
        
        if (!$order_id || $order_id <= 0) {
            error_log("Order creation failed - order_id is 0 or negative");
            throw new Exception("Failed to create order in database. Order ID returned: $order_id");
        }
        
        error_log("Order created successfully - ID: $order_id, Invoice: $invoice_no");
        
        // STEP 4: Create order items for each cart item
        $order_items_created = 0;
        foreach ($cart_items_to_order as $item) {
            $product_id = $item['product_id'] ?? null;
            $qty = intval($item['qty'] ?? 1);
            $unit_price = floatval($item['product_price'] ?? 0);
            
            if (!$product_id || $product_id <= 0) {
                error_log("Skipping invalid product_id in cart item");
                continue;
            }
            
            $detail_result = add_order_details_ctr($order_id, $product_id, $qty, $unit_price);
            
            if (!$detail_result) {
                throw new Exception("Failed to add order detail for product ID: $product_id");
            }
            
            $order_items_created++;
            error_log("Order item created - Product: $product_id, Qty: $qty, Price: $unit_price");
        }
        
        if ($order_items_created === 0) {
            throw new Exception("No order items were created. Order cannot be empty.");
        }
        
        error_log("Created $order_items_created order items for order $order_id");
        
        // Record payment in database
        $payment_id = record_payment_ctr(
            $total_amount,
            $customer_id,
            $order_id,
            'GHS',
            'paystack',
            $reference,
            $authorization_code,
            $payment_method
        );
        
        if (!$payment_id) {
            throw new Exception("Failed to record payment");
        }
        
        error_log("Payment recorded - ID: $payment_id, Reference: $reference");
        
        // ============================================================
        // STEP 5: CLEAR THE USER'S CART
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
        try {
            $order->commit();
            error_log("Database transaction committed successfully");
            
            // Verify order was actually created in database
            $verified_order = $order->verify_order_exists($order_id);
            
            if ($verified_order) {
                error_log("Order verified in database - ID: " . $verified_order['order_id'] . ", Invoice: " . $verified_order['invoice_no']);
            } else {
                error_log("WARNING: Order was not found in database after commit! Order ID: $order_id");
                error_log("This may indicate a transaction rollback or database connection issue.");
            }
        } catch (Exception $commit_error) {
            error_log("ERROR: Transaction commit failed: " . $commit_error->getMessage());
            throw $commit_error;
        }
        
        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_timestamp']);
        
        // ============================================================
        // STEP 6: REDIRECT TO ORDER SUMMARY PAGE
        // ============================================================
        
        error_log("Redirecting to order summary - Order ID: $order_id");
        
        // Redirect to order summary page instead of returning JSON
        // The callback page will handle the redirect
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Payment successful! Order confirmed.',
            'order_id' => $order_id,
            'invoice_no' => strval($invoice_no), // Convert to string for display
            'total_amount' => number_format($total_amount, 2),
            'currency' => 'GHS',
            'order_date' => date('F j, Y', strtotime($order_date)),
            'customer_name' => $_SESSION['user_name'] ?? 'Customer',
            'item_count' => $order_items_created,
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_method),
            'customer_email' => $customer_email,
            'cart_cleared' => true,
            'redirect_url' => '../view/order_summary.php?order_id=' . $order_id
        ]);
        
    } catch (Exception $e) {
        // Rollback database transaction on error
        $order->rollBack();
        error_log("Database transaction rolled back: " . $e->getMessage());
        
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in Paystack verification: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment processing error: ' . $e->getMessage()
    ]);
}
?>
