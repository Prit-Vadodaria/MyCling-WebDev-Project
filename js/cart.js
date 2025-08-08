document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart functionality
    initializeCart();
    
    // Function to initialize cart
    function initializeCart() {
        // Setup quantity buttons
        setupQuantityButtons();
        
        // Setup remove buttons
        setupRemoveButtons();
        
        // Setup login notification
        setupLoginNotification();
    }
    
    // Function to handle quantity buttons
    function setupQuantityButtons() {
        const decreaseButtons = document.querySelectorAll('.cart-quantity-btn.decrease');
        const increaseButtons = document.querySelectorAll('.cart-quantity-btn.increase');
        const quantityInputs = document.querySelectorAll('.cart-quantity-input');
        
        // Handle decrease buttons
        decreaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.nextElementSibling;
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    updateCartItem(input);
                }
            });
        });
        
        // Handle increase buttons
        increaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const currentValue = parseInt(input.value);
                input.value = currentValue + 1;
                updateCartItem(input);
            });
        });
        
        // Handle direct input
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                updateCartItem(this);
            });
        });
    }
    
    // Function to update cart item quantity
    function updateCartItem(input) {
        const cartItem = input.closest('.cart-item');
        const productId = cartItem.getAttribute('data-product-id');
        const quantity = parseInt(input.value);
        
        if (quantity < 1) {
            input.value = 1;
            return;
        }
        
        // Show loading indicator
        cartItem.classList.add('updating');
        
        // Send update request
        fetch('/My-cling-WebDevelopment-project/php/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item total
                const totalElem = cartItem.querySelector('.cart-total');
                if (totalElem) {
                    totalElem.textContent = '₹' + data.item_total.toLocaleString();
                }
                
                // Update cart summary
                updateCartSummary(data);
                
                // Add highlight effect
                cartItem.classList.add('highlight');
                setTimeout(() => {
                    cartItem.classList.remove('highlight');
                }, 1000);
            } else {
                alert(data.message || 'Error updating cart');
            }
            
            // Remove loading indicator
            cartItem.classList.remove('updating');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating cart. Please try again.');
            cartItem.classList.remove('updating');
        });
    }
    
    // Function to setup remove buttons - SIMPLIFIED
    function setupRemoveButtons() {
        // Get all remove buttons
        const removeButtons = document.querySelectorAll('.remove-item');
        
        // Add click event to each button
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get product ID from data attribute
                const productId = this.getAttribute('data-product-id');
                // Get cart item element
                const cartItem = this.closest('.cart-item');
                // Call remove function
                removeCartItem(productId, cartItem);
            });
        });
    }
    
    // Function to update cart summary
    function updateCartSummary(data) {
        const subtotalElem = document.querySelector('.summary-row:nth-child(1) span:last-child');
        const shippingElem = document.querySelector('.summary-row:nth-child(2) span:last-child');
        const taxElem = document.querySelector('.summary-row:nth-child(3) span:last-child');
        const totalElem = document.querySelector('.summary-row.total span:last-child');
        const cartCount = document.querySelector('.cart-count');
        const cartHeading = document.querySelector('.cart-heading h2');
        
        if (subtotalElem) subtotalElem.textContent = '₹' + data.subtotal.toLocaleString();
        if (shippingElem) shippingElem.textContent = '₹' + data.shipping.toLocaleString();
        if (taxElem) taxElem.textContent = '₹' + data.tax.toLocaleString();
        if (totalElem) totalElem.textContent = '₹' + data.total.toLocaleString();
        
        if (cartCount) {
            cartCount.textContent = data.total_items;
            if (data.total_items === 0) {
                cartCount.style.display = 'none';
            }
        }
        
        if (cartHeading) {
            cartHeading.textContent = `Your Shopping Cart (${data.total_items} items)`;
        }
    }
    
    // Function to setup login notification
    function setupLoginNotification() {
        const loginNotification = document.getElementById('loginNotification');
        const closeNotificationBtn = document.getElementById('closeNotification');
        
        if (closeNotificationBtn && loginNotification) {
            closeNotificationBtn.addEventListener('click', function() {
                loginNotification.style.display = 'none';
            });
        }
    }
});

// Function to remove cart item
function removeCartItem(productId, cartItem) {
    console.log('Removing product ID:', productId);
    
    if (!cartItem) {
        console.error('Cart item element not found');
        return;
    }
    
    if (confirm('Are you sure you want to remove this item?')) {
        // Show loading indicator
        cartItem.classList.add('updating');
        
        // Send remove request
        fetch('/My-cling-WebDevelopment-project/php/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                // Add removing animation
                cartItem.classList.add('removing');
                
                // Remove after animation completes
                setTimeout(() => {
                    cartItem.remove();
                    
                    // Update cart summary
                    const subtotalElem = document.querySelector('.summary-row:nth-child(1) span:last-child');
                    const shippingElem = document.querySelector('.summary-row:nth-child(2) span:last-child');
                    const taxElem = document.querySelector('.summary-row:nth-child(3) span:last-child');
                    const totalElem = document.querySelector('.summary-row.total span:last-child');
                    const cartCount = document.querySelector('.cart-count');
                    const cartHeading = document.querySelector('.cart-heading h2');
                    
                    if (subtotalElem) subtotalElem.textContent = '₹' + data.subtotal.toLocaleString();
                    if (shippingElem) shippingElem.textContent = '₹' + data.shipping.toLocaleString();
                    if (taxElem) taxElem.textContent = '₹' + data.tax.toLocaleString();
                    if (totalElem) totalElem.textContent = '₹' + data.total.toLocaleString();
                    
                    if (cartCount) {
                        cartCount.textContent = data.total_items;
                        if (data.total_items === 0) {
                            cartCount.style.display = 'none';
                        }
                    }
                    
                    if (cartHeading) {
                        cartHeading.textContent = `Your Shopping Cart (${data.total_items} items)`;
                    }
                    
                    // If cart is now empty, reload page
                    if (data.total_items === 0) {
                        window.location.reload();
                    }
                }, 300);
            } else {
                alert(data.message || 'Error removing item from cart');
                cartItem.classList.remove('updating');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing item from cart. Please try again.');
            cartItem.classList.remove('updating');
        });
    }
} 