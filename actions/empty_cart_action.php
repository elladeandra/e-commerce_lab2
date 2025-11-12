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

[$customer_id, $ip_address] = cart_context();

try {
    empty_cart_ctr($customer_id, $ip_address);

    echo json_encode([
        'status' => 'success',
        'message' => 'Cart emptied successfully.',
        'data' => [
            'cart' => get_user_cart_summary_ctr($customer_id, $ip_address),
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to empty cart. Please try again.',
    ]);
}

