<?php
session_start();

require_once dirname(__FILE__) . '/../controllers/cart_controller.php';

[$customer_id, $ip_address] = cart_context();
$cart_summary = get_user_cart_summary_ctr($customer_id, $ip_address);
$cart_items = $cart_summary['items'];
$totals = $cart_summary['totals'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - E-Commerce Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/cart.js" defer></script>
</head>
<body class="cart-page">
    <header class="site-header">
        <div class="container">
            <a class="brand" href="../index.php"><i class="fas fa-store"></i> E‑Commerce</a>
            <nav class="menu">
                <a class="btn" href="../index.php"><i class="fas fa-home"></i> Home</a>
                <a class="btn" href="all_product.php"><i class="fas fa-box"></i> Products</a>
                <a class="btn btn-primary active" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
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
                    <a class="btn btn-secondary" href="login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="cart-main">
        <div class="container">
            <div class="section-head">
                <h1><i class="fas fa-shopping-basket"></i> Your Shopping Cart</h1>
                <p>Review your selected products, update quantities, or proceed to a smooth checkout.</p>
            </div>

            <div id="cartFeedback" class="feedback-banner" role="alert" hidden></div>

            <?php if (empty($cart_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is currently empty</h2>
                    <p>Browse our catalog and add items to your cart to see them here.</p>
                    <a href="all_product.php" class="btn btn-primary btn-large">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-layout" data-cart-summary='<?php echo json_encode($totals); ?>'>
                    <section class="cart-items" aria-label="Cart items">
                        <?php foreach ($cart_items as $item): 
                            $itemSubtotal = $item['qty'] * $item['product_price'];
                        ?>
                            <article class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="item-overview">
                                    <div class="item-image">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                                        <?php else: ?>
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-info">
                                        <h3><?php echo htmlspecialchars($item['product_title']); ?></h3>
                                        <p class="item-price">
                                            <i class="fas fa-dollar-sign"></i>
                        <span class="unit-price" data-unit-price="<?php echo htmlspecialchars($item['product_price']); ?>">
                                                $<?php echo number_format($item['product_price'], 2); ?>
                                            </span>
                                        </p>
                                        <?php if (!empty($item['product_keywords'])): ?>
                                            <p class="item-keywords">
                                                <i class="fas fa-tags"></i>
                                                <?php echo htmlspecialchars($item['product_keywords']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="item-actions">
                                    <div class="quantity-control" data-qty>
                                        <button class="qty-btn decrement" type="button" aria-label="Decrease quantity">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" min="1" value="<?php echo (int)$item['qty']; ?>" class="cart-qty-input" aria-label="Quantity">
                                        <button class="qty-btn increment" type="button" aria-label="Increase quantity">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="item-subtotal">
                                        <span>Subtotal</span>
                                        <strong class="subtotal-value" data-subtotal="<?php echo $itemSubtotal; ?>">
                                            $<?php echo number_format($itemSubtotal, 2); ?>
                                        </strong>
                                    </div>
                                    <button class="btn btn-text remove-item-btn" type="button">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <aside class="cart-summary">
                        <div class="summary-card">
                            <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                            <div class="summary-line">
                                <span>Items</span>
                                <strong id="summaryItemCount"><?php echo (int)$totals['count']; ?></strong>
                            </div>
                            <div class="summary-line">
                                <span>Subtotal</span>
                                <strong id="summarySubtotal">$<?php echo number_format($totals['subtotal'], 2); ?></strong>
                            </div>
                            <p class="summary-note"><i class="fas fa-info-circle"></i> Taxes and shipping are simulated for this lab.</p>

                            <div class="summary-actions">
                                <a href="checkout.php" class="btn btn-primary btn-large proceed-checkout">
                                    <i class="fas fa-lock"></i> Proceed to Checkout
                                </a>
                                <button class="btn btn-secondary btn-large empty-cart-btn" type="button">
                                    <i class="fas fa-trash-can"></i> Empty Cart
                                </button>
                                <a href="all_product.php" class="btn btn-text">
                                    <i class="fas fa-arrow-left"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
                <div class="empty-state" id="cartEmptyState" hidden>
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is currently empty</h2>
                    <p>Browse our catalog and add items to your cart to see them here.</p>
                    <a href="all_product.php" class="btn btn-primary btn-large">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <small>© <?php echo date('Y'); ?> E‑Commerce Platform. All rights reserved.</small>
        </div>
    </footer>

    <div id="cartToastContainer" class="toast-container" aria-live="assertive" aria-atomic="true"></div>
</body>
</html>

