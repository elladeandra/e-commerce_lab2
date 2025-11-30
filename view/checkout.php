<?php
session_start();

require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
require_once dirname(__FILE__) . '/../controllers/category_controller.php';
require_once dirname(__FILE__) . '/../controllers/brand_controller.php';

// ALWAYS use session-based cart for consistency (same as cart.php)
// Initialize session cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// If session cart is empty, try to load from database and convert to session
if (empty($_SESSION['cart'])) {
    [$customer_id, $ip_address] = cart_context();
    $cart_summary = get_user_cart_summary_ctr($customer_id, $ip_address);
    $db_cart_items = $cart_summary['items'];
    
    // Convert database cart items to session format
    if (!empty($db_cart_items)) {
        foreach ($db_cart_items as $item) {
            $size = $item['size'] ?? 'M'; // Default size if not set
            $cartKey = 'product_' . $item['product_id'] . '_' . $size;
            
            // Only add if not already in session
            if (!isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey] = [
                    'cart_key' => $cartKey,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_title'],
                    'product_price' => floatval($item['product_price']),
                    'product_image' => $item['product_image'] ?? '',
                    'product_category' => $item['cat_name'] ?? 'Product',
                    'product_keywords' => $item['product_keywords'] ?? '',
                    'size' => $size,
                    'quantity' => intval($item['qty'])
                ];
            }
        }
    }
}

// Build cart items array from session
$cart_items = [];
$subtotal = 0;
$totalItems = 0;

