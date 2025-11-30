<?php
session_start();
require_once dirname(__FILE__) . '/../settings/core.php';
require_once dirname(__FILE__) . '/../controllers/order_controller.php';
require_once dirname(__FILE__) . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$customer_id = getLoggedInUserId();
$invoice_no = isset($_GET['invoice']) ? htmlspecialchars($_GET['invoice']) : '';
$reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : '';
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Get order details if order_id is provided
$order_items = [];
$order_total = 0;
$order_info = null;

if ($order_id) {
    $order_items = get_order_details_ctr($order_id);
    if ($order_items && is_array($order_items)) {
        // get_order_details_ctr returns array of items (fetchAll)
        // Calculate total from order items
        foreach ($order_items as $item) {
            $qty = intval($item['qty'] ?? 1);
            $price = floatval($item['unit_price'] ?? $item['product_price'] ?? 0);
            $order_total += $price * $qty;
        }
    }
}

// If no order_id, try to get latest order by invoice_no
if (empty($order_items) && $invoice_no) {
    $customer_orders = get_customer_orders_ctr($customer_id);
    foreach ($customer_orders as $order) {
        // Compare as integers since invoice_no is int in database
        $order_invoice = intval($order['invoice_no']);
        $search_invoice = intval($invoice_no);
        if ($order_invoice == $search_invoice) {
            $order_id = $order['order_id'];
            $order_items = get_order_details_ctr($order_id);
            if ($order_items && is_array($order_items)) {
                foreach ($order_items as $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $price = floatval($item['unit_price'] ?? $item['product_price'] ?? 0);
                    $order_total += $price * $qty;
                }
                break;
            }
        }
    }
}

// Add shipping and tax
$shipping = 20.00;
$tax = $order_total * 0.1;
$grand_total = $order_total + $shipping + $tax;

