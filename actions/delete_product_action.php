<?php
session_start();
require_once dirname(__FILE__).'/../controllers/product_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id <= 0) {
        echo "Invalid product ID";
        exit;
    }

    $result = delete_product_ctr($product_id);
    echo $result;
}
?>
