<?php
$files = [
    'add-to-cart-simple.php',
    'remove-from-cart-simple.php',
    'get_cart_count_action.php',
    'add_to_cart_with_size_action.php',
    'remove_from_cart_action.php',
    'update_cart_quantity_action.php'
];

echo '<h2>File Check</h2>';
echo '<style>body { font-family: monospace; padding: 2rem; } .exists { color: green; } .missing { color: red; }</style>';

foreach ($files as $file) {
    $exists = file_exists($file);
    $class = $exists ? 'exists' : 'missing';
    $icon = $exists ? '✓' : '✗';
    echo "<p class='$class'>$icon $file: " . ($exists ? 'EXISTS' : 'MISSING') . '</p>';
}

echo '<h2>Directory Contents</h2>';
echo '<pre>';
$dir = dirname(__FILE__);
$files = scandir($dir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo $file . "\n";
    }
}
echo '</pre>';
?>

