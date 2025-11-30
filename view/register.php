<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/register.js" defer></script>
</head>
<body class="register-page">
  <div class="register-card">
    <p style="text-align:left;margin-bottom:8px;"><a class="btn" href="../index.php">← Back to Home</a></p>
    <h2>Customer Registration</h2>
    <form id="registerForm">
      <input type="text" name="fullname" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="text" name="country" placeholder="Country" required>
      <input type="text" name="city" placeholder="City" required>
      <input type="text" name="contact" placeholder="Contact Number" required>
      <button type="submit" id="registerBtn">Register</button>
    </form>
    <p id="message"></p>
    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
  </div>

</body>
</html>
