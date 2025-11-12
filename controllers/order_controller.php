<?php
require_once dirname(__FILE__) . '/../classes/order_class.php';

function create_order_ctr(int $customer_id, string $invoice_no, string $status = 'processing'): int
{
    $order = new Order();
    return $order->create_order($customer_id, $invoice_no, $status);
}

function add_order_details_ctr(int $order_id, int $product_id, int $qty, float $unit_price): bool
{
    $order = new Order();
    return $order->add_order_detail($order_id, $product_id, $qty, $unit_price);
}

function record_payment_ctr(float $amount, int $customer_id, int $order_id, string $currency = 'USD'): bool
{
    $order = new Order();
    return $order->record_payment($amount, $customer_id, $order_id, $currency);
}

function get_customer_orders_ctr(int $customer_id): array
{
    $order = new Order();
    return $order->get_orders_by_customer($customer_id);
}

function get_order_details_ctr(int $order_id): array
{
    $order = new Order();
    return $order->get_order_details($order_id);
}

function generate_order_reference(): string
{
    return 'ORD-' . strtoupper(dechex(time())) . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

