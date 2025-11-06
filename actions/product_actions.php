<?php
require_once dirname(__FILE__).'/../controllers/product_controller.php';
require_once dirname(__FILE__).'/../controllers/category_controller.php';
require_once dirname(__FILE__).'/../controllers/brand_controller.php';

// Handle AJAX requests for product search
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'search':
            $query = isset($_GET['query']) ? trim($_GET['query']) : '';
            $cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : null;
            $brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : null;
            
            if (!empty($query) || $cat_id !== null || $brand_id !== null) {
                $products = search_products_with_filters_ctr($query, $cat_id, $brand_id);
                echo json_encode(['success' => true, 'products' => $products]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No search criteria provided']);
            }
            exit;
            
        case 'get_categories':
            $categories = get_categories_ctr();
            echo json_encode(['success' => true, 'categories' => $categories]);
            exit;
            
        case 'get_brands':
            $brands = get_brands_ctr();
            echo json_encode(['success' => true, 'brands' => $brands]);
            exit;
    }
}

// Handle regular page requests
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $cat_id = isset($_GET['category']) ? $_GET['category'] : null;
    $brand_id = isset($_GET['brand']) ? $_GET['brand'] : null;
    
    if (!empty($search_query) || $cat_id !== null || $brand_id !== null) {
        $products = search_products_with_filters_ctr($search_query, $cat_id, $brand_id);
        $search_term = $search_query;
    } else {
        $products = view_all_products_ctr();
        $search_term = '';
    }
} elseif (isset($_GET['category'])) {
    $cat_id = $_GET['category'];
    $products = filter_products_by_category_ctr($cat_id);
    $search_term = '';
} elseif (isset($_GET['brand'])) {
    $brand_id = $_GET['brand'];
    $products = filter_products_by_brand_ctr($brand_id);
    $search_term = '';
} else {
    $products = view_all_products_ctr();
    $search_term = '';
}

// Get categories and brands for filters
$categories = get_categories_ctr();
$brands = get_brands_ctr();

// Pagination
$per_page = 10;
$total_products = count($products);
$total_pages = ceil($total_products / $per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $per_page;
$paginated_products = array_slice($products, $offset, $per_page);

// Set variables for use in view pages (these will be available after require)
// All variables are now set and ready to use in the including file
?>

