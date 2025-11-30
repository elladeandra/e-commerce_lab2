document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addProductForm');
    const editForm = document.getElementById('editProductForm');
    const editModal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');
    const productsList = document.getElementById('productsList');
    const messageDiv = document.getElementById('message');

    // Load products on page load
    loadProducts();

    // Add product form submission
    addForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(addForm);
        const productTitle = formData.get('product_title').trim();
        const productPrice = formData.get('product_price').trim();
        const productDescription = formData.get('product_description').trim();
        const productKeyword = formData.get('product_keyword').trim();
        const productCategory = formData.get('product_category');
        const productBrand = formData.get('product_brand');

        // Validation
        if (!productTitle) {
            showMessage('Please enter a product title', 'error');
            return;
        }
        if (!productPrice || isNaN(productPrice) || parseFloat(productPrice) <= 0) {
            showMessage('Please enter a valid price', 'error');
            return;
        }
        if (!productDescription) {
            showMessage('Please enter a product description', 'error');
            return;
        }
        if (!productKeyword) {
            showMessage('Please enter product keywords', 'error');
            return;
        }
        if (!productCategory) {
            showMessage('Please select a category', 'error');
            return;
        }
        if (!productBrand) {
            showMessage('Please select a brand', 'error');
            return;
        }

        try {
            // First, add the product
            const response = await fetch('../actions/add_product_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                // EXTRA CREDIT: Handle bulk image upload if files are selected
                const bulkFiles = document.getElementById('bulk_images').files;
                if (bulkFiles.length > 0) {
                    await handleBulkUpload(bulkFiles, 'temp'); // Use temp ID for new products
                }
                
                showMessage('Product added successfully!', 'success');
                addForm.reset();
                loadProducts();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    });

    // Edit product form submission
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(editForm);
        const productTitle = formData.get('product_title').trim();
        const productPrice = formData.get('product_price').trim();
        const productDescription = formData.get('product_description').trim();
        const productKeyword = formData.get('product_keyword').trim();
        const productCategory = formData.get('product_category');
        const productBrand = formData.get('product_brand');
        const productId = formData.get('product_id');

        // Validation
        if (!productTitle) {
            showMessage('Please enter a product title', 'error');
            return;
        }
        if (!productPrice || isNaN(productPrice) || parseFloat(productPrice) <= 0) {
            showMessage('Please enter a valid price', 'error');
            return;
        }
        if (!productDescription) {
            showMessage('Please enter a product description', 'error');
            return;
        }
        if (!productKeyword) {
            showMessage('Please enter product keywords', 'error');
            return;
        }
        if (!productCategory) {
            showMessage('Please select a category', 'error');
            return;
        }
        if (!productBrand) {
            showMessage('Please select a brand', 'error');
            return;
        }

        try {
            // First, update the product
            const response = await fetch('../actions/update_product_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                // EXTRA CREDIT: Handle bulk image upload if files are selected
                const bulkFiles = document.getElementById('edit_bulk_images').files;
                if (bulkFiles.length > 0) {
                    await handleBulkUpload(bulkFiles, productId);
                }
                
                showMessage('Product updated successfully!', 'success');
                closeModal();
                loadProducts();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    });

    // Close modal
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && editModal.classList.contains('active')) {
            closeModal();
        }
    });

    // Prevent body scroll when modal is open
    function preventBodyScroll() {
        document.body.style.overflow = 'hidden';
    }

    function allowBodyScroll() {
        document.body.style.overflow = 'auto';
    }

    // Load products from server
    async function loadProducts() {
        try {
            const response = await fetch('../actions/fetch_product_action.php');
            const products = await response.json();
            
            if (Array.isArray(products)) {
                displayProducts(products);
            } else {
                showMessage('Error loading products', 'error');
            }
        } catch (error) {
            showMessage('Error loading products', 'error');
        }
    }

    // Load categories for dropdown
    async function loadCategories() {
        try {
            const response = await fetch('../actions/fetch_category_action.php');
            const categories = await response.json();
            
            if (Array.isArray(categories)) {
                const categorySelect = document.getElementById('product_category');
                const editCategorySelect = document.getElementById('edit_product_category');
                
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">Select Category</option>' + 
                        categories.map(cat => `<option value="${cat.cat_id}">${escapeHtml(cat.cat_name)}</option>`).join('');
                }
                
                if (editCategorySelect) {
                    editCategorySelect.innerHTML = '<option value="">Select Category</option>' + 
                        categories.map(cat => `<option value="${cat.cat_id}">${escapeHtml(cat.cat_name)}</option>`).join('');
                }
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load brands for dropdown
    async function loadBrands() {
        try {
            const response = await fetch('../actions/fetch_brand_action.php');
            const brands = await response.json();
            
            if (Array.isArray(brands)) {
                const brandSelect = document.getElementById('product_brand');
                const editBrandSelect = document.getElementById('edit_product_brand');
                
                if (brandSelect) {
                    brandSelect.innerHTML = '<option value="">Select Brand</option>' + 
                        brands.map(brand => `<option value="${brand.brand_id}">${escapeHtml(brand.brand_name)}</option>`).join('');
                }
                
                if (editBrandSelect) {
                    editBrandSelect.innerHTML = '<option value="">Select Brand</option>' + 
                        brands.map(brand => `<option value="${brand.brand_id}">${escapeHtml(brand.brand_name)}</option>`).join('');
                }
            }
        } catch (error) {
            console.error('Error loading brands:', error);
        }
    }

    // Load dropdowns on page load
    loadCategories();
    loadBrands();

    // Display products in the grid
    function displayProducts(products) {
        if (products.length === 0) {
            productsList.innerHTML = '<p class="no-products">No products found. Create your first product above!</p>';
            return;
        }

        productsList.innerHTML = products.map(product => {
            // Escape all user input to prevent XSS
            const title = escapeHtml(product.product_title);
            const price = parseFloat(product.product_price).toFixed(2);
            const category = escapeHtml(product.cat_name || 'N/A');
            const brand = escapeHtml(product.brand_name || 'N/A');
            const desc = escapeHtml(product.product_desc || '');
            const keywords = escapeHtml(product.product_keywords || '');
            // Fix image path - remove leading slash if present
            let imagePath = '';
            if (product.product_image) {
                imagePath = product.product_image.startsWith('/') 
                    ? product.product_image.substring(1) 
                    : `../${product.product_image}`;
            }
            
            // Escape for onclick attributes
            const titleEscaped = product.product_title.replace(/'/g, "\\'");
            const descEscaped = (product.product_desc || '').replace(/'/g, "\\'");
            const keywordsEscaped = (product.product_keywords || '').replace(/'/g, "\\'");
            
            return `
            <div class="admin-product-card">
                <div class="admin-product-image-wrapper">
                    ${product.product_image ? `
                        <img src="${imagePath}" alt="${title}" class="admin-product-image">
                    ` : `
                        <div class="no-image" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #999; background: linear-gradient(135deg, #FFE5EC, #E5DEFF);">
                            <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <p>No Image</p>
                        </div>
                    `}
                </div>
                
                <div class="admin-product-info">
                    <h3 class="admin-product-name">${title}</h3>
                    
                    <p class="admin-product-price">$ Price: $${price}</p>
                    
                    <p class="admin-product-details">
                        <strong>🏷️ Category:</strong> ${category}
                    </p>
                    
                    <p class="admin-product-details">
                        <strong>TM Brand:</strong> ${brand}
                    </p>
                    
                    <p class="admin-product-details">
                        <strong># ID:</strong> ${product.product_id}
                    </p>
                    
                    ${desc ? `
                    <p class="admin-product-description">${desc}</p>
                    ` : ''}
                    
                    ${keywords ? `
                    <p class="admin-product-keywords">
                        🔍 Keywords: ${keywords}
                    </p>
                    ` : ''}
                    
                    <div class="admin-product-actions">
                        <button onclick="editProduct(${product.product_id}, '${titleEscaped}', '${product.product_price}', '${descEscaped}', '${keywordsEscaped}', '${product.product_cat}', '${product.product_brand}')" class="admin-btn admin-btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteProduct(${product.product_id}, '${titleEscaped}')" class="admin-btn admin-btn-delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    // Edit product function
    window.editProduct = function(productId, productTitle, productPrice, productDescription, productKeywords, catId, brandId) {
        document.getElementById('edit_product_id').value = productId;
        document.getElementById('edit_product_title').value = productTitle;
        document.getElementById('edit_product_price').value = productPrice;
        document.getElementById('edit_product_description').value = productDescription;
        document.getElementById('edit_product_keyword').value = productKeywords;
        document.getElementById('edit_product_category').value = catId;
        document.getElementById('edit_product_brand').value = brandId;
        editModal.classList.add('active');
        preventBodyScroll();
    };

    // Delete product function
    window.deleteProduct = async function(productId, productTitle) {
        if (!confirm(`Are you sure you want to delete the product "${productTitle}"?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('product_id', productId);

            const response = await fetch('../actions/delete_product_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Product deleted successfully!', 'success');
                loadProducts();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    };

    // Close modal function
    function closeModal() {
        editModal.classList.remove('active');
        allowBodyScroll();
        editForm.reset();
    }

    // Show message function
    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.className = `message ${type}`;
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }

    // Utility functions
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // EXTRA CREDIT: Handle bulk image upload
    async function handleBulkUpload(files, productId) {
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            
            // Add all files to FormData
            for (let i = 0; i < files.length; i++) {
                formData.append('bulk_images[]', files[i]);
            }

            const response = await fetch('../actions/upload_bulk_images_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                let message = `Bulk upload successful! ${result.total_uploaded} images uploaded.`;
                if (result.total_errors > 0) {
                    message += ` ${result.total_errors} files had errors.`;
                }
                showMessage(message, 'success');
            } else {
                showMessage('Bulk upload failed: ' + result.errors.join(', '), 'error');
            }
        } catch (error) {
            showMessage('Bulk upload failed: Something went wrong!', 'error');
        }
    }
});
