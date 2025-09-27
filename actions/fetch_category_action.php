<?php
session_start();
require_once dirname(__FILE__).'/../controllers/category_controller.php';

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit;
}

$categories = get_categories_ctr();

header('Content-Type: application/json');
echo json_encode($categories);
?>
