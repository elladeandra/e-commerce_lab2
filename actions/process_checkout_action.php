<?php
session_start();
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../classes/cart_class.php';
require_once dirname(__FILE__) . '/../classes/order_class.php';
require_once dirname(__FILE__) . '/../controllers/cart_controller.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.',
    ]);
    exit;
}

[$customer_id, $ip_address] = cart_context();

if (empty($customer_id)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please log in to complete checkout.',
    ]);
    exit;
}

$cart = new Cart();
$order = new Order();

try {
    $summary = $cart->get_cart_summary($customer_id, $ip_address);
    $items = $summary['items'];

    if (empty($items)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Your cart is empty. Add items before checking out.',
        ]);
        exit;
    }

    $order->beginTransaction();

    $invoice_no = random_int(100000000, 999999999);
    $order_reference = 'ORD-' . $invoice_no . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $order_id = $order->create_order($customer_id, (string)$invoice_no, 'processing');

    foreach ($items as $item) {
        $order->add_order_detail(
            $order_id,
            (int)$item['product_id'],
            (int)$item['qty'],
            (float)$item['product_price']
        );
    }

    $total_amount = (float)$summary['totals']['subtotal'];
    $currency = $_POST['currency'] ?? 'USD';

    $order->record_payment($total_amount, $customer_id, $order_id, $currency);

    $order->commit();

    $cart->empty_cart($customer_id, $ip_address);
    $empty_summary = $cart->get_cart_summary($customer_id, $ip_address);

    echo json_encode([
        'status' => 'success',
        'message' => 'Payment confirmed and order created successfully.',
        'data' => [
            'order_id' => $order_id,
            'invoice_no' => (string)$invoice_no,
            'order_reference' => $order_reference,
            'total_amount' => $total_amount,
            'currency' => $currency,
            'cart' => $empty_summary,
        ],
    ]);
} catch (Throwable $e) {
    $order->rollBack();
    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Checkout failed. Please try again.',
    ]);
}

