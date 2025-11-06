<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../controllers/customer_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = login_customer_ctr($email, $password);

    if ($result) 
    {
        // ✅ Set session variables
        $_SESSION['user_id'] = $result['customer_id'] ?? $result['user_id'] ?? null;
        $_SESSION['user_name'] = $result['customer_name'] ?? $result['user_name'] ?? null;
        $_SESSION['user_role'] = $result['user_role'] ?? 0;
    } 
    header('Content-Type: application/json');
    echo json_encode(['status' => $result ? 'success' : 'error']);

}
