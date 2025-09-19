<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/register.js" defer></script>
</head>
<body class="register-page">
  <div class="register-card">
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
  </div>

  <script>
    // Drop sparkles after page load
    window.addEventListener("load", () => {
      for (let i = 0; i < 15; i++) {
        let sparkle = document.createElement("div");
        sparkle.classList.add("sparkle");
        sparkle.textContent = "✨";
        sparkle.style.left = Math.random() * 100 + "vw";
        sparkle.style.animationDuration = (2 + Math.random() * 2) + "s";
        document.body.appendChild(sparkle);

        setTimeout(() => sparkle.remove(), 4000);
      }
    });
  </script>
</body>
</html>
