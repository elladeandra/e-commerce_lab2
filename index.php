<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My E-Commerce Platform</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="hero">
    <h1>Welcome to the E-Commerce Platform</h1>
    <p>Your one-stop shop for everything you need</p>
    <nav>
      <?php if (isset($_SESSION['user_id'])): ?>
        <span>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span> |
        <a href="view/logout.php">Logout</a>
      <?php else: ?>
        <a href="view/register.php">Register</a> |
        <a href="view/login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</body>
</html>
