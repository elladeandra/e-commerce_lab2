<?php
require_once dirname(__FILE__).'/../settings/core.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../view/login.php");
    exit;
}

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../view/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Brand Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../assets/js/brand.js" defer></script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <header class="admin-header">
            <h1><i class="fas fa-tags"></i> Brand Management</h1>
            <nav>
                <a href="../index.php" class="btn"><i class="fas fa-home"></i> Home</a>
                <a href="../view/logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <main class="admin-main">
            <!-- Add Brand Form -->
            <section class="add-category-section">
                <h2><i class="fas fa-plus-circle"></i> Add New Brand</h2>
                <form id="addBrandForm" class="category-form">
                    <div class="form-group">
                        <label for="brand_name">Brand Name:</label>
                        <input type="text" id="brand_name" name="brand_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Brand
                    </button>
                </form>
                <div style="margin-top: 20px;">
                    <a href="../index.php" class="btn">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </section>

            <!-- Brands List -->
            <section class="categories-section">
                <h2><i class="fas fa-list"></i> Your Brands</h2>
                <div id="brandsList" class="categories-grid">
                    <!-- Brands will be loaded here via JavaScript -->
                </div>
            </section>
        </main>
    </div>

    <!-- Edit Brand Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Brand</h2>
            <form id="editBrandForm" class="category-form">
                <input type="hidden" id="edit_brand_id" name="brand_id">
                <div class="form-group">
                    <label for="edit_brand_name">Brand Name:</label>
                    <input type="text" id="edit_brand_name" name="brand_name" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Brand
                </button>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="message" class="message"></div>
</body>
</html>
