<?php
require_once dirname(__FILE__).'/../settings/db_connection.php';

class Category extends db_connection {

    // Add new category
    public function add_category($cat_name) {
        $sql = "INSERT INTO categories (cat_name) VALUES (?)";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$cat_name]);
    }

    // Get all categories
    public function get_all_categories() {
        $sql = "SELECT * FROM categories ORDER BY cat_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single category by ID
    public function get_category_by_id($cat_id) {
        $sql = "SELECT * FROM categories WHERE cat_id = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$cat_id]);
        return $stmt->fetch();
    }

    // Update category name
    public function update_category($cat_id, $cat_name) {
        $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$cat_name, $cat_id]);
    }

    // Delete category
    public function delete_category($cat_id) {
        $sql = "DELETE FROM categories WHERE cat_id = ?";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$cat_id]);
    }

    // Check if category name exists (excluding current category for updates)
    public function category_name_exists($cat_name, $exclude_id = null) {
        if ($exclude_id) {
            $sql = "SELECT COUNT(*) FROM categories WHERE cat_name = ? AND cat_id != ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$cat_name, $exclude_id]);
        } else {
            $sql = "SELECT COUNT(*) FROM categories WHERE cat_name = ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$cat_name]);
        }
        return $stmt->fetchColumn() > 0;
    }
}
?>
