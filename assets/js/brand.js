document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addBrandForm');
    const editForm = document.getElementById('editBrandForm');
    const editModal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');
    const brandsList = document.getElementById('brandsList');
    const messageDiv = document.getElementById('message');

    // Load brands on page load
    loadBrands();

    // Add brand form submission
    addForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(addForm);
        const brandName = formData.get('brand_name').trim();

        if (!brandName) {
            showMessage('Please enter a brand name', 'error');
            return;
        }

        try {
            const response = await fetch('../actions/add_brand_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Brand added successfully!', 'success');
                addForm.reset();
                loadBrands();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    });

    // Edit brand form submission
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(editForm);
        const brandName = formData.get('brand_name').trim();

        if (!brandName) {
            showMessage('Please enter a brand name', 'error');
            return;
        }

        try {
            const response = await fetch('../actions/update_brand_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Brand updated successfully!', 'success');
                closeModal();
                loadBrands();
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

    // Load brands from server
    async function loadBrands() {
        try {
            const response = await fetch('../actions/fetch_brand_action.php');
            const brands = await response.json();
            
            if (Array.isArray(brands)) {
                displayBrands(brands);
            } else {
                showMessage('Error loading brands', 'error');
            }
        } catch (error) {
            showMessage('Error loading brands', 'error');
        }
    }

    // Display brands in the grid
    function displayBrands(brands) {
        if (brands.length === 0) {
            brandsList.innerHTML = '<p class="no-categories">No brands found. Create your first brand above!</p>';
            return;
        }

        brandsList.innerHTML = brands.map(brand => `
            <div class="category-card">
                <div class="category-info">
                    <h3>${escapeHtml(brand.brand_name)}</h3>
                    <p><i class="fas fa-hashtag"></i> ID: ${brand.brand_id}</p>
                </div>
                <div class="category-actions">
                    <button onclick="editBrand(${brand.brand_id}, '${escapeHtml(brand.brand_name)}')" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="deleteBrand(${brand.brand_id}, '${escapeHtml(brand.brand_name)}')" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Edit brand function
    window.editBrand = function(brandId, brandName) {
        document.getElementById('edit_brand_id').value = brandId;
        document.getElementById('edit_brand_name').value = brandName;
        editModal.style.display = 'block';
    };

    // Delete brand function
    window.deleteBrand = async function(brandId, brandName) {
        if (!confirm(`Are you sure you want to delete the brand "${brandName}"?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('brand_id', brandId);

            const response = await fetch('../actions/delete_brand_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Brand deleted successfully!', 'success');
                loadBrands();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    };

    // Close modal function
    function closeModal() {
        editModal.style.display = 'none';
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
});
