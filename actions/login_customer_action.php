<?php
session_start();
require_once dirname(__FILE__).'/../controllers/customer_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = login_customer_ctr($email, $password);

    if ($result) {
        // ✅ Set session variables
        $_SESSION['user_id'] = $result['customer_id'];
        $_SESSION['user_name'] = $result['customer_name'];
        $_SESSION['user_role'] = $result['user_role'];

        echo "success";
    } else {
        echo "Invalid email or password";
    }
}
