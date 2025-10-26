<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "error";
        exit;
    }

    if (!isset($_FILES['bulk_images']) || empty($_FILES['bulk_images']['name'][0])) {
        echo "No files uploaded";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?? 'temp';
    $uploaded_files = [];
    $errors = [];

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

    // Process each uploaded file
    $file_count = count($_FILES['bulk_images']['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['bulk_images']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading file " . ($i + 1);
            continue;
        }

        $file = [
            'name' => $_FILES['bulk_images']['name'][$i],
            'type' => $_FILES['bulk_images']['type'][$i],
            'tmp_name' => $_FILES['bulk_images']['tmp_name'][$i],
            'size' => $_FILES['bulk_images']['size'][$i]
        ];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type for " . $file['name'] . ". Only JPEG, PNG, GIF, and WebP are allowed";
            continue;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "File too large: " . $file['name'] . ". Maximum size is 5MB";
            continue;
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'bulk_' . time() . '_' . $i . '_' . uniqid() . '.' . $file_extension;
        $file_path = $product_dir . $filename;

        // Verify the path is within uploads directory (security check)
        $real_uploads_dir = realpath($uploads_dir);
        $real_file_path = realpath(dirname($file_path));
        
        if ($real_file_path === false || strpos($real_file_path, $real_uploads_dir) !== 0) {
            $errors[] = "Invalid upload path for " . $file['name'];
            continue;
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $relative_path = 'uploads/u' . $user_id . '/p' . $product_id . '/' . $filename;
            $uploaded_files[] = $relative_path;
        } else {
            $errors[] = "Failed to upload " . $file['name'];
        }
    }

    // Return results
    $result = [
        'success' => count($uploaded_files) > 0,
        'uploaded_files' => $uploaded_files,
        'errors' => $errors,
        'total_uploaded' => count($uploaded_files),
        'total_errors' => count($errors)
    ];

    header('Content-Type: application/json');
    echo json_encode($result);
}
?>
