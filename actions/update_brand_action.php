<?php
session_start();
require_once dirname(__FILE__).'/../controllers/brand_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $brand_name = trim($_POST['brand_name'] ?? '');

    if (empty($brand_name) || $brand_id <= 0) {
        echo "Invalid data provided";
        exit;
    }

    $result = update_brand_ctr($brand_id, $brand_name);
    echo $result;
}
?>
