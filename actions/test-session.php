<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body {
            font-family: monospace;
            padding: 2rem;
            background: #f5f5f5;
        }
        pre {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Session Debug Information</h1>
    
    <h2>Session ID:</h2>
    <p><?php echo session_id(); ?></p>
    
    <h2>Session Data:</h2>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h2>Cart Contents:</h2>
    <?php if (isset($_SESSION['cart'])): ?>
        <pre><?php print_r($_SESSION['cart']); ?></pre>
        <p><strong>Total Items:</strong> 
        <?php 
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['quantity'] ?? 1;
            }
            echo $total;
        ?>
        </p>
    <?php else: ?>
        <p>Cart is empty or not set.</p>
    <?php endif; ?>
    
    <h2>Test Links:</h2>
    <ul>
        <li><a href="add-to-cart-simple.php?productId=1&size=M">Add Product 1 (Size M)</a></li>
        <li><a href="add-to-cart-simple.php?productId=2&size=L">Add Product 2 (Size L)</a></li>
        <li><a href="remove-from-cart-simple.php?cartKey=product_1_M">Remove Product 1 (Size M)</a></li>
    </ul>
    
    <p><a href="../view/cart.php">Go to Cart Page</a></p>
</body>
</html>

