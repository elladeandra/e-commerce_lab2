<?php
session_start();
require_once dirname(__FILE__).'/controllers/category_controller.php';
require_once dirname(__FILE__).'/controllers/brand_controller.php';
require_once dirname(__FILE__).'/controllers/product_controller.php';

$categories = get_categories_ctr();
$brands = get_brands_ctr();
$products = get_products_ctr();
// Get first 4 products for featured section
$featured_products = array_slice($products, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumé - Premium Activewear</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="fontawesome/css/all.min.css">
  <script src="js/product_search.js" defer></script>
  <script src="js/cart.js" defer></script>
</head>
<body>

  <!-- HEADER - THREE SECTIONS STACKED VERTICALLY -->
  <header class="site-header">
    
    <!-- SECTION 1: MAIN HEADER -->
    <div class="main-header">
      <div class="header-content">
        <a href="index.php" class="brand">LUMÉ</a>
        
        <div class="header-search">
          <form method="GET" action="view/product_search_result.php" style="width: 100%;">
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
              <a href="admin/category.php" class="action-btn btn-register">Category</a>
              <a href="admin/brand.php" class="action-btn btn-register">Brand</a>
              <a href="admin/product.php" class="action-btn btn-register">Add Product</a>
            <?php endif; ?>
            <a href="view/logout.php" class="action-btn btn-login">Logout</a>
          <?php else: ?>
            <a href="view/register.php" class="action-btn btn-register">Register</a>
            <a href="view/login.php" class="action-btn btn-login">👤 Login</a>
          <?php endif; ?>
          <a href="view/cart.php" class="cart-btn">
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
          <a href="view/all_product.php" class="nav-link">Shop</a>
          <a href="view/all_product.php" class="nav-link">Collections</a>
          <a href="#about" class="nav-link">About</a>
          <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <div class="nav-secondary">
          <a href="view/all_product.php" class="secondary-link">All Products</a>
          <a href="#features" class="secondary-link">Features</a>
          <a href="view/checkout.php" class="secondary-link">🔒 Checkout</a>
        </div>
      </div>
    </div>

    <!-- SECTION 3: FILTERS BAR -->
    <div class="filters-bar">
      <div class="filters-content">
        <div class="filter-group">
          <span class="filter-label">Category:</span>
          <form method="GET" action="view/all_product.php" style="flex: 1;">
            <select name="category" class="filter-select" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['cat_id']; ?>">
                  <?php echo htmlspecialchars($category['cat_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        
        <div class="filter-group">
          <span class="filter-label">Brand:</span>
          <form method="GET" action="view/all_product.php" style="flex: 1;">
            <select name="brand" class="filter-select" onchange="this.form.submit()">
              <option value="">All Brands</option>
              <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand['brand_id']; ?>">
                  <?php echo htmlspecialchars($brand['brand_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <a href="view/all_product.php" class="search-filters-btn">Apply Filters</a>
      </div>
    </div>
  </header>

  <!-- HERO BANNER SECTION -->
  <section class="hero-banner">
    <img src="images/hero-image.jpg" alt="Lumé Premium Activewear" class="hero-image">
    <a href="#shop" class="hero-badge">New Collection</a>
  </section>

  <!-- CONTENT SECTION WITH GRADIENT -->
  <section class="content-section">
    <div class="content-wrapper">
      <p class="content-eyebrow">Premium Activewear</p>
      <h1 class="content-title">Move with confidence.<br>Designed for you.</h1>
      <p class="content-subtitle">Premium activewear crafted in Ghana. Inclusive sizing, sustainable materials, and timeless design.</p>
      <div class="content-cta">
        <a href="view/all_product.php" class="cta-btn cta-btn-primary">Shop Now</a>
        <a href="#about" class="cta-btn cta-btn-secondary">Learn Our Story</a>
      </div>
    </div>
  </section>

  <!-- PRODUCTS SECTION -->
  <section class="featured-section" id="shop">
    <div class="container">
      <div class="section-header">
        <p class="section-eyebrow">Shop</p>
        <h2 class="section-title">Essential Pieces</h2>
      </div>
      
      <div class="product-grid">
        <?php if (!empty($featured_products)): ?>
          <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
              <a href="view/single_product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                <div class="product-image-wrapper">
                  <?php if (!empty($product['product_image'])): ?>
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>" class="product-image">
                  <?php else: ?>
                    <div class="product-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999;">
                      <span>No Image</span>
                    </div>
                  <?php endif; ?>
                  <button class="quick-add-btn" 
                          type="button"
                          onclick="openSizeModal(event, 
                            <?php echo $product['product_id']; ?>, 
                            '<?php echo htmlspecialchars(addslashes($product['product_title'])); ?>', 
                            '$<?php echo number_format($product['product_price'], 2); ?>', 
                            '<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : ''; ?>')">
                    Quick Add
                  </button>
                </div>
                <div class="product-info">
                  <p class="product-category"><?php echo htmlspecialchars($product['cat_name'] ?? 'Product'); ?></p>
                  <h3 class="product-title"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                  <p class="product-price">$<?php echo number_format($product['product_price'], 2); ?></p>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="product-card">
            <div class="product-image-wrapper">
              <div class="product-image" style="background: linear-gradient(135deg, #FFE5EC, #E5DEFF); display: flex; align-items: center; justify-content: center; color: #999;">
                <span>Product Image</span>
              </div>
              <button class="quick-add-btn" type="button" onclick="openSizeModal(event, 0, 'Core Leggings', 'GHS 180.00', '')">Quick Add</button>
            </div>
            <div class="product-info">
              <p class="product-category">Leggings</p>
              <h3 class="product-title">Core Leggings</h3>
              <p class="product-price">GHS 180</p>
            </div>
          </div>
          
          <div class="product-card">
            <div class="product-image-wrapper">
              <div class="product-image" style="background: linear-gradient(135deg, #DFFFD8, #D5E8FF); display: flex; align-items: center; justify-content: center; color: #999;">
                <span>Product Image</span>
              </div>
              <button class="quick-add-btn" type="button" onclick="openSizeModal(event, 0, 'Core Leggings', 'GHS 180.00', '')">Quick Add</button>
            </div>
            <div class="product-info">
              <p class="product-category">Sports Bras</p>
              <h3 class="product-title">Flex Sports Bra</h3>
              <p class="product-price">GHS 120</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="section-cta">
        <a href="view/all_product.php" class="cta-btn cta-btn-primary">View All Products</a>
      </div>
    </div>
  </section>

  <!-- FEATURES SECTION -->
  <section class="features-section" id="features">
    <div class="container">
      <div class="section-header">
        <p class="section-eyebrow">Why Choose Lumé</p>
        <h2 class="section-title">Designed with You in Mind</h2>
        <p class="section-description">Every piece is thoughtfully crafted to empower your movement and celebrate your unique journey.</p>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">✨</div>
          <h3 class="feature-title">Premium Fabrics</h3>
          <p class="feature-description">Crafted from high-quality, breathable materials that move with you. Our fabrics are moisture-wicking, four-way stretch, and designed to maintain their shape wear after wear.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">💝</div>
          <h3 class="feature-title">Inclusive Sizing</h3>
          <p class="feature-description">Every body deserves to feel confident. Our size range from XS to 3XL ensures that everyone can find their perfect fit. Designed to flatter and support all body types.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">🌱</div>
          <h3 class="feature-title">Sustainably Made</h3>
          <p class="feature-description">Proudly crafted in Ghana with eco-conscious practices. We prioritize ethical production, fair wages, and sustainable materials to create activewear that's good for you and the planet.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">🎨</div>
          <h3 class="feature-title">Timeless Colors</h3>
          <p class="feature-description">Our carefully curated color palette features versatile neutrals and soft pastels that never go out of style. Mix, match, and create endless looks with pieces designed to complement each other.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">🇬🇭</div>
          <h3 class="feature-title">Crafted in Ghana</h3>
          <p class="feature-description">Every piece is made locally in Ghana, supporting our community and showcasing the incredible craftsmanship of Ghanaian artisans. Quality you can see, ethics you can trust.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">🌟</div>
          <h3 class="feature-title">Versatile Design</h3>
          <p class="feature-description">From studio to street, our activewear transitions seamlessly through your day. Thoughtful designs that look as good running errands as they do at your workout class.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section class="about-section" id="about">
    <div class="container">
      <div class="about-content">
        <div class="about-text">
          <p class="section-eyebrow">Our Story</p>
          <h2 class="section-title">Movement for Every Body</h2>
          
          <div class="about-body">
            <p>Lumé was born from a simple truth: finding activewear that truly fits shouldn't be a challenge. Too often, we saw women struggle to find pieces that celebrated their bodies, supported their movement, and reflected their style.</p>
            
            <p>We believe that everyone deserves to feel confident and comfortable in what they wear—whether you're hitting the gym, running errands, or simply living your life. That's why we created Lumé: premium activewear designed with real bodies in mind.</p>
            
            <p class="about-highlight">Every stitch, every seam, every silhouette is crafted in Ghana with intention—to empower your movement, celebrate your uniqueness, and support your journey.</p>
            
            <p>Our commitment goes beyond creating beautiful activewear. We're dedicated to inclusive sizing, sustainable practices, and supporting our local community. When you choose Lumé, you're choosing quality, ethics, and a brand that believes in you.</p>
            
            <p><strong>Welcome to Lumé. Move with confidence. Designed for you.</strong></p>
          </div>

          <div class="about-cta">
            <a href="view/all_product.php" class="cta-btn cta-btn-primary">Shop Our Collection</a>
          </div>
        </div>

        <div class="about-image">
          <img src="images/brand-image.jpg" alt="Lumé Brand" class="about-image-img">
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="contact-section" id="contact">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Get in Touch</h2>
        <p class="section-description">Please include your full name + order number if applicable. Allow 72 hours for a reply to all inquiries. Thank you for your support & patience!</p>
      </div>

      <div class="contact-content">
        <div class="contact-info">
          <div class="contact-info-item">
            <div class="contact-icon">📧</div>
            <div class="contact-details">
              <h3>Email Us</h3>
              <a href="mailto:support@lume.com">support@lume.com</a>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-icon">📱</div>
            <div class="contact-details">
              <h3>Call Us</h3>
              <a href="tel:+233544925402">+233 544 925 402</a>
            </div>
          </div>

          <div class="contact-info-item">
            <div class="contact-icon">📍</div>
            <div class="contact-details">
              <h3>Visit Us</h3>
              <p>Accra, Ghana</p>
            </div>
          </div>
        </div>

        <div class="contact-form-wrapper">
          <form class="contact-form" action="#" method="POST">
            <div class="form-row">
              <div class="form-group">
                <input type="text" name="name" placeholder="Name" required>
              </div>
              <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
              </div>
            </div>

            <div class="form-group">
              <textarea name="message" placeholder="Message" rows="6" required></textarea>
            </div>

            <button type="submit" class="cta-btn cta-btn-primary">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-brand">
          <h3 class="brand">LUMÉ</h3>
          <p>Premium activewear crafted in Ghana.</p>
        </div>

        <div class="footer-links">
          <div class="footer-column">
            <h4>Shop</h4>
            <a href="view/all_product.php">All Products</a>
            <a href="view/all_product.php">Collections</a>
            <a href="view/all_product.php">New Arrivals</a>
          </div>

          <div class="footer-column">
            <h4>About</h4>
            <a href="#about">Our Story</a>
            <a href="#features">Features</a>
            <a href="#contact">Contact</a>
          </div>

          <div class="footer-column">
            <h4>Support</h4>
            <a href="#contact">Shipping Info</a>
            <a href="#contact">Returns</a>
            <a href="#contact">Size Guide</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> Lumé Activewear. Crafted in Ghana with care.</p>
      </div>
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
    
    // Header scroll behavior - hide on scroll down, show on scroll up
    let lastScrollTop = 0;
    const header = document.querySelector('.site-header');
    const headerHeight = header ? header.offsetHeight : 0;
    
    // Add padding to body to account for fixed header
    document.body.style.paddingTop = headerHeight + 'px';
    
    window.addEventListener('scroll', function() {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      
      // Show header at the top of the page
      if (scrollTop < 50) {
        header.classList.remove('hidden');
        return;
      }
      
      // Hide header when scrolling down, show when scrolling up
      if (scrollTop > lastScrollTop && scrollTop > 100) {
        // Scrolling down
        header.classList.add('hidden');
      } else {
        // Scrolling up
        header.classList.remove('hidden');
      }
      
      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        // Don't prevent default for size guide link
        if (this.classList.contains('size-guide-link')) return;
        
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const target = document.querySelector(targetId);
        if (target) {
          // Account for fixed header height
          const targetPosition = target.offsetTop - headerHeight;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      });
    });

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
      log('Add to cart clicked');
      
      if (!selectedSize || !currentProductId) {
        log('Missing size or product ID', { selectedSize, currentProductId });
        alert('Please select a size');
        return;
      }
      
      const cartItem = {
        productId: currentProductId,
        size: selectedSize,
        quantity: 1
      };
      
      log('Sending cart item:', cartItem);
      
      // Try with simplified endpoint first
      fetch('actions/add-to-cart-simple.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(cartItem)
      })
      .then(response => {
        log('Response status:', response.status);
        return response.text();
      })
      .then(text => {
        log('Response text:', text);
        try {
          const data = JSON.parse(text);
          log('Parsed response:', data);
          
          if (data.success) {
            closeSizeModal();
            showToast('Added to Cart!', `Size ${selectedSize} added to your cart`);
            updateCartCount();
          } else {
            log('Error from server:', data);
            alert('Error: ' + (data.error || 'Unknown error'));
          }
        } catch (e) {
          log('JSON parse error:', e);
          log('Raw response:', text);
          alert('Server error. Check console for details.');
        }
      })
      .catch(error => {
        log('Fetch error:', error);
        alert('Network error: ' + error.message);
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
      log('Updating cart count');
      
      fetch('actions/get_cart_count_action.php')
        .then(response => response.json())
        .then(data => {
          log('Cart count response:', data);
          const cartCountElement = document.querySelector('.cart-count');
          if (cartCountElement && data.count !== undefined) {
            cartCountElement.textContent = data.count;
            log('Cart count updated to:', data.count);
          }
        })
        .catch(error => {
          log('Error updating cart count:', error);
        });
    }

    // Load cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
      log('Cart script loaded successfully');
      log('Current page:', window.location.pathname);
      updateCartCount();
    });
  </script>

</body>
</html>
