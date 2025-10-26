<?php
session_start();
require_once dirname(__FILE__).'/../controllers/product_controller.php';

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit;
}

$products = get_products_ctr();

header('Content-Type: application/json');
echo json_encode($products);
?>
