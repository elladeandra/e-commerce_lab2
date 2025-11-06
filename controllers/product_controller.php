<?php
require_once dirname(__FILE__).'/../classes/product_class.php';

// Add product controller
function add_product_ctr($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image = null) {
    $product = new Product();
    
    // Check if product title already exists
    if ($product->product_title_exists($product_title)) {
        return "Product title already exists";
    }
    
    $result = $product->add_product($product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image);
    return $result ? "success" : "Failed to add product";
}

// Get products controller
function get_products_ctr() {
    $product = new Product();
    return $product->get_all_products();
}

// Get single product controller
function get_product_ctr($product_id) {
    $product = new Product();
    return $product->get_product_by_id($product_id);
}

// Update product controller
function update_product_ctr($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image = null) {
    $product = new Product();
    
    // Check if product title already exists (excluding current product)
    if ($product->product_title_exists($product_title, $product_id)) {
        return "Product title already exists";
    }
    
    $result = $product->update_product($product_id, $product_cat, $product_brand, $product_title, $product_price, $product_desc, $product_keywords, $product_image);
    return $result ? "success" : "Failed to update product";
}

// Delete product controller
function delete_product_ctr($product_id) {
    $product = new Product();
    $result = $product->delete_product($product_id);
    return $result ? "success" : "Failed to delete product";
}

// Get products by category controller
function get_products_by_category_ctr($cat_id) {
    $product = new Product();
    return $product->get_products_by_category($cat_id);
}

// Get products by brand controller
function get_products_by_brand_ctr($brand_id) {
    $product = new Product();
    return $product->get_products_by_brand($brand_id);
}

// Search products by keywords controller
function search_products_by_keywords_ctr($keywords) {
    $product = new Product();
    return $product->search_products_by_keywords($keywords);
}

// View all products controller
function view_all_products_ctr() {
    $product = new Product();
    return $product->view_all_products();
}

// Search products controller
function search_products_ctr($query) {
    $product = new Product();
    return $product->search_products($query);
}

// Filter products by category controller
function filter_products_by_category_ctr($cat_id) {
    $product = new Product();
    return $product->filter_products_by_category($cat_id);
}

// Filter products by brand controller
function filter_products_by_brand_ctr($brand_id) {
    $product = new Product();
    return $product->filter_products_by_brand($brand_id);
}

// View single product controller
function view_single_product_ctr($id) {
    $product = new Product();
    return $product->view_single_product($id);
}

// Search products with filters controller
function search_products_with_filters_ctr($query = '', $cat_id = null, $brand_id = null) {
    $product = new Product();
    return $product->search_products_with_filters($query, $cat_id, $brand_id);
}
?>
