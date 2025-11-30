<?php
session_start();

require_once dirname(__FILE__) . '/../controllers/cart_controller.php';
require_once dirname(__FILE__) . '/../controllers/product_controller.php';
require_once dirname(__FILE__) . '/../controllers/category_controller.php';
require_once dirname(__FILE__) . '/../controllers/brand_controller.php';

// ALWAYS use session-based cart for consistency
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

// Get products for "Explore More" section
$all_products = view_all_products_ctr();
$explore_products = array_slice($all_products, 0, 3);

// Get categories and brands for header
$categories = get_categories_ctr();
$brands = get_brands_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bag - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/cart.js" defer></script>
</head>
<body class="cart-page">
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

    <main class="cart-main">
        <div class="container">
            <?php if (empty($cart_items)): ?>
                <!-- SKIMS-STYLE EMPTY CART -->
                <div class="empty-cart-container">
                    <div class="empty-cart-header">
                        <h1 class="empty-cart-title">MY BAG</h1>
                        <button class="close-cart-btn" onclick="window.history.back();">✕</button>
                    </div>
                    
                    <div class="empty-cart-message">
                        <p class="empty-cart-oops">Oops...</p>
                        <p class="empty-cart-text">You have no items in your bag</p>
                        <a href="all_product.php" class="shop-best-sellers-btn">SHOP BEST SELLERS</a>
                    </div>

                    <div class="explore-more-section">
                        <div class="explore-divider"></div>
                        <h2 class="explore-title">EXPLORE MORE</h2>
                        <div class="explore-products-grid">
                            <?php foreach ($explore_products as $product): ?>
                                <div class="explore-product-card">
                                    <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="explore-product-link">
                                        <div class="explore-product-image-container">
                                            <?php if (!empty($product['product_image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>" 
                                                     class="explore-product-image">
                                            <?php else: ?>
                                                <div class="explore-product-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999;">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="explore-product-info">
                                            <p class="explore-product-category"><?php echo htmlspecialchars($product['cat_name'] ?? 'Product'); ?></p>
                                            <h3 class="explore-product-name"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                                            <p class="explore-product-price">GHS <?php echo number_format($product['product_price'], 2); ?></p>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- CART WITH ITEMS -->
                <div class="section-head">
                    <h1><i class="fas fa-shopping-basket"></i> Your Shopping Cart</h1>
                    <p>Review your selected products, update quantities, or proceed to a smooth checkout.</p>
                </div>

                <div id="cartFeedback" class="feedback-banner" role="alert" hidden></div>
                
                <?php if (isset($_GET['success']) && $_GET['success'] === 'removed'): ?>
                    <div class="feedback-banner" style="background: #10b981; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                        ✓ Item removed from cart successfully!
                    </div>
                <?php endif; ?>

                <div class="cart-layout" data-cart-summary='<?php echo json_encode($totals); ?>'>
                    <section class="cart-items" aria-label="Cart items">
                        <?php foreach ($cart_items as $item): 
                            $cartKey = isset($item['cart_key']) ? $item['cart_key'] : 'product_' . $item['product_id'] . '_' . ($item['size'] ?? 'M');
                            $itemSubtotal = $item['product_price'] * ($item['qty'] ?? $item['quantity'] ?? 1);
                        ?>
                            <article class="cart-item" data-product-id="<?php echo $item['product_id']; ?>" data-cart-key="<?php echo htmlspecialchars($cartKey); ?>">
                                <!-- Product Image -->
                                <div class="cart-item-image-wrapper">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_title']); ?>" 
                                             class="cart-item-image">
                                    <?php else: ?>
                                        <div class="cart-item-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Product Details -->
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name"><?php echo htmlspecialchars($item['product_title']); ?></h3>
                                    <p class="cart-item-price">GHS <?php echo number_format($item['product_price'], 2); ?></p>
                                    <p class="cart-item-meta">
                                        <?php echo htmlspecialchars($item['cat_name'] ?? $item['product_category'] ?? 'Product'); ?> • 
                                        <?php if (isset($item['size'])): ?>
                                            Size: <?php echo htmlspecialchars($item['size']); ?>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="cart-item-quantity">
                                        <label>Quantity:</label>
                                        <div class="quantity-controls">
                                            <button class="qty-btn qty-decrease" 
                                                    type="button" 
                                                    aria-label="Decrease quantity"
                                                    onclick="updateQuantity('<?php echo htmlspecialchars($cartKey); ?>', -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="qty-input cart-qty-input" 
                                                   value="<?php echo (int)($item['qty'] ?? $item['quantity'] ?? 1); ?>" 
                                                   min="1" 
                                                   aria-label="Quantity"
                                                   data-unit-price="<?php echo htmlspecialchars($item['product_price']); ?>"
                                                   onchange="setQuantity('<?php echo htmlspecialchars($cartKey); ?>', this.value)">
                                            <button class="qty-btn qty-increase" 
                                                    type="button" 
                                                    aria-label="Increase quantity"
                                                    onclick="updateQuantity('<?php echo htmlspecialchars($cartKey); ?>', 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Item Subtotal -->
                                    <div class="cart-item-subtotal">
                                        <span>Subtotal:</span>
                                        <strong class="subtotal-value" data-subtotal="<?php echo $itemSubtotal; ?>">
                                            GHS <?php echo number_format($itemSubtotal, 2); ?>
                                        </strong>
                                    </div>
                                </div>

                                <!-- Remove Button - Multiple Methods for Reliability -->
                                <div class="cart-item-remove-wrapper">
                                    <!-- Primary: AJAX Remove Button -->
                                    <button class="cart-item-remove remove-item-btn" 
                                            type="button" 
                                            aria-label="Remove item"
                                            onclick="removeFromCart('<?php echo htmlspecialchars($cartKey); ?>')"
                                            title="Click to remove this item">
                                        <span class="remove-icon">✖</span>
                                        <span class="remove-text">Remove</span>
                                    </button>
                                    
                                    <!-- Fallback: Direct Link (if AJAX fails, user can right-click and open in new tab) -->
                                    <a href="../actions/force-remove-cart.php?cartKey=<?php echo urlencode($cartKey); ?>" 
                                       class="remove-link-fallback"
                                       style="display: none;"
                                       onclick="return confirm('Remove this item from your cart?')">
                                        Force Remove
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <aside class="cart-summary">
                        <div class="summary-card">
                            <h2 class="summary-title">Order Summary</h2>
                            <div class="summary-lines">
                                <div class="summary-line">
                                    <span>Subtotal (<?php echo (int)$totals['count']; ?> item<?php echo (int)$totals['count'] != 1 ? 's' : ''; ?>)</span>
                                    <span id="summarySubtotal">GHS <?php echo number_format($totals['subtotal'], 2); ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>Shipping</span>
                                    <span>GHS 20.00</span>
                                </div>
                                <div class="summary-line">
                                    <span>Tax</span>
                                    <span id="summaryTax">GHS <?php echo number_format($totals['subtotal'] * 0.1, 2); ?></span>
                                </div>
                                <hr class="summary-divider">
                                <div class="summary-line summary-total">
                                    <strong>Total</strong>
                                    <strong id="summaryTotal">GHS <?php echo number_format($totals['subtotal'] + 20 + ($totals['subtotal'] * 0.1), 2); ?></strong>
                                </div>
                            </div>
                            <p class="summary-note">Shipping and taxes calculated at checkout</p>

                            <div class="summary-actions">
                                <a href="checkout.php" class="btn btn-primary btn-large proceed-checkout">
                                    <i class="fas fa-lock"></i> Proceed to Checkout
                                </a>
                                <a href="all_product.php" class="btn btn-secondary btn-large">
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
            <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
        </div>
    </footer>

    <div id="cartToastContainer" class="toast-container" aria-live="assertive" aria-atomic="true"></div>

    <script>
    // Enable detailed logging
    const DEBUG = true;

    function log(...args) {
      if (DEBUG) {
        console.log('[CART DEBUG]', ...args);
      }
    }

    // ============================================================
    // REMOVE FROM CART
    // ============================================================

    function removeFromCart(cartKey) {
      log('Remove from cart called with key:', cartKey);
      
      if (!confirm('Remove this item from your cart?')) {
        return;
      }

      // Show loading state
      const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
      if (cartItem) {
        cartItem.style.opacity = '0.5';
        cartItem.style.pointerEvents = 'none';
      }

      log('Sending remove request for cart key:', cartKey);

      // Try multiple endpoints for reliability
      const endpoints = [
        '../actions/remove-from-cart-simple.php',
        'actions/remove-from-cart-simple.php',
        '../actions/remove_from_cart_action.php'
      ];
      
      let requestMade = false;
      
      // Try first endpoint
      fetch(endpoints[0], {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cartKey: cartKey })
      })
      .then(response => {
        log('Remove response status:', response.status);
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.text();
      })
      .then(text => {
        log('Remove response text:', text);
        try {
          const data = JSON.parse(text);
          log('Parsed remove response:', data);
          
          if (data.success) {
            // ALWAYS reload page to ensure session is synced
            log('Removal successful, reloading page to sync session');
            log('Debug info:', data.debug);
            
            // Show toast briefly
            showToast('Item Removed', 'Product removed from cart');
            
            // Reload page immediately to sync with server session
            window.location.reload();
            return;
          } else {
            log('Error from server:', data);
            alert('Error: ' + (data.error || 'Unknown error') + '\n\nDebug: ' + JSON.stringify(data.debug || {}));
            if (cartItem) {
              cartItem.style.opacity = '1';
              cartItem.style.pointerEvents = 'auto';
            }
          }
        } catch (e) {
          log('JSON parse error:', e);
          log('Raw response:', text);
          
          // If JSON parse fails, try direct reload anyway
          log('Attempting page reload despite parse error');
          setTimeout(() => {
            window.location.reload();
          }, 500);
        }
      })
      .catch(error => {
        log('Fetch error:', error);
        
        // Fallback: Use form submission
        log('Trying form-based removal as fallback');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '../actions/cart-action.php';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'remove';
        
        const keyInput = document.createElement('input');
        keyInput.type = 'hidden';
        keyInput.name = 'cartKey';
        keyInput.value = cartKey;
        
        form.appendChild(actionInput);
        form.appendChild(keyInput);
        document.body.appendChild(form);
        form.submit();
      });
    }

    // ============================================================
    // UPDATE QUANTITY
    // ============================================================

    function updateQuantity(cartKey, change) {
      const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
      if (!cartItem) return;

      const input = cartItem.querySelector('.qty-input');
      if (!input) return;

      let newQuantity = parseInt(input.value) + change;
      if (newQuantity < 1) newQuantity = 1;

      setQuantity(cartKey, newQuantity);
    }

    function setQuantity(cartKey, quantity) {
      quantity = parseInt(quantity);
      if (quantity < 1) quantity = 1;

      const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
      if (!cartItem) return;

      const input = cartItem.querySelector('.qty-input');
      if (input) {
        input.value = quantity;
        input.disabled = true;
      }

      fetch('../actions/update_cart_quantity_action.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          cartKey: cartKey,
          quantity: quantity
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update item subtotal
          const subtotalElement = cartItem.querySelector('.cart-item-subtotal strong');
          if (subtotalElement) {
            subtotalElement.textContent = 'GHS ' + data.itemSubtotal;
          }

          // Update cart totals
          updateCartTotals(data);

          // Update cart count
          updateCartCountDisplay(data.cartCount);

          if (input) {
            input.disabled = false;
          }
        } else {
          alert('Error: ' + data.error);
          if (input) {
            input.disabled = false;
            input.value = quantity - 1;
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating quantity');
        if (input) {
          input.disabled = false;
        }
      });
    }

    // ============================================================
    // UPDATE CART TOTALS IN SUMMARY
    // ============================================================

    function updateCartTotals(data = null) {
      if (data) {
        // Update from server response
        const subtotalElement = document.getElementById('summarySubtotal');
        const taxElement = document.getElementById('summaryTax');
        const totalElement = document.getElementById('summaryTotal');

        if (subtotalElement) subtotalElement.textContent = 'GHS ' + data.cartSubtotal;
        if (taxElement) taxElement.textContent = 'GHS ' + data.cartTax;
        if (totalElement) totalElement.textContent = 'GHS ' + data.cartTotal;
      } else {
        // Recalculate from page
        let subtotal = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
          const priceText = item.querySelector('.cart-item-price').textContent;
          const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
          const quantity = parseInt(item.querySelector('.qty-input').value);
          subtotal += price * quantity;
        });

        const shipping = 20.00;
        const tax = subtotal * 0.1;
        const total = subtotal + shipping + tax;

        const subtotalElement = document.getElementById('summarySubtotal');
        const taxElement = document.getElementById('summaryTax');
        const totalElement = document.getElementById('summaryTotal');

        if (subtotalElement) subtotalElement.textContent = 'GHS ' + subtotal.toFixed(2);
        if (taxElement) taxElement.textContent = 'GHS ' + tax.toFixed(2);
        if (totalElement) totalElement.textContent = 'GHS ' + total.toFixed(2);
      }
    }

    // ============================================================
    // UPDATE CART COUNT IN HEADER
    // ============================================================

    function updateCartCountDisplay(count) {
      const cartCountElement = document.querySelector('.cart-count');
      if (cartCountElement) {
        cartCountElement.textContent = count;
        
        // Animate update
        cartCountElement.style.transform = 'scale(1.3)';
        cartCountElement.style.transition = 'transform 0.2s ease';
        setTimeout(() => {
          cartCountElement.style.transform = 'scale(1)';
        }, 200);
      }
    }

    // ============================================================
    // SHOW TOAST NOTIFICATION
    // ============================================================

    function showToast(title, message) {
      log('Showing toast:', title, message);
      
      // Remove existing toast
      const existingToast = document.querySelector('.toast-notification');
      if (existingToast) {
        existingToast.remove();
      }

      // Create toast
      const toast = document.createElement('div');
      toast.className = 'toast-notification';
      toast.innerHTML = `
        <div class="toast-icon">✓</div>
        <div class="toast-message">
          <div class="toast-title">${title}</div>
          <div class="toast-text">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
      `;

      document.body.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('show');
      }, 100);

      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
          toast.remove();
        }, 300);
      }, 3000);
    }

    // ============================================================
    // INITIALIZE ON PAGE LOAD
    // ============================================================

    document.addEventListener('DOMContentLoaded', function() {
      log('Cart script loaded successfully');
      log('Current page:', window.location.pathname);
      
      // Add smooth transition to cart items
      const cartItems = document.querySelectorAll('.cart-item');
      log('Found cart items:', cartItems.length);
      cartItems.forEach(item => {
        item.style.transition = 'all 0.3s ease';
      });

      // Prevent form submission on quantity input
      const qtyInputs = document.querySelectorAll('.qty-input');
      qtyInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            const cartKey = this.closest('.cart-item').getAttribute('data-cart-key');
            log('Enter pressed, updating quantity for:', cartKey);
            setQuantity(cartKey, this.value);
          }
        });
      });
    });
    </script>
</body>
</html>
