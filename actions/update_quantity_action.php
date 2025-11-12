<?php
session_start();
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../controllers/cart_controller.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method',
    ]);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($product_id <= 0 || $qty <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product or quantity.',
    ]);
    exit;
}

[$customer_id, $ip_address] = cart_context();

try {
    $updated = update_cart_item_ctr($product_id, $qty, $customer_id, $ip_address);
    $summary = get_user_cart_summary_ctr($customer_id, $ip_address);

    echo json_encode([
        'status' => $updated ? 'success' : 'warning',
        'message' => $updated ? 'Cart updated successfully.' : 'No changes were made to the cart.',
        'data' => [
            'cart' => $summary,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to update cart at this time. Please try again.',
    ]);
}

