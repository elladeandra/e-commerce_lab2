<?php
/**
 * Paystack Payment Callback Handler
 * This page is called after Paystack payment process
 * User is redirected here by Paystack after payment
 */

require_once dirname(__FILE__) . '/../settings/core.php';
require_once dirname(__FILE__) . '/../settings/paystack_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get reference from URL
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;

if (!$reference) {
    // Payment cancelled or reference missing
    header('Location: checkout.php?error=cancelled');
    exit();
}

error_log("=== PAYSTACK CALLBACK PAGE ===");
error_log("Reference from URL: $reference");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Lumé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #FFE5EC 0%, #E5DEFF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 60px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .spinner {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 4px solid #F5F5F5;
            border-top: 4px solid #FF6B9D;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 30px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 15px;
        }
        
        p {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .reference {
            background: #FAFAFA;
            padding: 15px;
            border-radius: 12px;
            margin: 25px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.875rem;
            color: #666;
            border: 1px solid #E8E8E8;
        }
        
        .error {
            color: #dc2626;
            background: #fee2e2;
            border: 2px solid #fecaca;
            padding: 15px;
            border-radius: 12px;
            margin: 20px 0;
            display: none;
        }
        
        .success {
            color: #065f46;
            background: #d1fae5;
            border: 2px solid #6ee7b7;
            padding: 15px;
            border-radius: 12px;
            margin: 20px 0;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner" id="spinner"></div>
        
        <h1>Verifying Payment</h1>
        <p>Please wait while we verify your payment with Paystack...</p>
        
        <div class="reference">
            Payment Reference: <strong><?php echo htmlspecialchars($reference); ?></strong>
        </div>
        
        <div class="error" id="errorBox">
            <strong>Error:</strong> <span id="errorMessage"></span>
        </div>
        
        <div class="success" id="successBox">
            <strong>Success!</strong> Your payment has been verified. Redirecting...
        </div>
    </div>

    <script>
        /**
         * Verify payment with backend
         */
        async function verifyPayment() {
            const reference = '<?php echo htmlspecialchars($reference); ?>';
            
            try {
                const response = await fetch('../actions/paystack_verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reference: reference,
                        cart_items: null, // Will be fetched from backend
                        total_amount: null // Will be calculated from cart
                    })
                });
                
                let data;
                try {
                    const text = await response.text();
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid response from server');
                }
                
                console.log('Verification response:', data);
                
                // Hide spinner
                document.getElementById('spinner').style.display = 'none';
                
                if (data.status === 'success' && data.verified) {
                    // Payment verified successfully
                    document.getElementById('successBox').style.display = 'block';
                    
                    // Redirect to order summary page with order_id
                    const orderId = data.order_id || '';
                    if (orderId) {
                        window.location.replace(`order_summary.php?order_id=${encodeURIComponent(orderId)}`);
                    } else {
                        // Fallback to payment success page if no order_id
                        window.location.replace(`payment_success.php?reference=${encodeURIComponent(reference)}&invoice=${encodeURIComponent(data.invoice_no)}`);
                    }
                    
                } else {
                    // Payment verification failed
                    const errorMsg = data.message || 'Payment verification failed';
                    showError(errorMsg);
                    
                    // Redirect after 5 seconds
                    setTimeout(() => {
                        window.location.href = 'checkout.php?error=verification_failed';
                    }, 5000);
                }
                
            } catch (error) {
                console.error('Verification error:', error);
                showError('Connection error. Please try again or contact support.');
                
                // Redirect after 5 seconds
                setTimeout(() => {
                    window.location.href = 'checkout.php?error=connection_error';
                }, 5000);
            }
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            document.getElementById('errorBox').style.display = 'block';
            document.getElementById('errorMessage').textContent = message;
        }
        
        // Start verification when page loads
        window.addEventListener('load', verifyPayment);
    </script>
</body>
</html>

