<?php
session_start();
require_once dirname(__FILE__).'/../controllers/product_controller.php';
require_once dirname(__FILE__).'/../controllers/cart_controller.php';

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

// Get cart count for header
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['quantity']) ? $item['quantity'] : 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
</head>
<body>
    <!-- HEADER -->
    <header class="site-header">
        <div class="main-header">
            <div class="header-content">
                <a href="../index.php" class="brand">LUMÉ</a>
                
                <div class="header-search">
                    <form method="GET" action="product_search_result.php" style="width: 100%;">
                        <input type="text" 
                               name="search" 
                               class="search-input-header" 
                               placeholder="Search products by title, description, or keywords...">
                        <span class="search-icon">🔍</span>
                    </form>
                </div>

                <div class="header-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</span>
                        <a href="../view/logout.php" class="action-btn btn-login">Logout</a>
                    <?php else: ?>
                        <a href="register.php" class="action-btn btn-register">Register</a>
                        <a href="login.php" class="action-btn btn-login">👤 Login</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-btn">
                        🛒
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-bar">
            <div class="nav-content">
                <nav class="nav-main">
                    <a href="all_product.php" class="nav-link">Shop</a>
                    <a href="all_product.php" class="nav-link">Collections</a>
                    <a href="../index.php#about" class="nav-link">About</a>
                    <a href="../index.php#contact" class="nav-link">Contact</a>
                </nav>

                <div class="nav-secondary">
                    <a href="all_product.php" class="secondary-link">All Products</a>
                    <a href="../index.php#features" class="secondary-link">Features</a>
                    <a href="checkout.php" class="secondary-link">🔒 Checkout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="single-product-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-nav">
                <a href="../index.php">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="all_product.php">All Products</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current"><?php echo htmlspecialchars($product['product_title']); ?></span>
            </nav>

            <div class="single-product-grid">
                <!-- Product Image Section -->
                <div class="product-image-wrapper">
                    <?php if (!empty($product['product_image'])): ?>
                        <div class="product-main-image-container">
                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                 class="product-main-image"
                                 onerror="this.style.display='none'; this.parentElement.classList.add('placeholder');">
                        </div>
                    <?php else: ?>
                        <div class="product-main-image-container placeholder">
                            <div class="placeholder-content">
                                <i class="fas fa-image"></i>
                                <span>No Image Available</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Details Section -->
                <div class="product-details-wrapper">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></h1>
                        <div class="product-meta-info">
                            <span class="product-category-badge"><?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?></span>
                        </div>
                    </div>

                    <div class="product-price-section">
                        <p class="product-price">GHS <?php echo number_format($product['product_price'], 2); ?></p>
                    </div>

                    <div class="product-info-section">
                        <div class="info-row">
                            <span class="info-label">Category:</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['cat_name'] ?? 'No Category'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Brand:</span>
                            <span class="info-value"><?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($product['product_desc'])): ?>
                        <div class="product-description-section">
                            <h3 class="section-title">Description</h3>
                            <p class="description-text"><?php echo nl2br(htmlspecialchars($product['product_desc'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['product_keywords'])): ?>
                        <div class="product-keywords-section">
                            <h3 class="section-title">Keywords</h3>
                            <div class="keywords-tags">
                                <?php 
                                $keywords = explode(',', $product['product_keywords']);
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
                    <?php endif; ?>

                    <div class="product-actions">
                        <button class="btn btn-primary btn-large quick-add-btn" 
                                onclick="openSizeModal(event, <?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars(addslashes($product['product_title'])); ?>', 'GHS <?php echo number_format($product['product_price'], 2); ?>', '<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>')">
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

    <!-- SIZE SELECTION MODAL -->
    <div class="size-modal-overlay" id="sizeModal">
        <div class="size-modal">
            <button class="modal-close" onclick="closeSizeModal()">&times;</button>
            
            <div class="modal-header">
                <h3 class="modal-title">Select Size</h3>
                <p class="modal-subtitle">Choose your size to add to cart</p>
            </div>

            <div class="modal-body">
                <!-- Product Preview -->
                <div class="modal-product-preview">
                    <img src="" alt="" class="modal-product-image" id="modalProductImage">
                    <div class="modal-product-info">
                        <p class="modal-product-name" id="modalProductName"></p>
                        <p class="modal-product-price" id="modalProductPrice"></p>
                    </div>
                </div>

                <!-- Size Selection -->
                <div class="size-selection">
                    <h4 class="size-label">Select Size:</h4>
                    <div class="size-options">
                        <button class="size-option" data-size="XS">XS</button>
                        <button class="size-option" data-size="S">S</button>
                        <button class="size-option" data-size="M">M</button>
                        <button class="size-option" data-size="L">L</button>
                        <button class="size-option" data-size="XL">XL</button>
                        <button class="size-option" data-size="2XL">2XL</button>
                        <button class="size-option" data-size="3XL">3XL</button>
                    </div>
                </div>

                <!-- Size Guide Link -->
                <a href="#" class="size-guide-link" target="_blank">📏 View Size Guide</a>

                <!-- Add to Cart Button -->
                <button class="modal-add-to-cart" id="addToCartBtn" disabled>
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-notification" id="toastNotification">
        <div class="toast-icon">✓</div>
        <div class="toast-message">
            <div class="toast-title" id="toastTitle">Success!</div>
            <div class="toast-text" id="toastText">Item added to cart</div>
        </div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
        </div>
    </footer>

    <script>
        // Size Modal Functions
        let selectedSize = null;
        let currentProductId = null;

        function openSizeModal(event, productId, productName, productPrice, productImage) {
            event.preventDefault();
            currentProductId = productId;
            selectedSize = null;
            
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductPrice').textContent = productPrice;
            
            if (productImage) {
                document.getElementById('modalProductImage').src = '../' + productImage;
                document.getElementById('modalProductImage').style.display = 'block';
            } else {
                document.getElementById('modalProductImage').style.display = 'none';
            }
            
            // Reset size selection
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            document.getElementById('addToCartBtn').disabled = true;
            document.getElementById('sizeModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSizeModal() {
            document.getElementById('sizeModal').classList.remove('active');
            document.body.style.overflow = '';
            selectedSize = null;
        }

        // Size selection
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.classList.contains('out-of-stock')) return;
                
                document.querySelectorAll('.size-option').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedSize = this.getAttribute('data-size');
                document.getElementById('addToCartBtn').disabled = false;
            });
        });

        // Add to Cart
        document.getElementById('addToCartBtn').addEventListener('click', async function() {
            if (!selectedSize || !currentProductId) return;
            
            this.disabled = true;
            this.textContent = 'Adding...';
            
            try {
                const response = await fetch('../actions/add_to_cart_with_size_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        productId: currentProductId,
                        size: selectedSize,
                        quantity: 1
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Item Added!', 'Product added to cart successfully');
                    updateCartCount();
                    closeSizeModal();
                    
                    // Reload page after a short delay to update cart count
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Error', data.message || 'Failed to add item to cart');
                    this.disabled = false;
                    this.textContent = 'Add to Cart';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error', 'Something went wrong. Please try again.');
                this.disabled = false;
                this.textContent = 'Add to Cart';
            }
        });

        // Close modal on overlay click
        document.getElementById('sizeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSizeModal();
            }
        });

        // Toast Notification
        function showToast(title, text) {
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastText').textContent = text;
            document.getElementById('toastNotification').classList.add('show');
            
            setTimeout(() => {
                closeToast();
            }, 3000);
        }

        function closeToast() {
            document.getElementById('toastNotification').classList.remove('show');
        }

        // Update cart count
        async function updateCartCount() {
            try {
                const response = await fetch('../actions/get_cart_count_action.php');
                const data = await response.json();
                
                if (data.count !== undefined) {
                    const cartCountEl = document.querySelector('.cart-count');
                    if (cartCountEl) {
                        cartCountEl.textContent = data.count;
                    }
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
            }
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
