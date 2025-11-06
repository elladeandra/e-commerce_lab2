document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const messageBox = document.getElementById("message");
  
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
  
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();
  
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
        console.log("Attempting login with:", email);
        const response = await fetch("../actions/login_customer_action.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ email, password }),
        });

        const result = await response.json();
        console.log(result.status)
        if (result.status === "success") {
          messageBox.style.color = "green";
          messageBox.textContent = "Login successful! Redirecting...";
          setTimeout(() => {
            window.location.href = "../index.php";
          }, 2000);
        } else {
          messageBox.style.color = "red";
          messageBox.textContent = result.status;
        }
      } catch (error) {
        console.error(error);
        messageBox.style.color = "red";
        messageBox.textContent = "Something went wrong!";
      }
    });
  });
  