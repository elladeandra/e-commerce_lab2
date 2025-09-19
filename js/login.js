document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const messageBox = document.getElementById("message");
  
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
  
      const email = form.email.value.trim();
      const password = form.password.value.trim();
  
      // ✅ Client-side validation
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
      }
      if (password.length < 3) {
        alert("Password must be at least 3 characters long.");
        return;
      }
  
      try {
        const response = await fetch("../actions/login_customer_action.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ email, password }),
        });
  
        const result = await response.text();
  
        if (result.trim() === "success") {
          messageBox.style.color = "green";
          messageBox.textContent = "Login successful! Redirecting...";
          setTimeout(() => {
            window.location.href = "../index.php";
          }, 2000);
        } else {
          messageBox.style.color = "red";
          messageBox.textContent = result;
        }
      } catch (error) {
        console.error(error);
        messageBox.style.color = "red";
        messageBox.textContent = "Something went wrong!";
      }
    });
  });
  