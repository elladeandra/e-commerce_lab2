<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        echo "No file uploaded or upload error";
        exit;
    }

    $file = $_FILES['product_image'];
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? 'temp';

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed";
        exit;
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo "File too large. Maximum size is 5MB";
        exit;
    }

    // Use existing uploads directory structure
    $uploads_dir = dirname(__FILE__) . '/../uploads/';
    $user_dir = $uploads_dir . 'u' . $user_id . '/';
    $product_dir = $user_dir . 'p' . $product_id . '/';

    // Ensure user and product directories exist (uploads folder already exists)
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0755, true);
    }
    if (!is_dir($product_dir)) {
        mkdir($product_dir, 0755, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'image_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = $product_dir . $filename;

    // Verify the path is within uploads directory (security check)
    $real_uploads_dir = realpath($uploads_dir);
    $real_file_path = realpath(dirname($file_path));
    
    if ($real_file_path === false || strpos($real_file_path, $real_uploads_dir) !== 0) {
        echo "Invalid upload path";
        exit;
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Return relative path from uploads directory
        $relative_path = 'uploads/u' . $user_id . '/p' . $product_id . '/' . $filename;
        echo $relative_path;
    } else {
        echo "Failed to upload file";
    }
}
?>
