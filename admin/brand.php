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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Brand Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-styles.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <script src="../assets/js/brand.js" defer></script>
</head>
<body class="admin-page">
    <!-- ADMIN HEADER -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo-section">
                <span class="admin-icon"><i class="fas fa-tags"></i></span>
                <h1 class="admin-page-title">Brand Management</h1>
            </div>
            
            <div class="admin-actions">
                <a href="../index.php" class="admin-btn admin-btn-home"><i class="fas fa-home"></i> Home</a>
                <a href="../view/logout.php" class="admin-btn admin-btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <!-- ADMIN MAIN CONTENT -->
    <main class="admin-main">
        <div class="admin-container">
            
            <!-- ADD NEW SECTION -->
            <section class="admin-card">
                <h2 class="admin-section-title"><i class="fas fa-plus-circle"></i> Add New Brand</h2>
                
                <form id="addBrandForm" class="admin-form">
                    <div class="form-group">
                        <label for="brand_name">Brand Name:</label>
                        <input type="text" id="brand_name" name="brand_name" placeholder="Enter brand name" required>
                    </div>
                    
                    <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-plus"></i> Add Brand</button>
                </form>
            </section>

            <!-- LIST SECTION -->
            <section class="admin-card">
                <h2 class="admin-section-title"><i class="fas fa-list"></i> Your Brands</h2>
                
                <div id="brandsList" class="admin-list">
                    <!-- Brands will be loaded here via JavaScript -->
                </div>
            </section>
        </div>
    </main>

    <!-- EDIT BRAND MODAL -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Brand</h2>
            <form id="editBrandForm" class="admin-form">
                <input type="hidden" id="edit_brand_id" name="brand_id">
                <div class="form-group">
                    <label for="edit_brand_name">Brand Name:</label>
                    <input type="text" id="edit_brand_name" name="brand_name" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Update Brand</button>
                    <button type="button" class="admin-btn admin-btn-secondary" onclick="document.getElementById('editModal').classList.remove('active');"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="message" class="message hidden"></div>
</body>
</html>
