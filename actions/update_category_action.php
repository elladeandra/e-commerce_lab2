<?php
session_start();
require_once dirname(__FILE__).'/../controllers/category_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $cat_id = (int)($_POST['category_id'] ?? 0);
    $cat_name = trim($_POST['category_name'] ?? '');

    if (empty($cat_name) || $cat_id <= 0) {
        echo "Invalid data provided";
        exit;
    }

    $result = update_category_ctr($cat_id, $cat_name);
    echo $result;
}
?>
