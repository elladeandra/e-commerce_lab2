<?php
session_start();
require_once dirname(__FILE__).'/../actions/product_actions.php';
// Variables are set in product_actions.php and available here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - E-Commerce Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/product_search.js" defer></script>
</head>
<body class="product-page">
    <header class="site-header">
        <div class="container">
            <a class="brand" href="../index.php"><i class="fas fa-store"></i> E‑Commerce</a>
            <nav class="menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == '1'): ?>
                        <a class="btn" href="../admin/category.php">Category</a>
                        <a class="btn" href="../admin/brand.php">Brand</a>
                        <a class="btn" href="../admin/product.php">Add Product</a>
                    <?php endif; ?>
                    <a class="btn btn-logout" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn" href="register.php">Register</a>
                    <a class="btn btn-primary" href="login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="products-main">
        <div class="container">
            <div class="section-head">
                <h1><i class="fas fa-search"></i> Search Results</h1>
                <?php if (!empty($search_term)): ?>
                    <p>Results for: "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>
                <?php else: ?>
                    <p>Filtered product results</p>
                <?php endif; ?>
            </div>

            <!-- Search and Filters Section -->
            <div class="filters-section">
                <div class="search-box-container">
                    <form method="GET" action="product_search_result.php" class="search-form">
                        <div class="search-input-group">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search products by title, description, or keywords..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   class="search-input">
                            <button type="submit" class="btn btn-primary search-btn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>

                <div class="filters-container">
                    <form method="GET" action="product_search_result.php" class="filters-form">
                        <?php if (!empty($search_term)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <?php endif; ?>
                        
                        <div class="filter-group">
                            <label for="filter_category"><i class="fas fa-tag"></i> Category:</label>
                            <select id="filter_category" name="category" class="filter-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['cat_id']; ?>" 
                                            <?php echo ($selected_category == $category['cat_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['cat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter_brand"><i class="fas fa-trademark"></i> Brand:</label>
                            <select id="filter_brand" name="brand" class="filter-select">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['brand_id']; ?>" 
                                            <?php echo ($selected_brand == $brand['brand_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary filter-btn">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="all_product.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    </form>
                </div>
            </div>

            <!-- Products Count -->
            <div class="products-count">
                <p>Found <?php echo $total_products; ?> product<?php echo $total_products != 1 ? 's' : ''; ?> (Showing <?php echo count($paginated_products); ?> on this page)</p>
            </div>

            <!-- Products Grid -->
            <?php if (empty($paginated_products)): ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your search terms or filters.</p>
                    <a href="all_product.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> View All Products
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($paginated_products as $product): ?>
                        <div class="product-card">
                            <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                                <?php if (!empty($product['product_image'])): ?>
                                    <div class="product-image">
                                        <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                             onerror="this.style.display='none'; this.parentElement.classList.add('placeholder');">
                                    </div>
                                <?php else: ?>
                                    <div class="product-image placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['product_title']); ?></h3>
                                    <p class="product-price">
                                        <i class="fas fa-dollar-sign"></i> 
                                        $<?php echo number_format($product['product_price'], 2); ?>
                                    </p>
                                    <p class="product-category">
                                        <i class="fas fa-tag"></i> 
                                        <?php echo htmlspecialchars($product['cat_name'] ?? 'No Category'); ?>
                                    </p>
                                    <p class="product-brand">
                                        <i class="fas fa-trademark"></i> 
                                        <?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?>
                                    </p>
                                </div>
                            </a>
                            
                            <div class="product-actions">
                                <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo ($current_page - 1); ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?><?php echo $selected_category ? '&category='.$selected_category : ''; ?><?php echo $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="pagination-btn active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?><?php echo $selected_category ? '&category='.$selected_category : ''; ?><?php echo $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo ($current_page + 1); ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?><?php echo $selected_category ? '&category='.$selected_category : ''; ?><?php echo $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <small>© <?php echo date('Y'); ?> E‑Commerce Platform. All rights reserved.</small>
        </div>
    </footer>

    <script>
        // Add to cart functionality (placeholder)
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                alert('Add to Cart functionality will be implemented soon! Product ID: ' + productId);
            });
        });
    </script>
</body>
</html>

