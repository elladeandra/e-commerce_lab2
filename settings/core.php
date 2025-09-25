<?php
//start session
session_start(); 

//for header redirection
ob_start();

//funtion to check for login
function isLoggedIn() : bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    return !empty($_SESSION['user_id']);
}

//function to get user ID
function getLoggedInUserId() : ?int {
    return isLoggedIn() ? (int)($_SESSION['user_id'] ?? 0) : null;
}

//function to check for role (admin, customer, etc)
function isAdmin() : bool {
    if (!isLoggedIn()) {
        return false;
    }
    $role = $_SESSION['user_role'] ?? null;
    return ((string)$role === '1');
}


?>