// Cart should be empty after successful payment
// Force clear cart as safety measure (cart should already be cleared in payment verification)
$_SESSION['cart'] = [];
[$customer_id_db, $ip_address] = cart_context();
empty_cart_ctr($customer_id_db, $ip_address);
$cart_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #FFE5EC 0%, #E5DEFF 100%);
            min-height: 100vh;
        }
        
        .success-page-content {
            padding: 4rem 0;
            min-height: calc(100vh - 200px);
        }
        
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 0 2rem; 
        }
        
        .success-box { 
            background: white;
            border: 2px solid #6ee7b7; 
            border-radius: 24px; 
            padding: 50px 40px; 
            text-align: center;
            box-shadow: 0 8px 30px rgba(255, 107, 157, 0.1);
        }
        
        .success-icon { 
            font-size: 80px; 
            margin-bottom: 20px; 
            animation: bounce 1s ease-in-out;
            color: #059669;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        h1 { 
            font-size: 3rem; 
            font-weight: 700;
            color: #065f46; 
            margin-bottom: 10px; 
        }
        
        .subtitle { 
            font-size: 1.125rem; 
            color: #047857; 
            margin-bottom: 30px; 
        }
        
        .order-details { 
            background: #FAFAFA; 
            padding: 30px; 
            border-radius: 16px; 
            margin: 30px 0; 
            text-align: left;
            border: 1px solid #E8E8E8;
        }
        
        .detail-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 12px 0; 
            border-bottom: 1px solid #E8E8E8;
            color: #374151;
        }
        
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #1A1A1A; }
        .detail-value { color: #666; word-break: break-all; }
        
        .order-items-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #E8E8E8;
        }
        
        .order-items-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #E8E8E8;
        }
        
        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        
        .order-item-meta {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .order-item-price {
            font-weight: 700;
            color: #FF6B9D;
        }
        
        .order-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #E8E8E8;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 1rem;
        }
        
        .total-row.grand-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #FF6B9D;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #E8E8E8;
        }
        
        .btn { 
            padding: 1rem 2rem; 
            border: none; 
            border-radius: 50px; 
            font-size: 1rem; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            text-decoration: none; 
            display: inline-block;
            margin: 0 10px;
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, #FF6B9D, #9D7FEA); 
            color: white; 
            box-shadow: 0 8px 25px rgba(255, 107, 157, 0.3); 
        }
        
        .btn-primary:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 12px 35px rgba(255, 107, 157, 0.4); 
        }
        
        .btn-secondary { 
            background: white; 
            color: #374151; 
            border: 2px solid #E8E8E8; 
        }
        
        .btn-secondary:hover { 
            background: #FAFAFA;
            border-color: #FF6B9D;
        }
        
        .buttons-container { 
            display: flex; 
            justify-content: center; 
            margin-top: 40px; 
            flex-wrap: wrap;
        }
        
        .confirmation-message { 
            background: #eff6ff; 
            border: 2px solid #3b82f6; 
            padding: 20px; 
            border-radius: 12px; 
            color: #1e40af;
            margin-bottom: 20px;
        }
        
        .empty-value {
            color: #999;
            font-style: italic;
        }
        
        .no-items-message {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-style: italic;
        }
    </style>
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

    <main class="success-page-content">
        <div class="container">
            <div class="success-box">
                <div class="success-icon">✓</div>
                <h1>Order Successful!</h1>
                <p class="subtitle">Your payment has been processed successfully</p>
                
                <div class="confirmation-message">
                    <strong>✓ Payment Confirmed</strong><br>
                    Thank you for your purchase! Your order has been confirmed and will be processed shortly.
                </div>
                
                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Invoice Number</span>
                        <span class="detail-value"><?php echo $invoice_no ?: '<span class="empty-value">Processing...</span>'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Reference</span>
                        <span class="detail-value"><?php echo $reference ?: '<span class="empty-value">N/A</span>'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Date</span>
                        <span class="detail-value"><?php echo date('F j, Y'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value" style="color: #059669; font-weight: 600;">Paid ✓</span>
                    </div>
                    
                    <!-- ORDER ITEMS SECTION -->
                    <?php if (!empty($order_items)): ?>
                        <div class="order-items-section">
                            <h3 class="order-items-title">Items Purchased</h3>
                            <?php foreach ($order_items as $item): 
                                $item_image = '../' . ($item['product_image'] ?? '');
                                $item_name = htmlspecialchars($item['product_title'] ?? 'Product');
                                $item_qty = intval($item['qty'] ?? 1);
                                $item_price = floatval($item['unit_price'] ?? $item['product_price'] ?? 0);
                                $item_subtotal = $item_price * $item_qty;
                            ?>
                                <div class="order-item">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item_image); ?>" 
                                             alt="<?php echo $item_name; ?>" 
                                             class="order-item-image"
                                             onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div class="order-item-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.75rem;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                    <div class="order-item-info">
                                        <div class="order-item-name"><?php echo $item_name; ?></div>
                                        <div class="order-item-meta">Quantity: <?php echo $item_qty; ?></div>
                                        <div class="order-item-price">GHS <?php echo number_format($item_subtotal, 2); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- ORDER TOTALS -->
                            <div class="order-totals">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <strong>GHS <?php echo number_format($order_total, 2); ?></strong>
                                </div>
                                <div class="total-row">
                                    <span>Shipping</span>
                                    <strong>GHS <?php echo number_format($shipping, 2); ?></strong>
                                </div>
                                <div class="total-row">
                                    <span>Tax (10%)</span>
                                    <strong>GHS <?php echo number_format($tax, 2); ?></strong>
                                </div>
                                <div class="total-row grand-total">
                                    <span>Total</span>
                                    <strong>GHS <?php echo number_format($grand_total, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="order-items-section">
                            <p class="no-items-message">Order items are being processed. Please check your order history for details.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="buttons-container">
                    <a href="all_product.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                    <?php if ($order_id): ?>
                        <a href="order_details.php?order_id=<?php echo $order_id; ?>" class="btn btn-secondary">View Order Details</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
        </div>
    </footer>
</body>
</html>
