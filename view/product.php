<?php
require_once dirname(__FILE__).'/../settings/core.php';
require_once dirname(__FILE__).'/../controllers/product_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get all products
$products = get_products_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
</head>
<body class="product-page">
    <div class="container">
        <header class="site-header">
            <div class="container">
                <a class="brand" href="../index.php"><i class="fas fa-store"></i> E‑Commerce</a>
                <nav class="menu">
                    <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    <a class="btn" href="../index.php"><i class="fas fa-home"></i> Home</a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == '1'): ?>
                        <a class="btn" href="../admin/product.php"><i class="fas fa-plus"></i> Add Product</a>
                    <?php endif; ?>
                    <a class="btn btn-logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </header>

        <main class="products-main">
            <div class="section-head">
                <h1><i class="fas fa-box"></i> Our Products</h1>
                <p>Discover our wide range of products across different categories and brands.</p>
            </div>

            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Available</h3>
                    <p>Check back later for new products!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if (!empty($product['product_image'])): ?>
                                <div class="product-image">
                                    <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                         onerror="this.style.display='none'">
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
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['product_desc'], 0, 100)); ?>
                                    <?php if (strlen($product['product_desc']) > 100): ?>...<?php endif; ?>
                                </p>
                                <p class="product-keywords">
                                    <i class="fas fa-key"></i> 
                                    <?php echo htmlspecialchars($product['product_keywords']); ?>
                                </p>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn btn-primary" onclick="viewProduct(<?php echo $product['product_id']; ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer class="site-footer">
            <div class="container">
                <small>© <?php echo date('Y'); ?> E‑Commerce Platform. All rights reserved.</small>
            </div>
        </footer>
    </div>

    <script>
        function viewProduct(productId) {
            alert('Product details view for ID: ' + productId + '\n(This would show detailed product information in a future implementation)');
        }
    </script>
</body>
</html>
