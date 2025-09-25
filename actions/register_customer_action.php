<?php
require_once(__DIR__ . '/../controllers/customer_controller.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $role = 2; // force Customer role (role 2)

    // Validate all fields are filled (basic check)
    if (empty($name) || empty($email) || empty($password) || empty($country) || empty($city) || empty($contact)) {
        echo "Please fill in all fields.";
        exit;
    }

    $result = register_customer_ctr($name, $email, $password, $country, $city, $contact, $role);

    if ($result) {
        echo "success";
    } else {
        echo "Registration failed";
    }
}
?>
