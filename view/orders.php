<?php
session_start();
require_once dirname(__FILE__) . '/../settings/core.php';
require_once dirname(__FILE__) . '/../controllers/order_controller.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$customer_id = getLoggedInUserId();
$orders = get_customer_orders_ctr($customer_id);

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
    <title>My Orders - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #FFE5EC 0%, #E5DEFF 100%);
            min-height: 100vh;
        }
        
        .orders-page-content {
            padding: 4rem 0;
            min-height: calc(100vh - 200px);
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 2rem; 
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            font-size: 1.125rem;
            color: #666;
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .order-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #E8E8E8;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(255, 107, 157, 0.15);
            border-color: #FF6B9D;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #E8E8E8;
        }
        
        .order-info {
            flex: 1;
        }
        
        .order-id {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        
        .order-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.9375rem;
            color: #666;
        }
        
        .order-status {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .order-status.paid {
            background: #D1FAE5;
            color: #065F46;
            border: 2px solid #6EE7B7;
        }
        
        .order-status.processing {
            background: #DBEAFE;
            color: #1E40AF;
            border: 2px solid #60A5FA;
        }
        
        .order-status.completed {
            background: #E0E7FF;
            color: #3730A3;
            border: 2px solid #818CF8;
        }
        
        .order-details {
            margin-top: 1.5rem;
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #E8E8E8;
        }
        
        .total-label {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1A1A1A;
        }
        
        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FF6B9D;
        }
        
        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF6B9D, #9D7FEA);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 157, 0.4);
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
        
        .empty-orders {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #999;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        
        .empty-text {
            font-size: 1rem;
            color: #666;
            margin-bottom: 2rem;
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

    <main class="orders-page-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Orders</h1>
                <p class="page-subtitle">View all your past and current orders</p>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <div class="empty-icon">📦</div>
                    <h2 class="empty-title">No Orders Yet</h2>
                    <p class="empty-text">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                    <a href="all_product.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): 
                        $order_id = $order['order_id'];
                        $invoice_no = $order['invoice_no'];
                        $order_date = $order['order_date'];
                        $order_status = strtolower($order['order_status'] ?? 'processing');
                        $payment_amount = $order['payment_amount'] ?? 0;
                        $currency = $order['currency'] ?? 'GHS';
                        
                        // Get order items
                        $order_items = get_order_details_ctr($order_id);
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-id">Order #<?php echo htmlspecialchars($invoice_no); ?></div>
                                    <div class="order-meta">
                                        <span><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order_date)); ?></span>
                                        <span><strong>Order ID:</strong> <?php echo $order_id; ?></span>
                                    </div>
                                </div>
                                <div class="order-status <?php echo htmlspecialchars($order_status); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order_status)); ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($order_items)): ?>
                                <div class="order-details">
                                    <h3 style="font-size: 1.125rem; font-weight: 600; color: #1A1A1A; margin-bottom: 1rem;">Items:</h3>
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        <?php foreach ($order_items as $item): 
                                            $item_name = htmlspecialchars($item['product_title'] ?? 'Product');
                                            $item_qty = intval($item['qty'] ?? 1);
                                            $item_price = floatval($item['unit_price'] ?? 0);
                                        ?>
                                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #F5F5F5;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span><?php echo $item_name; ?> × <?php echo $item_qty; ?></span>
                                                    <strong style="color: #FF6B9D;"><?php echo $currency; ?> <?php echo number_format($item_price * $item_qty, 2); ?></strong>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="order-total">
                                <span class="total-label">Total Amount</span>
                                <span class="total-amount"><?php echo $currency; ?> <?php echo number_format($payment_amount, 2); ?></span>
                            </div>
                            
                            <div class="order-actions">
                                <a href="order_details.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">View Details</a>
                                <a href="all_product.php" class="btn btn-secondary">Continue Shopping</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
        </div>
    </footer>
</body>
</html>

