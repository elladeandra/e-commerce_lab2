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
    <title>Lumé - All Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/product_search.js" defer></script>
    <script src="../js/cart.js" defer></script>
</head>
<body>

  <!-- HEADER (same as landing page) -->
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
            <span class="cart-count">0</span>
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

    <!-- SECTION 3: FILTERS BAR -->
    <div class="filters-bar">
      <div class="filters-content">
        <div class="filter-group">
          <span class="filter-label">Category:</span>
          <form method="GET" action="all_product.php" style="flex: 1;">
            <select name="category" class="filter-select" id="category-filter" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['cat_id']; ?>" 
                        <?php echo (isset($selected_category) && $selected_category == $category['cat_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($category['cat_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        
        <div class="filter-group">
          <span class="filter-label">Brand:</span>
          <form method="GET" action="all_product.php" style="flex: 1;">
            <select name="brand" class="filter-select" id="brand-filter" onchange="this.form.submit()">
              <option value="">All Brands</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand['brand_id']; ?>" 
                        <?php echo (isset($selected_brand) && $selected_brand == $brand['brand_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($brand['brand_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <a href="all_product.php" class="search-filters-btn">Apply Filters</a>
      </div>
    </div>
  </header>

  <!-- PAGE HEADER SECTION -->
  <section class="page-header-section">
    <div class="container">
      <h1 class="page-title">All Products</h1>
      <p class="page-subtitle">Browse our complete collection of products</p>
      <p class="products-count">Showing <span id="product-count"><?php echo count($paginated_products ?? []); ?></span> of <?php echo $total_products ?? 0; ?> products</p>
    </div>
  </section>

  <!-- PRODUCTS GRID SECTION -->
  <section class="products-section">
    <div class="container">
      
      <!-- PRODUCT GRID - 4 COLUMNS ON DESKTOP -->
      <?php if (empty($paginated_products)): ?>
        <div class="no-products">
          <i class="fas fa-box-open"></i>
          <h3>No Products Found</h3>
          <p>Try adjusting your filters or check back later for new products!</p>
          <a href="all_product.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> View All Products
          </a>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($paginated_products as $product): ?>
            <!-- SINGLE PRODUCT CARD -->
            <div class="product-card">
              <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                <!-- Product Image Container -->
                <div class="product-image-container">
                  <!-- Sale Badge (optional - can be added based on product data) -->
                  <?php if (false): // Add condition for sale items ?>
                    <span class="sale-badge">30% OFF</span>
                  <?php endif; ?>
                  
                  <!-- Product Image -->
                  <?php if (!empty($product['product_image'])): ?>
                    <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_title']); ?>" 
                         class="product-image"
                         onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #FFE5EC, #E5DEFF)';">
                  <?php else: ?>
                    <div class="product-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.875rem;">
                      No Image
                    </div>
                  <?php endif; ?>
                  
                  <!-- Quick Add Button (appears on hover) -->
                  <button class="quick-add-btn" 
                          type="button"
                          onclick="openSizeModal(event, 
                            <?php echo $product['product_id']; ?>, 
                            '<?php echo htmlspecialchars(addslashes($product['product_title'])); ?>', 
                            '$<?php echo number_format($product['product_price'], 2); ?>', 
                            '<?php echo !empty($product['product_image']) ? '../' . htmlspecialchars($product['product_image']) : ''; ?>')">
                    Quick Add
                  </button>
                </div>
                
                <!-- Product Info -->
                <div class="product-info">
                  <p class="product-category"><?php echo htmlspecialchars($product['cat_name'] ?? 'Product'); ?></p>
                  <h3 class="product-name"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                  <div class="product-pricing">
                    <span class="product-price">$<?php echo number_format($product['product_price'], 2); ?></span>
                    <!-- If on sale, show original price too -->
                    <!-- <span class="product-price-original">$<?php echo number_format($product['product_price'] * 1.5, 2); ?></span> -->
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- END OF PRODUCTS GRID -->

        <!-- Pagination -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
          <div class="pagination">
            <?php if ($current_page > 1): ?>
              <a href="?page=<?php echo ($current_page - 1); ?><?php echo isset($selected_category) && $selected_category ? '&category='.$selected_category : ''; ?><?php echo isset($selected_brand) && $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                <i class="fas fa-chevron-left"></i> Previous
              </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <?php if ($i == $current_page): ?>
                <span class="pagination-btn active"><?php echo $i; ?></span>
              <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($selected_category) && $selected_category ? '&category='.$selected_category : ''; ?><?php echo isset($selected_brand) && $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                  <?php echo $i; ?>
                </a>
              <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
              <a href="?page=<?php echo ($current_page + 1); ?><?php echo isset($selected_category) && $selected_category ? '&category='.$selected_category : ''; ?><?php echo isset($selected_brand) && $selected_brand ? '&brand='.$selected_brand : ''; ?>" class="pagination-btn">
                Next <i class="fas fa-chevron-right"></i>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
    </div>
  </footer>

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
        <a href="#contact" class="size-guide-link" onclick="closeSizeModal();">📏 View Size Guide</a>

        <!-- Add to Cart Button -->
        <button class="modal-add-to-cart" id="addToCartBtn" disabled>
          Add to Cart
        </button>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let selectedSize = null;
    let currentProductId = null;

    // Open Size Modal
    function openSizeModal(event, productId, productName, productPrice, productImage) {
      event.preventDefault();
      event.stopPropagation();
      
      // Store current product ID
      currentProductId = productId;
      
      // Update modal content
      const modalImage = document.getElementById('modalProductImage');
      if (productImage && productImage.trim() !== '') {
        modalImage.src = productImage;
        modalImage.style.display = 'block';
      } else {
        modalImage.style.display = 'none';
      }
      modalImage.alt = productName;
      document.getElementById('modalProductName').textContent = productName;
      document.getElementById('modalProductPrice').textContent = productPrice;
      
      // Reset size selection
      selectedSize = null;
      document.querySelectorAll('.size-option').forEach(btn => {
        btn.classList.remove('selected');
        btn.classList.remove('out-of-stock');
      });
      document.getElementById('addToCartBtn').disabled = true;
      
      // Show modal
      document.getElementById('sizeModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    // Close Size Modal
    function closeSizeModal() {
      document.getElementById('sizeModal').classList.remove('active');
      document.body.style.overflow = '';
      selectedSize = null;
      currentProductId = null;
    }

    // Close modal when clicking outside
    document.getElementById('sizeModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeSizeModal();
      }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeSizeModal();
      }
    });

    // Size Selection
    document.querySelectorAll('.size-option').forEach(button => {
      button.addEventListener('click', function() {
        // Don't allow selection of out of stock items
        if (this.classList.contains('out-of-stock')) {
          return;
        }
        
        // Remove selected class from all buttons
        document.querySelectorAll('.size-option').forEach(btn => {
          btn.classList.remove('selected');
        });
        
        // Add selected class to clicked button
        this.classList.add('selected');
        selectedSize = this.getAttribute('data-size');
        
        // Enable add to cart button
        document.getElementById('addToCartBtn').disabled = false;
      });
    });

    // Add to Cart Function
    document.getElementById('addToCartBtn').addEventListener('click', function() {
      if (!selectedSize || !currentProductId) {
        return;
      }
      
      // Create cart item data
      const cartItem = {
        productId: currentProductId,
        size: selectedSize,
        quantity: 1,
        timestamp: new Date().toISOString()
      };
      
      // Send to server via AJAX
      fetch('../actions/add_to_cart_with_size_action.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(cartItem)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Close modal
          closeSizeModal();
          
          // Show success notification
          showToast('Added to Cart!', `Size ${selectedSize} added to your cart`);
          
          // Update cart count in header
          updateCartCount();
        } else {
          alert('Error adding item to cart. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding item to cart. Please try again.');
      });
    });

    // Show Toast Notification
    function showToast(title, message) {
      // Remove existing toast if any
      const existingToast = document.querySelector('.toast-notification');
      if (existingToast) {
        existingToast.remove();
      }
      
      // Create toast element
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
      
      // Add to body
      document.body.appendChild(toast);
      
      // Show toast
      setTimeout(() => {
        toast.classList.add('show');
      }, 100);
      
      // Auto remove after 3 seconds
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
          toast.remove();
        }, 300);
      }, 3000);
    }

    // Update Cart Count
    function updateCartCount() {
      fetch('../actions/get_cart_count_action.php')
        .then(response => response.json())
        .then(data => {
          const cartCountElement = document.querySelector('.cart-count');
          if (cartCountElement && data.count !== undefined) {
            cartCountElement.textContent = data.count;
          }
        })
        .catch(error => {
          console.error('Error updating cart count:', error);
        });
    }

    // Update product count dynamically
    document.addEventListener('DOMContentLoaded', function() {
      const productCount = document.querySelectorAll('.product-card').length;
      const countElement = document.getElementById('product-count');
      if (countElement) {
        countElement.textContent = productCount;
      }
      
      // Load cart count on page load
      updateCartCount();
    });
  </script>

</body>
</html>