foreach ($_SESSION['cart'] as $cartKey => $item) {
    $cart_items[] = [
        'cart_key' => $cartKey,
        'product_id' => $item['product_id'],
        'product_title' => $item['product_name'],
        'product_price' => $item['product_price'],
        'product_image' => $item['product_image'],
        'cat_name' => $item['product_category'],
        'product_keywords' => $item['product_keywords'] ?? '',
        'size' => $item['size'],
        'qty' => $item['quantity']
    ];
    $subtotal += $item['product_price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$totals = [
    'count' => $totalItems,
    'subtotal' => $subtotal
];

$requires_login = empty($_SESSION['user_id']);

// Get categories and brands for header
$categories = get_categories_ctr();
$brands = get_brands_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/cart.js" defer></script>
    <script src="../js/checkout.js" defer></script>
</head>
<body class="checkout-page">
    <!-- HEADER - THREE SECTIONS STACKED VERTICALLY -->
    <header class="site-header">
        
        <!-- SECTION 1: MAIN HEADER -->
        <div class="main-header">
            <div class="header-content">
                <a href="../index.php" class="brand">LUMÉ</a>
                
                <div class="header-search">
                    <form method="GET" action="product_search_result.php" style="width: 100%;">
                        <input type="text" 
                               name="search" 
                               class="search-input-header" 
                               placeholder="Search products by title, description, or keywords..."
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <span class="search-icon">🔍</span>
                    </form>
                </div>

                <div class="header-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == '1'): ?>
                            <a href="../admin/category.php" class="action-btn btn-register">Category</a>
                            <a href="../admin/brand.php" class="action-btn btn-register">Brand</a>
                            <a href="../admin/product.php" class="action-btn btn-register">Add Product</a>
                        <?php endif; ?>
                        <a href="logout.php" class="action-btn btn-login">Logout</a>
                    <?php else: ?>
                        <a href="register.php" class="action-btn btn-register">Register</a>
                        <a href="login.php" class="action-btn btn-login">👤 Login</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-btn">
                        🛒
                        <span class="cart-count"><?php echo count($cart_items); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- SECTION 2: NAVIGATION BAR -->
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

    <main class="checkout-main">
        <div class="container">
            <div class="section-head">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
                <p>Confirm your order details and simulate a secure payment.</p>
            </div>

            <div id="checkoutFeedback" class="feedback-banner" role="alert" hidden></div>

            <?php 
            // Display error messages from URL parameters
            if (isset($_GET['error'])): 
                $error_type = $_GET['error'] ?? '';
                $error_msg = $_GET['msg'] ?? '';
                $error_details = $_GET['details'] ?? '';
                
                $error_messages = [
                    'verification_failed' => $error_msg ?: 'Payment verification failed. Please contact support if payment was deducted.',
                    'connection_error' => $error_details ?: 'Connection error occurred. Please try again.',
                    'cancelled' => 'Payment was cancelled. You can try again when ready.'
                ];
                
                $display_message = $error_messages[$error_type] ?? 'An error occurred during payment processing.';
            ?>
                <div class="feedback-banner" role="alert" style="background: #fee2e2; border: 2px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                    <strong><i class="fas fa-exclamation-triangle"></i> Payment Error:</strong> <?php echo htmlspecialchars($display_message); ?>
                    <?php if ($error_type === 'verification_failed'): ?>
                        <br><small style="margin-top: 0.5rem; display: block;">If payment was deducted, please contact support with your payment reference.</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($requires_login): ?>
                <div class="checkout-login-prompt">
                    <div class="checkout-lock-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2 class="checkout-prompt-title">Sign in to continue</h2>
                    <p class="checkout-prompt-text">Please log in or create an account to place your order.</p>
                    <div class="checkout-action-buttons">
                        <a href="login.php" class="checkout-btn checkout-btn-primary">
                            <span>LOGIN</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="register.php" class="checkout-btn checkout-btn-primary">
                            <span>CREATE ACCOUNT</span>
                            <i class="fas fa-user-plus"></i>
                        </a>
                    </div>
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
                                        <p>Qty: <?php echo (int)$item['qty']; ?> &bullet; GHS <?php echo number_format($item['product_price'], 2); ?></p>
                                    </div>
                                    <strong>GHS <?php echo number_format($itemSubtotal, 2); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-totals">
                            <div class="summary-line">
                                <span>Subtotal (<?php echo (int)$totals['count']; ?> item<?php echo (int)$totals['count'] != 1 ? 's' : ''; ?>)</span>
                                <strong id="checkoutSubtotal">GHS <?php echo number_format($totals['subtotal'], 2); ?></strong>
                            </div>
                            <div class="summary-line">
                                <span>Shipping</span>
                                <strong>GHS 20.00</strong>
                            </div>
                            <div class="summary-line">
                                <span>Tax</span>
                                <strong>GHS <?php echo number_format($totals['subtotal'] * 0.1, 2); ?></strong>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-line summary-total">
                                <strong>Total</strong>
                                <strong id="checkoutTotal">GHS <?php echo number_format($totals['subtotal'] + 20 + ($totals['subtotal'] * 0.1), 2); ?></strong>
                            </div>
                        </div>
                        <p class="summary-note">Shipping and taxes calculated at checkout</p>
                    </section>

                    <section class="checkout-actions">
                        <div class="payment-card">
                            <h2><i class="fas fa-shield-check"></i> Secure Payment</h2>
                            <p>Click the button below to proceed to Paystack secure payment gateway. Your payment will be processed securely.</p>

            <div class="payment-method">
                <label for="checkoutCurrency"><i class="fas fa-globe"></i> Currency</label>
                <select id="checkoutCurrency" class="styled-select" disabled>
                    <option value="GHS" selected>GHS - Ghana Cedi</option>
                </select>
            </div>

            <button class="btn btn-primary btn-large" id="simulatePaymentBtn" data-total-amount="<?php echo $totals['subtotal'] + 20 + ($totals['subtotal'] * 0.1); ?>"<?php echo ($totals['subtotal'] <= 0) ? ' disabled' : ''; ?>>
                <i class="fas fa-lock"></i> PROCEED TO PAYMENT
            </button>
                            <a href="cart.php" class="btn btn-secondary btn-large">
                                <i class="fas fa-arrow-left"></i> BACK TO CART
                            </a>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
        </div>
    </footer>

    <div id="cartToastContainer" class="toast-container" aria-live="assertive" aria-atomic="true"></div>

    <div class="modal-backdrop" id="paymentModal" hidden>
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
            <button class="modal-close" type="button" aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2 id="paymentModalTitle"><i class="fas fa-lock"></i> Secure Payment via Paystack</h2>
            </div>
            <div class="modal-body">
                <p>You will be redirected to Paystack's secure payment gateway to complete your payment of <strong id="modalAmount"></strong>.</p>
                <p>Your payment is processed securely by Paystack. After payment, you'll be redirected back to confirm your order.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" id="cancelPaymentBtn">
                    <i class="fas fa-times-circle"></i> Cancel
                </button>
                <button class="btn btn-primary" type="button" id="confirmPaymentBtn">
                    <i class="fas fa-lock"></i> Pay Now
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
