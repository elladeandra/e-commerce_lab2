document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const messageBox = document.getElementById('message');
  
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
  
      const formData = new FormData(form);
  
      // Regex checks
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const phoneRegex = /^[0-9]{7,15}$/;
      const passwordRegex = /^.{6,}$/;
  
      if (!emailRegex.test(formData.get("email"))) {
        return alert("Invalid email format");
      }
      if (!phoneRegex.test(formData.get("contact"))) {
        return alert("Contact must be a number between 7–15 digits");
      }
      if (!passwordRegex.test(formData.get("password"))) {
        return alert("Password must be at least 6 characters");
      }
  
      const response = await fetch('../actions/register_customer_action.php', {
        method: 'POST',
        body: formData
      });
  
      const text = await response.text();
      messageBox.innerText = text;
  
      if (text.includes("success")) {
        setTimeout(() => window.location.href = "../view/login.php", 1000);
      }
    });
  });
  