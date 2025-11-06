<?php
require_once '../classes/customer_class.php';

function register_customer_ctr($fullname, $email, $password, $country, $city, $contact, $role = 2) {
    $customer = new Customer();
    return $customer->register_customer($fullname, $email, $password, $country, $city, $contact, $role);
}

function login_customer_ctr($email, $password) {
    $customer = new Customer();
    $user = $customer->get_customer_by_email($email);

    if ($user && password_verify($password, $user['customer_pass'])) {
        return $user; // success → return user data
    }
    return false; // fail
}
