<?php
session_start();
require_once dirname(__FILE__).'/../controllers/category_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $cat_name = trim($_POST['category_name'] ?? '');

    if (empty($cat_name)) {
        echo "Category name is required";
        exit;
    }

    $result = add_category_ctr($cat_name);
    echo $result;
}
?>
