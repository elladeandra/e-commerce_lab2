<?php
require_once dirname(__FILE__).'/../settings/db_connection.php';

class Customer extends db_connection {

    // ✅ Register new customer with hashed password
    public function register_customer($fullname, $email, $password, $country, $city, $contact, $role = 0) {
        // Always hash the password before saving
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users 
                (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$fullname, $email, $hashedPassword, $country, $city, $contact, $role]);
    }

    // ✅ Fetch a customer by email (for login)
    public function get_customer_by_email($email) {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_pass, user_role FROM customer WHERE customer_email = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        // Map database columns to expected column names
        if ($result) {
            return [
                'customer_id' => $result['customer_id'],
                'customer_name' => $result['customer_name'],
                'customer_email' => $result['customer_email'],
                'customer_pass' => $result['customer_pass'],
                'user_role' => $result['user_role']
            ];
        }
        return false;
    }
}
