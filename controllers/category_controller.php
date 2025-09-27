<?php
require_once dirname(__FILE__).'/../classes/category_class.php';

// Add category controller
function add_category_ctr($cat_name) {
    $category = new Category();
    
    // Check if category name already exists
    if ($category->category_name_exists($cat_name)) {
        return "Category name already exists";
    }
    
    $result = $category->add_category($cat_name);
    return $result ? "success" : "Failed to add category";
}

// Get categories controller
function get_categories_ctr() {
    $category = new Category();
    return $category->get_all_categories();
}

// Get single category controller
function get_category_ctr($cat_id) {
    $category = new Category();
    return $category->get_category_by_id($cat_id);
}

// Update category controller
function update_category_ctr($cat_id, $cat_name) {
    $category = new Category();
    
    // Check if category name already exists (excluding current category)
    if ($category->category_name_exists($cat_name, $cat_id)) {
        return "Category name already exists";
    }
    
    $result = $category->update_category($cat_id, $cat_name);
    return $result ? "success" : "Failed to update category";
}

// Delete category controller
function delete_category_ctr($cat_id) {
    $category = new Category();
    $result = $category->delete_category($cat_id);
    return $result ? "success" : "Failed to delete category";
}
?>
