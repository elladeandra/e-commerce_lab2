<?php
session_start();
require_once dirname(__FILE__).'/../controllers/product_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    $product_cat = (int)($_POST['product_category'] ?? 0);
    $product_brand = (int)($_POST['product_brand'] ?? 0);
    $product_title = trim($_POST['product_title'] ?? '');
    $product_price = trim($_POST['product_price'] ?? '');
    $product_desc = trim($_POST['product_description'] ?? '');
    $product_keywords = trim($_POST['product_keyword'] ?? '');

    if (empty($product_title) || empty($product_price) || empty($product_desc) || empty($product_keywords) || $product_cat <= 0 || $product_brand <= 0) {
        echo "All fields are required";
        exit;
    }

    if (!is_numeric($product_price) || $product_price <= 0) {
        echo "Invalid price";
        exit;
    }

    // ✅ Handle single image upload
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__FILE__).'/../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $tmp_name = $_FILES['product_image']['tmp_name'];
        $filename = time().'_'.basename($_FILES['product_image']['name']);
        $target_path = $upload_dir.$filename;

        if (move_uploaded_file($tmp_name, $target_path)) {
            $product_image = 'uploads/products/'.$filename; // relative path for DB
        } else {
            echo "Failed to upload image";
            exit;
        }
    }

    // ✅ Call controller with actual image path
    $result = add_product_ctr(
        $product_cat,
        $product_brand,
        $product_title,
        $product_price,
        $product_desc,
        $product_keywords,
        $product_image
    );

    echo $result ? "success" : "error";
}
?>
