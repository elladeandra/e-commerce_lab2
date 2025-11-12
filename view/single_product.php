<?php
session_start();
require_once dirname(__FILE__).'/../controllers/product_controller.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: all_product.php");
    exit;
}

// Get product details
$product = view_single_product_ctr($product_id);

if (!$product) {
    header("Location: all_product.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - E-Commerce Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/cart.js" defer></script>
</head>
<body class="product-page">
    <header class="site-header">
        <div class="container">
            <a class="brand" href="../index.php"><i class="fas fa-store"></i> E‑Commerce</a>
            <nav class="menu">
                <a class="btn" href="../index.php"><i class="fas fa-home"></i> Home</a>
                <a class="btn" href="all_product.php"><i class="fas fa-box"></i> Products</a>
                <a class="btn" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a class="btn" href="checkout.php"><i class="fas fa-lock"></i> Checkout</a>
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

    <main class="single-product-main">
        <div class="container">
            <div class="breadcrumb">
                <a href="../index.php"><i class="fas fa-home"></i> Home</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <a href="all_product.php">All Products</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span><?php echo htmlspecialchars($product['product_title']); ?></span>
            </div>

            <div class="single-product-container">
                <div class="product-image-section">
                    <?php if (!empty($product['product_image'])): ?>
                        <div class="product-main-image">
                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                 onerror="this.style.display='none'; this.parentElement.classList.add('placeholder');">
                        </div>
                    <?php else: ?>
                        <div class="product-main-image placeholder">
                            <i class="fas fa-image"></i>
                            <span>No Image Available</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-details-section">
                    <div class="product-header">
                        <h1><?php echo htmlspecialchars($product['product_title']); ?></h1>
                        <div class="product-meta">
                            <span class="product-id">
                                <i class="fas fa-hashtag"></i> Product ID: <?php echo $product['product_id']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="product-price-section">
                        <p class="product-price-large">
                            <i class="fas fa-dollar-sign"></i> 
                            $<?php echo number_format($product['product_price'], 2); ?>
                        </p>
                    </div>

                    <div class="product-info-grid">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-tag"></i> Category:</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['cat_name'] ?? 'No Category'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-trademark"></i> Brand:</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?></span>
                        </div>
                    </div>

                    <div class="product-description">
                        <h3><i class="fas fa-align-left"></i> Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['product_desc'] ?? 'No description available.')); ?></p>
                    </div>

                    <div class="product-keywords">
                        <h3><i class="fas fa-key"></i> Keywords</h3>
                        <div class="keywords-list">
                            <?php 
                            $keywords = explode(',', $product['product_keywords'] ?? '');
                            foreach ($keywords as $keyword): 
                                $keyword = trim($keyword);
                                if (!empty($keyword)):
                            ?>
                                <span class="keyword-tag"><?php echo htmlspecialchars($keyword); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>

                    <div class="product-actions-section">
                        <button class="btn btn-primary btn-large add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <a href="all_product.php" class="btn btn-secondary btn-large">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <small>© <?php echo date('Y'); ?> E‑Commerce Platform. All rights reserved.</small>
        </div>
    </footer>

</body>
</html>

