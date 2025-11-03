<?php
require_once dirname(__FILE__).'/../settings/db_connection.php';

class Customer extends db_connection {

    // ✅ Register new customer with hashed password
    public function register_customer($fullname, $email, $password, $country, $city, $contact, $role = 0) {
        // Always hash the password before saving
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users 
                (user_name, user_email, user_password, user_role) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$fullname, $email, $hashedPassword, $role]);
    }

    // ✅ Fetch a customer by email (for login)
    public function get_customer_by_email($email) {
        $sql = "SELECT user_id, user_name, user_email, user_password as customer_pass, user_role FROM users WHERE user_email = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        // Map database columns to expected column names
        if ($result) {
            return [
                'customer_id' => $result['user_id'],
                'customer_name' => $result['user_name'],
                'customer_email' => $result['user_email'],
                'customer_pass' => $result['customer_pass'],
                'user_role' => $result['user_role']
            ];
        }
        return false;
    }
}
