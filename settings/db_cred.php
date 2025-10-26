<?php
//Database credentials
// Auto-detect environment: local development vs remote server

// Check if we're on the remote server (by checking if the remote username exists)
if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == '169.239.251.102' || file_exists('/home/emmanuella.oteng'))) {
    // Remote server configuration
    define("SERVER", "localhost");
    define("USERNAME", "emmanuella.oteng");
    define("PASSWD", "NeverForget20");
    define("DATABASE", "ecommerce_2025A_emmanuella_oteng");
} else {
    // Local development configuration
    define("SERVER", "localhost");
    define("USERNAME", "root");
    define("PASSWD", "");
    define("DATABASE", "ecommerce_2025A_emmanuella_oteng");
}
?>

