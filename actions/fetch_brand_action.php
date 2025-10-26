<?php
session_start();
require_once dirname(__FILE__).'/../controllers/brand_controller.php';

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit;
}

$brands = get_brands_ctr();

header('Content-Type: application/json');
echo json_encode($brands);
?>
