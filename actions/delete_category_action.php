<?php
session_start();
require_once dirname(__FILE__).'/../controllers/category_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $cat_id = (int)($_POST['category_id'] ?? 0);

    if ($cat_id <= 0) {
        echo "Invalid category ID";
        exit;
    }

    $result = delete_category_ctr($cat_id);
    echo $result;
}
?>
