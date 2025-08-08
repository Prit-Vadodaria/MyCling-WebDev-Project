document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const categorySelect = document.querySelector('.filter-group select:nth-child(2)');
    const sortBySelect = document.querySelector('.filter-group:nth-child(2) select');
    const priceRangeSlider = document.querySelector('.price-slider');
    const filterButton = document.querySelector('.filter-button');
    const priceValueMin = document.querySelector('.price-values span:first-child');
    const priceValueMax = document.querySelector('.price-values span:last-child');
    const productList = document.querySelector('.product-list');
    const productCards = document.querySelectorAll('.product-card');
    
    // Store original product order
    const originalProducts = Array.from(productCards);
    
    // Initialize product data for filtering
    let products = [];
    originalProducts.forEach(product => {
        const priceText = product.querySelector('.product-price').textContent;
        const priceMatch = priceText.match(/₹([\d,]+)/);
        const price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;
        
        const categoryText = product.querySelector('h3').textContent;
        let category = 'Other';
        
        // Determine category based on product name
        if (categoryText.toLowerCase().includes('hoodie')) {
            category = 'Hoodies';
        } else if (categoryText.toLowerCase().includes('shirt') || categoryText.toLowerCase().includes('top')) {
            category = 'T-Shirts';
        } else if (categoryText.toLowerCase().includes('jeans')) {
            category = 'Jeans';
        } else if (categoryText.toLowerCase().includes('dress')) {
            category = 'Dresses';
        } else if (categoryText.toLowerCase().includes('watch') || categoryText.toLowerCase().includes('bag')) {
            category = 'Accessories';
        } else if (categoryText.toLowerCase().includes('shoes')) {
            category = 'Shoes';
        }
        
        // Get badge info if available
        const badge = product.querySelector('.product-badge');
        const isSale = badge && badge.classList.contains('sale');
        const isNew = badge && !badge.classList.contains('sale') && badge.textContent.trim() === 'New';
        
        // Get rating if available
        const ratingEls = product.querySelectorAll('.fas.fa-star, .fas.fa-star-half-alt');
        const rating = ratingEls.length;
        
        products.push({
            element: product,
            price: price,
            category: category,
            isSale: isSale,
            isNew: isNew,
            name: categoryText,
            rating: rating
        });
    });
    
    // Update price slider range display
    if (priceRangeSlider) {
        priceRangeSlider.addEventListener('input', function() {
            priceValueMax.textContent = `₹${this.value}`;
        });
    }
    
    // Apply filters when button is clicked
    filterButton.addEventListener('click', applyFilters);
    
    function applyFilters() {
        const selectedCategory = categorySelect ? categorySelect.value : 'All Categories';
        const selectedSort = sortBySelect ? sortBySelect.value : 'Featured';
        const maxPrice = priceRangeSlider ? parseInt(priceRangeSlider.value) : 5000;
        
        // Filter products
        let filteredProducts = products.filter(product => {
            // Filter by category
            if (selectedCategory !== 'All Categories' && product.category !== selectedCategory) {
                return false;
            }
            
            // Filter by price
            if (product.price > maxPrice) {
                return false;
            }
            
            return true;
        });
        
        // Sort products
        if (selectedSort === 'Price: Low to High') {
            filteredProducts.sort((a, b) => a.price - b.price);
        } else if (selectedSort === 'Price: High to Low') {
            filteredProducts.sort((a, b) => b.price - a.price);
        } else if (selectedSort === 'Newest First') {
            filteredProducts.sort((a, b) => {
                if (a.isNew && !b.isNew) return -1;
                if (!a.isNew && b.isNew) return 1;
                return 0;
            });
        }
        
        // Clear product list
        while (productList.firstChild) {
            productList.removeChild(productList.firstChild);
        }
        
        // Display filtered and sorted products with animation
        if (filteredProducts.length === 0) {
            const noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.innerHTML = '<i class="fas fa-search"></i><h3>No products found</h3><p>Try adjusting your filters</p>';
            productList.appendChild(noResultsMsg);
        } else {
            filteredProducts.forEach((product, index) => {
                // Clone the original product element to maintain event listeners
                const clonedProduct = product.element.cloneNode(true);
                
                // Add animation delay based on index
                clonedProduct.style.animationDelay = `${index * 0.1}s`;
                clonedProduct.classList.remove('visible');
                
                // Add to DOM
                productList.appendChild(clonedProduct);
                
                // Force reflow
                void clonedProduct.offsetWidth;
                
                // Add visible class for animation
                setTimeout(() => {
                    clonedProduct.classList.add('visible', 'fade-in');
                }, 10);
                
                // Reattach event listener for "Add to Cart" button
                const addToCartBtn = clonedProduct.querySelector('.add-to-cart-btn');
                if (addToCartBtn) {
                    addToCartBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        this.innerHTML = '<i class="fas fa-check"></i> Added';
                        this.classList.add('added');
                        
                        setTimeout(() => {
                            this.innerHTML = 'Add to Cart';
                            this.classList.remove('added');
                        }, 2000);
                    });
                }
            });
        }
        
        // Update product count
        updateProductCount(filteredProducts.length);
    }
    
    // Add product count display
    function createProductCountElement() {
        const countContainer = document.createElement('div');
        countContainer.className = 'product-count';
        countContainer.innerHTML = `<span>${products.length}</span> products found`;
        
        const sectionTitle = document.querySelector('.section-title');
        if (sectionTitle) {
            sectionTitle.after(countContainer);
        } else {
            const productsSection = document.querySelector('.products .container');
            if (productsSection && productsSection.firstChild) {
                productsSection.insertBefore(countContainer, productsSection.firstChild);
            }
        }
        
        return countContainer;
    }
    
    const productCount = createProductCountElement();
    
    function updateProductCount(count) {
        if (productCount) {
            productCount.innerHTML = `<span>${count}</span> products found`;
        }
    }
    
    // Add search functionality
    const searchInput = document.querySelector('.search-bar');
    const searchIcon = document.querySelector('.search-icon');
    
    if (searchInput && searchIcon) {
        // Search when icon is clicked
        searchIcon.addEventListener('click', performSearch);
        
        // Search when Enter key is pressed
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (searchTerm === '') return;
        
        // Filter products based on search term
        let searchResults = products.filter(product => 
            product.name.toLowerCase().includes(searchTerm) || 
            product.category.toLowerCase().includes(searchTerm)
        );
        
        // Clear product list
        while (productList.firstChild) {
            productList.removeChild(productList.firstChild);
        }
        
        // Display search results
        if (searchResults.length === 0) {
            const noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results';
            noResultsMsg.innerHTML = `<i class="fas fa-search"></i><h3>No products found</h3><p>No products match "${searchTerm}"</p>`;
            productList.appendChild(noResultsMsg);
        } else {
            searchResults.forEach((product, index) => {
                const clonedProduct = product.element.cloneNode(true);
                clonedProduct.style.animationDelay = `${index * 0.1}s`;
                productList.appendChild(clonedProduct);
                
                setTimeout(() => {
                    clonedProduct.classList.add('visible', 'fade-in');
                }, 10);
                
                // Reattach event listener for "Add to Cart" button
                const addToCartBtn = clonedProduct.querySelector('.add-to-cart-btn');
                if (addToCartBtn) {
                    addToCartBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        this.innerHTML = '<i class="fas fa-check"></i> Added';
                        this.classList.add('added');
                        
                        setTimeout(() => {
                            this.innerHTML = 'Add to Cart';
                            this.classList.remove('added');
                        }, 2000);
                    });
                }
            });
        }
        
        // Update product count
        updateProductCount(searchResults.length);
        
        // Reset filters to match search results
        if (categorySelect) categorySelect.value = 'All Categories';
        if (sortBySelect) sortBySelect.value = 'Featured';
        if (priceRangeSlider) {
            priceRangeSlider.value = priceRangeSlider.max;
            priceValueMax.textContent = `₹${priceRangeSlider.max}`;
        }
    }
    
    // Reset filters when a reset button is clicked
    // First, let's add a reset button to the filter container
    const resetButton = document.createElement('button');
    resetButton.className = 'reset-filter-button';
    resetButton.innerHTML = '<i class="fas fa-undo"></i> Reset Filters';
    
    const filterContainer = document.querySelector('.filter-container');
    if (filterContainer) {
        filterContainer.appendChild(resetButton);
    }
    
    resetButton.addEventListener('click', resetFilters);
    
    function resetFilters() {
        // Reset category select
        if (categorySelect) categorySelect.value = 'All Categories';
        
        // Reset sort select
        if (sortBySelect) sortBySelect.value = 'Featured';
        
        // Reset price range
        if (priceRangeSlider) {
            priceRangeSlider.value = priceRangeSlider.max;
            priceValueMax.textContent = `₹${priceRangeSlider.max}`;
        }
        
        // Clear search input
        if (searchInput) searchInput.value = '';
        
        // Clear product list
        while (productList.firstChild) {
            productList.removeChild(productList.firstChild);
        }
        
        // Restore original products
        originalProducts.forEach((product, index) => {
            const clonedProduct = product.cloneNode(true);
            clonedProduct.style.animationDelay = `${index * 0.1}s`;
            productList.appendChild(clonedProduct);
            
            setTimeout(() => {
                clonedProduct.classList.add('visible', 'fade-in');
            }, 10);
            
            // Reattach event listener for "Add to Cart" button
            const addToCartBtn = clonedProduct.querySelector('.add-to-cart-btn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.innerHTML = '<i class="fas fa-check"></i> Added';
                    this.classList.add('added');
                    
                    setTimeout(() => {
                        this.innerHTML = 'Add to Cart';
                        this.classList.remove('added');
                    }, 2000);
                });
            }
        });
        
        // Update product count
        updateProductCount(originalProducts.length);
    }
}); 