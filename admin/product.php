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
    <title>Product Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../assets/js/product.js" defer></script>
</head>
<body class="admin-page">
    <div class="admin-container">
        <header class="admin-header">
            <h1><i class="fas fa-box"></i> Product Management</h1>
            <nav>
                <a href="../index.php" class="btn"><i class="fas fa-home"></i> Home</a>
                <a href="../view/logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </header>

        <main class="admin-main">
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <a href="../index.php" class="tab-button home-button">
                    <i class="fas fa-home"></i> Home
                </a>
                <button class="tab-button active" onclick="switchTab('add-product')">
                    <i class="fas fa-plus-circle"></i> Add Product
                </button>
                <button class="tab-button" onclick="switchTab('view-products')">
                    <i class="fas fa-list"></i> View Products
                </button>
            </div>

            <!-- Add Product Tab -->
            <div id="add-product-tab" class="tab-content active">
                <section class="add-product-section">
                    <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                <form id="addProductForm" class="product-form" enctype="multipart/form-data">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="product_category">Product Category:</label>
                                <select id="product_category" name="product_category" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="product_brand">Product Brand:</label>
                                <select id="product_brand" name="product_brand" required>
                                    <option value="">Select Brand</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_title">Product Title:</label>
                            <input type="text" id="product_title" name="product_title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_price">Product Price:</label>
                            <input type="number" id="product_price" name="product_price" step="0.01" min="0" required>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-align-left"></i> Description & Keywords</h3>
                        <div class="form-group">
                            <label for="product_description">Product Description:</label>
                            <textarea id="product_description" name="product_description" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_keyword">Product Keywords:</label>
                            <input type="text" id="product_keyword" name="product_keyword" placeholder="e.g., electronics, smartphone, mobile (comma-separated)" required>
                            <small>Separate keywords with commas for better search performance</small>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-images"></i> Product Images</h3>
                        <div class="form-group">
                            <label for="product_image">Single Image Upload:</label>
                            <input type="file" id="product_image" name="product_image" accept="image/*">
                            <small>Upload a single image for this product (optional)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="bulk_images">Bulk Image Upload:</label>
                            <input type="file" id="bulk_images" name="bulk_images[]" accept="image/*" multiple>
                            <small>Select multiple images to upload at once (optional)</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="switchTab('view-products')">
                            <i class="fas fa-list"></i> View Products
                        </button>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Home
                        </a>
                </div>
            </form>
                </section>
            </div>

            <!-- View Products Tab -->
            <div id="view-products-tab" class="tab-content">
                <section class="products-section">
                    <h2><i class="fas fa-list"></i> Your Products</h2>
                    <div id="productsList" class="products-grid">
                        <!-- Products will be loaded here via JavaScript -->
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Product</h2>
            <form id="editProductForm" class="product-form" enctype="multipart/form-data">
                <input type="hidden" id="edit_product_id" name="product_id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_product_category">Product Category:</label>
                            <select id="edit_product_category" name="product_category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_product_brand">Product Brand:</label>
                            <select id="edit_product_brand" name="product_brand" required>
                                <option value="">Select Brand</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_product_title">Product Title:</label>
                        <input type="text" id="edit_product_title" name="product_title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_product_price">Product Price:</label>
                        <input type="number" id="edit_product_price" name="product_price" step="0.01" min="0" required>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="form-section">
                    <h3><i class="fas fa-align-left"></i> Description & Keywords</h3>
                    <div class="form-group">
                        <label for="edit_product_description">Product Description:</label>
                        <textarea id="edit_product_description" name="product_description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_product_keyword">Product Keywords:</label>
                        <input type="text" id="edit_product_keyword" name="product_keyword" placeholder="e.g., electronics, smartphone, mobile (comma-separated)" required>
                        <small>Separate keywords with commas for better search performance</small>
                    </div>
                </div>

                <!-- Image Upload Section -->
                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Product Images</h3>
                    <div class="form-group">
                        <label for="edit_product_image">Single Image Upload:</label>
                        <input type="file" id="edit_product_image" name="product_image" accept="image/*">
                        <small>Upload a new image (optional - leave empty to keep current image)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_bulk_images">Bulk Image Upload:</label>
                        <input type="file" id="edit_bulk_images" name="bulk_images[]" accept="image/*" multiple>
                        <small>Select multiple images to upload at once (optional)</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="message" class="message"></div>

    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // If switching to view products, load the products
            if (tabName === 'view-products') {
                loadProducts();
            }
        }
    </script>
</body>
</html>
