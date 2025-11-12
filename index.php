<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My E-Commerce Platform</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="fontawesome/css/all.min.css">
  <script src="js/product_search.js" defer></script>
  <script src="js/cart.js" defer></script>
</head>
<body class="home-page">
  <header class="site-header">
    <div class="container">
      <a class="brand" href="index.php"><i class="fas fa-store"></i> E‑Commerce</a>
      <nav class="menu">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
          <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == '1'): ?>
            <a class="btn" href="admin/category.php">Category</a>
            <a class="btn" href="admin/brand.php">Brand</a>
            <a class="btn" href="admin/product.php">Add Product</a>
          <?php endif; ?>
          <a class="btn btn-logout" href="view/logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link" href="#features">Features</a>
          <a class="nav-link" href="#about">About</a>
          <a class="btn" href="view/register.php">Register</a>
          <a class="btn btn-primary" href="view/login.php">Login</a>
        <?php endif; ?>
        <a class="btn" href="view/all_product.php"><i class="fas fa-box"></i> All Products</a>
        <a class="btn" href="view/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a class="btn" href="view/checkout.php"><i class="fas fa-lock"></i> Checkout</a>
      </nav>
    </div>
  </header>

  <!-- Search and Filter Section -->
  <section class="search-section">
    <div class="container">
      <div class="search-filter-container">
        <form method="GET" action="view/product_search_result.php" class="main-search-form">
          <div class="search-input-wrapper">
            <input type="text" 
                   name="search" 
                   placeholder="Search products by title, description, or keywords..." 
                   class="main-search-input"
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary search-submit-btn">
              <i class="fas fa-search"></i> Search
            </button>
          </div>
        </form>

        <?php
        require_once dirname(__FILE__).'/controllers/category_controller.php';
        require_once dirname(__FILE__).'/controllers/brand_controller.php';
        $categories = get_categories_ctr();
        $brands = get_brands_ctr();
        ?>

        <div class="quick-filters">
          <form method="GET" action="view/all_product.php" class="quick-filters-form">
            <div class="filter-item">
              <label for="quick_category"><i class="fas fa-tag"></i> Category:</label>
              <select id="quick_category" name="category" class="quick-filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category['cat_id']; ?>">
                    <?php echo htmlspecialchars($category['cat_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-item">
              <label for="quick_brand"><i class="fas fa-trademark"></i> Brand:</label>
              <select id="quick_brand" name="brand" class="quick-filter-select" onchange="this.form.submit()">
                <option value="">All Brands</option>
                <?php foreach ($brands as $brand): ?>
                  <option value="<?php echo $brand['brand_id']; ?>">
                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-copy">
        <h1>Your one‑stop marketplace</h1>
        <p>Discover products you love from Lumé, with fast checkout and secure payments.</p>
        <div class="cta-row">
          <a class="btn btn-primary" href="view/register.php"><i class="fas fa-user-plus"></i> Get Started</a>
          <a class="btn" href="view/login.php"><i class="fas fa-right-to-bracket"></i> Sign In</a>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-card">
          <i class="fas fa-shield-halved"></i>
          <h3>Secure by design</h3>
          <p>Modern encryption for your data and transactions.</p>
        </div>
        <div class="hero-card">
          <i class="fas fa-truck-fast"></i>
          <h3>Fast checkout</h3>
          <p>Smooth purchasing with minimal friction.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="features" class="features">
    <div class="container section-head">
      <h2><i class="fas fa-stars"></i> Why choose us</h2>
      <p>Built for buyers and sellers with reliability at its core.</p>
    </div>
    <div class="container grid">
      <div class="card">
        <h3>Wide Selection</h3>
        <p>Browse a diverse catalog across categories curated for quality and value.</p>
      </div>
      <div class="card">
        <h3>Secure & Fast</h3>
        <p>Modern security practices and a smooth checkout keep you moving.</p>
      </div>
      <div class="card">
        <h3>Customer First</h3>
        <p>Support that actually helps, with easy returns and helpful guidance.</p>
      </div>
    </div>
  </section>

  <section id="about" class="about">
    <div class="container section-head">
      <h2><i class="fas fa-circle-info"></i> About the platform</h2>
      <p>A simple, scalable foundation for your commerce projects and coursework.</p>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <small>© <?php echo date('Y'); ?> EēCommerce Platform. All rights reserved.</small>
    </div>
  </footer>
</body>
</html>
