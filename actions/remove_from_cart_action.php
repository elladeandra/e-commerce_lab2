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

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid cart item.',
    ]);
    exit;
}

[$customer_id, $ip_address] = cart_context();

try {
    $removed = remove_from_cart_ctr($product_id, $customer_id, $ip_address);
    $summary = get_user_cart_summary_ctr($customer_id, $ip_address);

    echo json_encode([
        'status' => $removed ? 'success' : 'warning',
        'message' => $removed ? 'Item removed from cart.' : 'Item was not found in cart.',
        'data' => [
            'cart' => $summary,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to remove item from cart. Please try again.',
    ]);
}

