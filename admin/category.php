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
    <title>Category Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../js/category.js" defer></script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <header class="admin-header">
            <h1><i class="fas fa-tags"></i> Category Management</h1>
            <nav>
                <a href="../index.php" class="btn"><i class="fas fa-home"></i> Home</a>
                <a href="../view/logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <main class="admin-main">
            <!-- Add Category Form -->
            <section class="add-category-section">
                <h2><i class="fas fa-plus-circle"></i> Add New Category</h2>
                <form id="addCategoryForm" class="category-form">
                    <div class="form-group">
                        <label for="category_name">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </form>
            </section>

            <!-- Categories List -->
            <section class="categories-section">
                <h2><i class="fas fa-list"></i> Your Categories</h2>
                <div id="categoriesList" class="categories-grid">
                    <!-- Categories will be loaded here via JavaScript -->
                </div>
            </section>
        </main>
    </div>

    <!-- Edit Category Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Category</h2>
            <form id="editCategoryForm" class="category-form">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="form-group">
                    <label for="edit_category_name">Category Name:</label>
                    <input type="text" id="edit_category_name" name="category_name" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Category
                </button>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="message" class="message"></div>
</body>
</html>
