<?php
session_start();
require_once dirname(__FILE__).'/../controllers/brand_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $brand_name = trim($_POST['brand_name'] ?? '');

    if (empty($brand_name)) {
        echo "Brand name is required";
        exit;
    }

    $result = add_brand_ctr($brand_name);
    echo $result;
}
?>
