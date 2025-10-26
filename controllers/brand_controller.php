<?php
require_once dirname(__FILE__).'/../classes/brand_class.php';

// Add brand controller
function add_brand_ctr($brand_name) {
    $brand = new Brand();
    
    // Check if brand name already exists
    if ($brand->brand_name_exists($brand_name)) {
        return "Brand name already exists";
    }
    
    $result = $brand->add_brand($brand_name);
    return $result ? "success" : "Failed to add brand";
}

// Get brands controller
function get_brands_ctr() {
    $brand = new Brand();
    return $brand->get_all_brands();
}

// Get single brand controller
function get_brand_ctr($brand_id) {
    $brand = new Brand();
    return $brand->get_brand_by_id($brand_id);
}

// Update brand controller
function update_brand_ctr($brand_id, $brand_name) {
    $brand = new Brand();
    
    // Check if brand name already exists (excluding current brand)
    if ($brand->brand_name_exists($brand_name, $brand_id)) {
        return "Brand name already exists";
    }
    
    $result = $brand->update_brand($brand_id, $brand_name);
    return $result ? "success" : "Failed to update brand";
}

// Delete brand controller
function delete_brand_ctr($brand_id) {
    $brand = new Brand();
    $result = $brand->delete_brand($brand_id);
    return $result ? "success" : "Failed to delete brand";
}
?>
