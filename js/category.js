document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addCategoryForm');
    const editForm = document.getElementById('editCategoryForm');
    const editModal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');
    const categoriesList = document.getElementById('categoriesList');
    const messageDiv = document.getElementById('message');

    // Load categories on page load
    loadCategories();

    // Add category form submission
    addForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(addForm);
        const categoryName = formData.get('category_name').trim();

        if (!categoryName) {
            showMessage('Please enter a category name', 'error');
            return;
        }

        try {
            const response = await fetch('../actions/add_category_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Category added successfully!', 'success');
                addForm.reset();
                loadCategories();
            } else {
                showMessage(result, 'error');
            }
        } catch (error) {
            showMessage('Something went wrong!', 'error');
        }
    });

    // Edit category form submission
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(editForm);
        const categoryName = formData.get('category_name').trim();

        if (!categoryName) {
            showMessage('Please enter a category name', 'error');
            return;
        }

        try {
            const response = await fetch('../actions/update_category_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Category updated successfully!', 'success');
                closeModal();
                loadCategories();
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

    // Load categories from server
    async function loadCategories() {
        try {
            const response = await fetch('../actions/fetch_category_action.php');
            const categories = await response.json();
            
            if (Array.isArray(categories)) {
                displayCategories(categories);
            } else {
                showMessage('Error loading categories', 'error');
            }
        } catch (error) {
            showMessage('Error loading categories', 'error');
        }
    }

    // Display categories in the grid
    function displayCategories(categories) {
        if (categories.length === 0) {
            categoriesList.innerHTML = '<p class="no-categories">No categories found. Create your first category above!</p>';
            return;
        }

        categoriesList.innerHTML = categories.map(category => `
            <div class="category-card">
                <div class="category-info">
                    <h3>${escapeHtml(category.cat_name)}</h3>
                    <p><i class="fas fa-hashtag"></i> ID: ${category.cat_id}</p>
                </div>
                <div class="category-actions">
                    <button onclick="editCategory(${category.cat_id}, '${escapeHtml(category.cat_name)}')" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="deleteCategory(${category.cat_id}, '${escapeHtml(category.cat_name)}')" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Edit category function
    window.editCategory = function(categoryId, categoryName) {
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_category_name').value = categoryName;
        editModal.style.display = 'block';
    };

    // Delete category function
    window.deleteCategory = async function(categoryId, categoryName) {
        if (!confirm(`Are you sure you want to delete the category "${categoryName}"?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('category_id', categoryId);

            const response = await fetch('../actions/delete_category_action.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            if (result === 'success') {
                showMessage('Category deleted successfully!', 'success');
                loadCategories();
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
