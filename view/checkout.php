<?php
session_start();

require_once dirname(__FILE__) . '/../controllers/cart_controller.php';

[$customer_id, $ip_address] = cart_context();
$cart_summary = get_user_cart_summary_ctr($customer_id, $ip_address);
$cart_items = $cart_summary['items'];
$totals = $cart_summary['totals'];
$requires_login = empty($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Commerce Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/cart.js" defer></script>
    <script src="../js/checkout.js" defer></script>
</head>
<body class="checkout-page">
    <header class="site-header">
        <div class="container">
            <a class="brand" href="../index.php"><i class="fas fa-store"></i> E‑Commerce</a>
            <nav class="menu">
                <a class="btn" href="../index.php"><i class="fas fa-home"></i> Home</a>
                <a class="btn" href="all_product.php"><i class="fas fa-box"></i> Products</a>
                <a class="btn" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a class="btn btn-primary active" href="checkout.php"><i class="fas fa-lock"></i> Checkout</a>
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

    <main class="checkout-main">
        <div class="container">
            <div class="section-head">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
                <p>Confirm your order details and simulate a secure payment.</p>
            </div>

            <div id="checkoutFeedback" class="feedback-banner" role="alert" hidden></div>

            <?php if ($requires_login): ?>
                <div class="empty-state">
                    <i class="fas fa-user-lock"></i>
                    <h2>Sign in to continue</h2>
                    <p>Please log in or create an account to place your order.</p>
                    <a href="login.php" class="btn btn-primary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-text">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </div>
            <?php elseif (empty($cart_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Add items to your cart before you can complete a checkout.</p>
                    <a href="all_product.php" class="btn btn-primary btn-large">
                        <i class="fas fa-box-open"></i> Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="checkout-grid">
                    <section class="checkout-summary">
                        <h2><i class="fas fa-list-check"></i> Order Summary</h2>
                        <div class="summary-list">
                            <?php foreach ($cart_items as $item): 
                                $itemSubtotal = $item['qty'] * $item['product_price'];
                            ?>
                                <div class="summary-item" data-product-id="<?php echo $item['product_id']; ?>">
                                    <div class="summary-item-info">
                                        <h3><?php echo htmlspecialchars($item['product_title']); ?></h3>
                                        <p>Qty: <?php echo (int)$item['qty']; ?> &bullet; $<?php echo number_format($item['product_price'], 2); ?></p>
                                    </div>
                                    <strong>$<?php echo number_format($itemSubtotal, 2); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-totals">
                            <div class="summary-line">
                                <span>Items</span>
                                <strong id="checkoutItemCount"><?php echo (int)$totals['count']; ?></strong>
                            </div>
                            <div class="summary-line">
                                <span>Subtotal</span>
                                <strong id="checkoutSubtotal">$<?php echo number_format($totals['subtotal'], 2); ?></strong>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-actions">
                        <div class="payment-card">
                            <h2><i class="fas fa-shield-check"></i> Simulated Payment</h2>
                            <p>Click the button below to open the payment modal. Confirming will process the order and clear your cart.</p>

                            <div class="payment-method">
                                <label for="checkoutCurrency"><i class="fas fa-globe"></i> Currency</label>
                                <select id="checkoutCurrency" class="styled-select">
                                    <option value="USD" selected>USD - United States Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                </select>
                            </div>

                            <button class="btn btn-primary btn-large" id="simulatePaymentBtn" data-total-amount="<?php echo $totals['subtotal']; ?>"<?php echo ($totals['subtotal'] <= 0) ? ' disabled' : ''; ?>>
                                <i class="fas fa-money-check-alt"></i> Simulate Payment
                            </button>
                            <a href="cart.php" class="btn btn-text">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a>
                        </div>
                    </section>
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

    <div class="modal-backdrop" id="paymentModal" hidden>
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
            <button class="modal-close" type="button" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2 id="paymentModalTitle"><i class="fas fa-wallet"></i> Confirm Simulated Payment</h2>
            </div>
            <div class="modal-body">
                <p>Please confirm that you have completed the simulated payment of <strong id="modalAmount"></strong>.</p>
                <p>This will create an order, record payment details, and empty your cart.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" id="cancelPaymentBtn">
                    <i class="fas fa-times-circle"></i> Cancel
                </button>
                <button class="btn btn-primary" type="button" id="confirmPaymentBtn">
                    <i class="fas fa-check-circle"></i> Yes, I've Paid
                </button>
            </div>
        </div>
    </div>

    <template id="checkoutResultTemplate">
        <div class="checkout-result">
            <div class="result-icon success">
                <i class="fas fa-circle-check"></i>
            </div>
            <h2>Thank you for your order!</h2>
            <p>Your order reference is <strong data-ref></strong>.</p>
            <p>A summary has been recorded for you. Feel free to continue shopping.</p>
            <div class="result-actions">
                <a class="btn btn-primary" href="all_product.php"><i class="fas fa-box"></i> Continue Shopping</a>
                <a class="btn btn-secondary" href="cart.php"><i class="fas fa-cart-shopping"></i> View Cart</a>
            </div>
        </div>
    </template>
</body>
</html>

