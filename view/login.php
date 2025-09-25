<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/login.js" defer></script>
</head>
<body class="login-page">
  <div class="login-card">
    <p style="text-align:left;margin-bottom:8px;"><a class="btn" href="../index.php">← Back to Home</a></p>
    <h2>Login</h2>
    <form id="loginForm">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p id="message"></p>
  </div>
</body>
</html>
