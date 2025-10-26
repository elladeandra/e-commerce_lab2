<?php
require_once dirname(__FILE__).'/../settings/db_connection.php';

class Brand extends db_connection {

    // Add new brand
    public function add_brand($brand_name) {
        $sql = "INSERT INTO brands (brand_name) VALUES (?)";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$brand_name]);
    }

    // Get all brands
    public function get_all_brands() {
        $sql = "SELECT * FROM brands ORDER BY brand_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single brand by ID
    public function get_brand_by_id($brand_id) {
        $sql = "SELECT * FROM brands WHERE brand_id = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$brand_id]);
        return $stmt->fetch();
    }

    // Update brand name
    public function update_brand($brand_id, $brand_name) {
        $sql = "UPDATE brands SET brand_name = ? WHERE brand_id = ?";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$brand_name, $brand_id]);
    }

    // Delete brand
    public function delete_brand($brand_id) {
        $sql = "DELETE FROM brands WHERE brand_id = ?";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$brand_id]);
    }

    // Check if brand name exists (excluding current brand for updates)
    public function brand_name_exists($brand_name, $exclude_id = null) {
        if ($exclude_id) {
            $sql = "SELECT COUNT(*) FROM brands WHERE brand_name = ? AND brand_id != ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$brand_name, $exclude_id]);
        } else {
            $sql = "SELECT COUNT(*) FROM brands WHERE brand_name = ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$brand_name]);
        }
        return $stmt->fetchColumn() > 0;
    }
}
?>
