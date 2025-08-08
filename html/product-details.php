<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Get cart summary for header
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];

// Get product details from ID in URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Get product details
$product_query = "SELECT * FROM products WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$product_result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($product_result) == 0) {
    // Product not found, redirect to products page
    header("Location: product.php");
    exit;
}

$product = mysqli_fetch_assoc($product_result);

// Get related products (same category)
$related_query = "SELECT * FROM products WHERE category_id = ? AND product_id != ? LIMIT 4";
$stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($stmt, "ii", $product['category_id'], $product_id);
mysqli_stmt_execute($stmt);
$related_result = mysqli_stmt_get_result($stmt);
$related_products = [];
while($row = mysqli_fetch_assoc($related_result)) {
    $related_products[] = $row;
}

// Get category name
$category_query = "SELECT name FROM categories WHERE category_id = ?";
$stmt = mysqli_prepare($conn, $category_query);
mysqli_stmt_bind_param($stmt, "i", $product['category_id']);
mysqli_stmt_execute($stmt);
$category_result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($category_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">MyCling</div>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="men.php"><i class="fas fa-male"></i> Men</a></li>
                <li><a href="women.php"><i class="fas fa-female"></i> Women</a></li>
                <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart <?php if($total_items > 0): ?><span class="cart-count"><?php echo $total_items; ?></span><?php endif; ?></a></li>
                <li><a href="login.php"><i class="fas fa-user"></i> Account</a></li>
                <li>
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="Search products..." />
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </li>
            </ul>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Product Details</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <a href="product.php">Products</a> / <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
        </div>
    </section>

    <!-- Product Details Section -->
    <section class="product-details">
        <div class="container">
            <div class="product-details-container">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image">
                    <div class="thumbnail-container">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?> Thumbnail" class="thumbnail active">
                        <img src="../images/hoodie-green.png" alt="Alternative View" class="thumbnail">
                        <img src="../images/hoodie-black.png" alt="Back View" class="thumbnail">
                        <img src="../images/hoodie-black.png" alt="Detail View" class="thumbnail">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-rating">
                        <?php 
                        $rating = $product['rating'];
                        $full_stars = floor($rating);
                        $half_star = $rating - $full_stars >= 0.5;
                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                        
                        for($i = 0; $i < $full_stars; $i++): 
                        ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        
                        <?php if($half_star): ?>
                        <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        
                        <?php for($i = 0; $i < $empty_stars; $i++): ?>
                        <i class="far fa-star"></i>
                        <?php endfor; ?>
                        
                        <span>(<?php echo $product['rating_count']; ?> reviews)</span>
                    </div>
                    <?php if($product['is_sale'] && $product['sale_price']): ?>
                    <p class="product-price">₹<?php echo number_format($product['sale_price']); ?> <span class="original-price">₹<?php echo number_format($product['price']); ?></span></p>
                    <?php else: ?>
                    <p class="product-price">₹<?php echo number_format($product['price']); ?></p>
                    <?php endif; ?>
                    <div class="product-description">
                        <p><?php echo $product['description']; ?></p>
                    </div>

                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value">PID-<?php echo $product['product_id']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Availability:</span>
                            <span class="meta-value"><?php echo $product['stock_quantity'] > 0 ? 'In Stock (' . $product['stock_quantity'] . ' items)' : 'Out of Stock'; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($category['name']); ?></span>
                        </div>
                    </div>

                    <div class="product-options">
                        <h3>Color:</h3>
                        <div class="color-options">
                            <div class="color-option active" style="background-color: #000000;" data-color="Black"></div>
                            <div class="color-option" style="background-color: #2E8B57;" data-color="Green"></div>
                            <div class="color-option" style="background-color: #4169E1;" data-color="Blue"></div>
                            <div class="color-option" style="background-color: #8B4513;" data-color="Brown"></div>
                        </div>

                        <h3>Size:</h3>
                        <div class="size-options">
                            <div class="size-option">S</div>
                            <div class="size-option active">M</div>
                            <div class="size-option">L</div>
                            <div class="size-option">XL</div>
                            <div class="size-option">XXL</div>
                        </div>

                        <h3>Quantity:</h3>
                        <div class="quantity-selector">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="quantity-input">
                            <button class="quantity-btn plus">+</button>
                        </div>
                    </div>

                    <button class="add-to-cart-large" data-product-id="<?php echo $product['product_id']; ?>"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                    <button class="wishlist-btn"><i class="far fa-heart"></i> Add to Wishlist</button>
                </div>
            </div>

            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="description">Description</button>
                    <button class="tab-button" data-tab="specifications">Specifications</button>
                    <button class="tab-button" data-tab="reviews">Reviews (<?php echo $product['rating_count']; ?>)</button>
                </div>

                <div class="tab-content active" id="description">
                    <h3>Product Description</h3>
                    <p><?php echo $product['description']; ?></p>
                    <p>Features:</p>
                    <ul>
                        <li>Premium cotton blend (80% cotton, 20% polyester)</li>
                        <li>Adjustable drawstring hood</li>
                        <li>Kangaroo pocket for convenience</li>
                        <li>Ribbed cuffs and hem for a secure fit</li>
                        <li>Machine washable</li>
                    </ul>
                    <p>This versatile hoodie pairs perfectly with jeans, joggers, or shorts for a casual yet stylish look. Available in multiple colors and sizes to suit your personal style.</p>
                </div>

                <div class="tab-content" id="specifications">
                    <h3>Product Specifications</h3>
                    <table class="specs-table">
                        <tr>
                            <td>Material</td>
                            <td>80% Cotton, 20% Polyester</td>
                        </tr>
                        <tr>
                            <td>Weight</td>
                            <td>350 GSM</td>
                        </tr>
                        <tr>
                            <td>Fit</td>
                            <td>Regular</td>
                        </tr>
                        <tr>
                            <td>Closure</td>
                            <td>Full front zipper</td>
                        </tr>
                        <tr>
                            <td>Pockets</td>
                            <td>1 kangaroo pocket</td>
                        </tr>
                        <tr>
                            <td>Care Instructions</td>
                            <td>Machine wash cold, tumble dry low</td>
                        </tr>
                        <tr>
                            <td>Country of Origin</td>
                            <td>India</td>
                        </tr>
                    </table>
                </div>

                <div class="tab-content" id="reviews">
                    <h3>Customer Reviews</h3>
                    <div class="review-summary">
                        <div class="average-rating">
                            <span class="rating-number"><?php echo number_format($product['rating'], 1); ?></span>
                            <div class="stars">
                                <?php 
                                for($i = 0; $i < $full_stars; $i++): 
                                ?>
                                <i class="fas fa-star"></i>
                                <?php endfor; ?>
                                
                                <?php if($half_star): ?>
                                <i class="fas fa-star-half-alt"></i>
                                <?php endif; ?>
                                
                                <?php for($i = 0; $i < $empty_stars; $i++): ?>
                                <i class="far fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="total-reviews">Based on <?php echo $product['rating_count']; ?> reviews</span>
                        </div>
                        <div class="rating-bars">
                            <div class="rating-bar">
                                <span>5 Stars</span>
                                <div class="bar-container">
                                    <div class="bar" style="width: 70%;"></div>
                                </div>
                                <span>70%</span>
                            </div>
                            <div class="rating-bar">
                                <span>4 Stars</span>
                                <div class="bar-container">
                                    <div class="bar" style="width: 20%;"></div>
                                </div>
                                <span>20%</span>
                            </div>
                            <div class="rating-bar">
                                <span>3 Stars</span>
                                <div class="bar-container">
                                    <div class="bar" style="width: 5%;"></div>
                                </div>
                                <span>5%</span>
                            </div>
                            <div class="rating-bar">
                                <span>2 Stars</span>
                                <div class="bar-container">
                                    <div class="bar" style="width: 3%;"></div>
                                </div>
                                <span>3%</span>
                            </div>
                            <div class="rating-bar">
                                <span>1 Star</span>
                                <div class="bar-container">
                                    <div class="bar" style="width: 2%;"></div>
                                </div>
                                <span>2%</span>
                            </div>
                        </div>
                    </div>

                    <div class="customer-reviews">
                        <div class="review-item">
                            <div class="reviewer-info">
                                <img src="../images/m1.png" alt="Reviewer" class="reviewer-avatar">
                                <div>
                                    <h4>Rahul Sharma</h4>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="review-date">June 15, 2023</span>
                                </div>
                            </div>
                            <p class="review-text">Absolutely love this hoodie! The material is soft yet durable, and it keeps me warm during chilly evenings. The fit is perfect - not too tight, not too loose. Will definitely order more in different colors.</p>
                        </div>

                        <div class="review-item">
                            <div class="reviewer-info">
                                <img src="../images/m2.png" alt="Reviewer" class="reviewer-avatar">
                                <div>
                                    <h4>Priya Patel</h4>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="review-date">May 28, 2023</span>
                                </div>
                            </div>
                            <p class="review-text">Great quality hoodie for the price. The material is thick and warm, perfect for winter. The only reason I'm giving 4 stars is because the sizing runs a bit large. I would recommend sizing down.</p>
                        </div>
                    </div>

                    <button class="load-more-reviews">Load More Reviews</button>
                </div>
            </div>

            <!-- Related Products -->
            <div class="related-products">
                <h2 class="section-title">You May Also Like</h2>
                <div class="product-carousel">
                    <?php foreach($related_products as $related): ?>
                    <div class="product-card">
                        <?php if($related['is_new']): ?>
                        <div class="product-badge">New</div>
                        <?php elseif($related['is_sale']): ?>
                        <div class="product-badge sale">Sale</div>
                        <?php endif; ?>
                        <a href="product-details.php?id=<?php echo $related['product_id']; ?>">
                            <img src="<?php echo $related['image_url']; ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                            <?php if($related['is_sale'] && $related['sale_price']): ?>
                            <p class="product-price">₹<?php echo number_format($related['sale_price']); ?> <span class="original-price">₹<?php echo number_format($related['price']); ?></span></p>
                            <?php else: ?>
                            <p class="product-price">₹<?php echo number_format($related['price']); ?></p>
                            <?php endif; ?>
                            <button class="add-to-cart-btn" data-product-id="<?php echo $related['product_id']; ?>">Add to Cart</button>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get updates on new arrivals, special offers and more</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">MyCling</div>
                    <p>Your one-stop destination for trendy and comfortable clothing.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="men.php">Men</a></li>
                        <li><a href="women.php">Women</a></li>
                        <li><a href="product.php">Products</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Shipping Policy</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <div class="footer-info">
                        <div class="contact">+123 456 7890</div>
                        <div class="email">support@mycling.com</div>
                        <div class="insta">@my_cling</div>
                        <div class="address">123 Fashion Street, Design City</div>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                Copyright &copy; MyCling <?php echo date('Y'); ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../js/cart.js"></script>
    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navMenu = document.querySelector('nav ul');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
        
        // Header Scroll Effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Animation on Scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.classList.add('visible');
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
        
        // Product Image Gallery
        const mainImage = document.querySelector('.main-image');
        const thumbnails = document.querySelectorAll('.thumbnail');
        
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                // Update main image
                mainImage.src = thumbnail.src;
                
                // Update active thumbnail
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                thumbnail.classList.add('active');
            });
        });
        
        // Color Options
        const colorOptions = document.querySelectorAll('.color-option');
        
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                colorOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
            });
        });
        
        // Size Options
        const sizeOptions = document.querySelectorAll('.size-option');
        
        sizeOptions.forEach(option => {
            option.addEventListener('click', () => {
                sizeOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
            });
        });
        
        // Quantity Selector
        const quantityInput = document.querySelector('.quantity-input');
        const minusBtn = document.querySelector('.minus');
        const plusBtn = document.querySelector('.plus');
        
        minusBtn.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
        
        plusBtn.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.getAttribute('max'));
            if (value < max) {
                quantityInput.value = value + 1;
            }
        });
        
        // Product Tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                // Update active tab button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Update active tab content
                tabContents.forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Add to Cart Button
        const addToCartBtn = document.querySelector('.add-to-cart-large');
        
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(document.querySelector('.quantity-input').value);
                
                // Add to cart via AJAX
                fetch('../php/cart_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const originalText = addToCartBtn.innerHTML;
                        addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
                        
                        // Update cart count in header
                        const cartLink = document.querySelector('nav ul li a[href="cart.php"]');
                        let cartCountElem = document.querySelector('.cart-count');
                        
                        if (data.summary.total_items > 0) {
                            if (!cartCountElem) {
                                cartCountElem = document.createElement('span');
                                cartCountElem.className = 'cart-count';
                                cartLink.appendChild(cartCountElem);
                            }
                            cartCountElem.textContent = data.summary.total_items;
                        }
                        
                        setTimeout(() => {
                            addToCartBtn.innerHTML = originalText;
                        }, 2000);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        }
        
        // Wishlist Button
        const wishlistBtn = document.querySelector('.wishlist-btn');
        
        wishlistBtn.addEventListener('click', () => {
            const heartIcon = wishlistBtn.querySelector('i');
            
            if (heartIcon.classList.contains('far')) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                heartIcon.style.color = '#FF6B6B';
            } else {
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
                heartIcon.style.color = '';
            }
        });
    </script>
</body>
</html> 