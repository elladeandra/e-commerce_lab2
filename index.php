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
</head>
<body class="home-page">
  <header class="site-header">
    <div class="container">
      <a class="brand" href="index.php"><i class="fas fa-store"></i> E‑Commerce</a>
      <nav class="menu">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
          <a class="btn btn-logout" href="view/logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link" href="#features">Features</a>
          <a class="nav-link" href="#about">About</a>
          <a class="btn" href="view/register.php">Register</a>
          <a class="btn btn-primary" href="view/login.php">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-copy">
        <h1>Your one‑stop marketplace</h1>
        <p>Discover products you love from trusted sellers, with fast checkout and secure payments.</p>
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
      <small>© <?php echo date('Y'); ?> E‑Commerce Platform. All rights reserved.</small>
    </div>
  </footer>
</body>
</html>
