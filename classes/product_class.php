<?php
require_once dirname(__FILE__).'/../settings/db_connection.php';

class Product extends db_connection {

    // Add new product
    public function add_product($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image = null) {
        $sql = "INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_keywords, product_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image]);
    }

    // Get all products with category and brand names
    public function get_all_products() {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single product by ID
    public function get_product_by_id($product_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_id = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$product_id]);
        return $stmt->fetch();
    }

    // Update product
    public function update_product($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image = null) {
        if ($product_image) {
            $sql = "UPDATE products SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?, product_desc = ?, product_keywords = ?, product_image = ? WHERE product_id = ?";
            $stmt = $this->connect()->prepare($sql);
            return $stmt->execute([$product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image, $product_id]);
        } else {
            $sql = "UPDATE products SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?, product_desc = ?, product_keywords = ? WHERE product_id = ?";
            $stmt = $this->connect()->prepare($sql);
            return $stmt->execute([$product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_id]);
        }
    }

    // Delete product
    public function delete_product($product_id) {
        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$product_id]);
    }

    // Check if product title exists (excluding current product for updates)
    public function product_title_exists($product_title, $exclude_id = null) {
        if ($exclude_id) {
            $sql = "SELECT COUNT(*) FROM products WHERE product_title = ? AND product_id != ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$product_title, $exclude_id]);
        } else {
            $sql = "SELECT COUNT(*) FROM products WHERE product_title = ?";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute([$product_title]);
        }
        return $stmt->fetchColumn() > 0;
    }

    // Get products by category
    public function get_products_by_category($cat_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_cat = ? 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$cat_id]);
        return $stmt->fetchAll();
    }

    // Get products by brand
    public function get_products_by_brand($brand_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_brand = ? 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$brand_id]);
        return $stmt->fetchAll();
    }

    // Search products by keywords (optimized for performance)
    public function search_products_by_keywords($keywords) {
        // Split keywords and create search terms
        $search_terms = array_map('trim', explode(',', $keywords));
        $search_terms = array_filter($search_terms); // Remove empty terms
        
        if (empty($search_terms)) {
            return [];
        }
        
        // Create LIKE conditions for each keyword
        $like_conditions = [];
        $params = [];
        
        foreach ($search_terms as $term) {
            $like_conditions[] = "p.product_keywords LIKE ?";
            $params[] = '%' . $term . '%';
        }
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE " . implode(' OR ', $like_conditions) . "
                ORDER BY p.product_id DESC";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // View all products (customer-facing method)
    public function view_all_products() {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Search products by title/name
    public function search_products($query) {
        $search_term = '%' . $query . '%';
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_title LIKE ? OR p.product_desc LIKE ? OR p.product_keywords LIKE ?
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term]);
        return $stmt->fetchAll();
    }

    // Filter products by category
    public function filter_products_by_category($cat_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_cat = ? 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$cat_id]);
        return $stmt->fetchAll();
    }

    // Filter products by brand
    public function filter_products_by_brand($brand_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_brand = ? 
                ORDER BY p.product_id DESC";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$brand_id]);
        return $stmt->fetchAll();
    }

    // View single product
    public function view_single_product($id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                WHERE p.product_id = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Advanced search with filters
    public function search_products_with_filters($query = '', $cat_id = null, $brand_id = null) {
        $conditions = [];
        $params = [];
        
        if (!empty($query)) {
            $search_term = '%' . $query . '%';
            $conditions[] = "(p.product_title LIKE ? OR p.product_desc LIKE ? OR p.product_keywords LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        if ($cat_id !== null && $cat_id !== '') {
            $conditions[] = "p.product_cat = ?";
            $params[] = $cat_id;
        }
        
        if ($brand_id !== null && $brand_id !== '') {
            $conditions[] = "p.product_brand = ?";
            $params[] = $brand_id;
        }
        
        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                $where_clause
                ORDER BY p.product_id DESC";
        
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
