// Product Search and Filter JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initSearch();
    initFilters();
    initAsyncSearch();
});

// Initialize main search functionality
function initSearch() {
    const searchForm = document.querySelector('.main-search-form');
    const searchInput = document.querySelector('.main-search-input');
    
    if (searchForm && searchInput) {
        // Add real-time search suggestions (optional enhancement)
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Optional: Show search suggestions after 300ms delay
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // Could implement search suggestions here
                }, 300);
            }
        });

        // Handle form submission
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query === '') {
                e.preventDefault();
                alert('Please enter a search term');
                return false;
            }
        });
    }
}

// Initialize filter functionality
function initFilters() {
    const filterSelects = document.querySelectorAll('.filter-select, .quick-filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // If it's a quick filter, the form will auto-submit
            // Otherwise, we can add custom behavior here
            if (this.classList.contains('filter-select')) {
                // Could add async filtering here
                const form = this.closest('form');
                if (form) {
                    // Optionally submit form or do async request
                }
            }
        });
    });
}

// Initialize async search (for dynamic dropdowns)
function initAsyncSearch() {
    const searchInput = document.querySelector('.main-search-input');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Perform async search after user stops typing (500ms delay)
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performAsyncSearch(query);
                }, 500);
            } else if (query.length === 0) {
                // Clear results if search is empty
                clearSearchResults();
            }
        });
    }
}

// Perform async search
function performAsyncSearch(query) {
    const category = document.getElementById('filter_category')?.value || 
                     document.getElementById('quick_category')?.value || '';
    const brand = document.getElementById('filter_brand')?.value || 
                  document.getElementById('quick_brand')?.value || '';
    
    // Build URL with parameters
    const params = new URLSearchParams({
        action: 'search',
        query: query
    });
    
    if (category) params.append('cat_id', category);
    if (brand) params.append('brand_id', brand);
    
    // Make AJAX request
    fetch(`actions/product_actions.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Could update UI with results dynamically
                console.log('Search results:', data.products);
                // Optionally display results in a dropdown or update page
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

// Clear search results
function clearSearchResults() {
    // Clear any dynamic search result displays
    const resultContainer = document.querySelector('.dynamic-search-results');
    if (resultContainer) {
        resultContainer.innerHTML = '';
    }
}

// Dynamic category/brand filter loading
function loadCategories() {
    fetch('actions/product_actions.php?action=get_categories')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCategoryDropdown(data.categories);
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

function loadBrands() {
    fetch('actions/product_actions.php?action=get_brands')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBrandDropdown(data.brands);
            }
        })
        .catch(error => {
            console.error('Error loading brands:', error);
        });
}

// Update category dropdown
function updateCategoryDropdown(categories) {
    const select = document.getElementById('filter_category') || 
                   document.getElementById('quick_category');
    
    if (select) {
        const currentValue = select.value;
        select.innerHTML = '<option value="">All Categories</option>';
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.cat_id;
            option.textContent = category.cat_name;
            if (category.cat_id == currentValue) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }
}

// Update brand dropdown
function updateBrandDropdown(brands) {
    const select = document.getElementById('filter_brand') || 
                   document.getElementById('quick_brand');
    
    if (select) {
        const currentValue = select.value;
        select.innerHTML = '<option value="">All Brands</option>';
        
        brands.forEach(brand => {
            const option = document.createElement('option');
            option.value = brand.brand_id;
            option.textContent = brand.brand_name;
            if (brand.brand_id == currentValue) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    }
}

// Add to cart functionality (deprecated - now handled by size modal)
function addToCart(productId) {
    // This function is deprecated - Quick Add now uses openSizeModal() directly
    // Keeping for backwards compatibility but should not be called
    console.log('addToCart() is deprecated. Use openSizeModal() instead. Product ID:', productId);
}

// Export functions for use in other scripts
window.productSearch = {
    performAsyncSearch,
    loadCategories,
    loadBrands,
    addToCart
};

