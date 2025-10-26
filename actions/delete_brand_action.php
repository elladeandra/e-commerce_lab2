<?php
session_start();
require_once dirname(__FILE__).'/../controllers/brand_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $brand_id = (int)($_POST['brand_id'] ?? 0);

    if ($brand_id <= 0) {
        echo "Invalid brand ID";
        exit;
    }

    $result = delete_brand_ctr($brand_id);
    echo $result;
}
?>
