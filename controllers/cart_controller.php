<?php
require_once dirname(__FILE__) . '/../classes/cart_class.php';

function cart_context(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $customer_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

    return [$customer_id, $ip_address];
}

function add_to_cart_ctr(int $product_id, int $qty = 1, ?int $customer_id = null, ?string $ip_address = null): array
{
    $cart = new Cart();
    return $cart->add_product_to_cart($product_id, $qty, $customer_id, $ip_address);
}

function update_cart_item_ctr(int $product_id, int $qty, ?int $customer_id = null, ?string $ip_address = null): bool
{
    $cart = new Cart();
    return $cart->update_cart_item($product_id, $qty, $customer_id, $ip_address);
}

function remove_from_cart_ctr(int $product_id, ?int $customer_id = null, ?string $ip_address = null): bool
{
    $cart = new Cart();
    return $cart->remove_cart_item($product_id, $customer_id, $ip_address);
}

function empty_cart_ctr(?int $customer_id = null, ?string $ip_address = null): bool
{
    $cart = new Cart();
    return $cart->empty_cart($customer_id, $ip_address);
}

function get_user_cart_ctr(?int $customer_id = null, ?string $ip_address = null): array
{
    $cart = new Cart();
    return $cart->get_cart_items($customer_id, $ip_address);
}

function get_user_cart_summary_ctr(?int $customer_id = null, ?string $ip_address = null): array
{
    $cart = new Cart();
    return $cart->get_cart_summary($customer_id, $ip_address);
}

