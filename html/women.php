<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Get cart summary for header
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];

// Get categories for women's products
$categories_query = "SELECT * FROM categories WHERE name IN ('Dresses', 'Tops', 'Jeans', 'Accessories')";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
while($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}

// Get products for women (using category filter or specific women's products)
$sql = "SELECT * FROM products WHERE category_id IN (3, 4, 5) ORDER BY is_new DESC, is_featured DESC";
$result = mysqli_query($conn, $sql);
$products = [];
while($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Collection - MyCling | Modern Fashion E-Commerce</title>
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
                <li><a href="women.php" class="active"><i class="fas fa-female"></i> Women</a></li>
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
    <section class="page-banner women-banner">
        <div class="container">
        <h1>Women's Collection</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Women</span>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-container">
                <div class="filter-group">
                    <label>Category:</label>
                    <select id="category-filter">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sort By:</label>
                    <select id="sort-filter">
                        <option value="featured">Featured</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Price Range:</label>
                    <div class="price-range">
                        <input type="range" min="0" max="5000" value="5000" class="price-slider" id="price-filter">
                        <div class="price-values">
                            <span>₹0</span>
                            <span>₹5000</span>
                        </div>
                    </div>
                </div>
                <button class="filter-button" id="apply-filters"><i class="fas fa-filter"></i> Apply Filters</button>
                <button class="reset-filter-button" id="reset-filters">Reset</button>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products">
        <div class="container">
            <h2 class="section-title fade-in">Explore Women's Fashion</h2>
            <div class="product-count">
                Showing <span><?php echo count($products); ?></span> products
            </div>
            <div class="product-list" id="product-list">
                <?php if(empty($products)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search terms</p>
                </div>
                <?php else: ?>
                <?php foreach($products as $product): ?>
                <div class="product-card fade-in">
                    <?php if($product['is_new']): ?>
                    <div class="product-badge">New</div>
                    <?php elseif($product['is_sale']): ?>
                    <div class="product-badge sale">Sale</div>
                    <?php endif; ?>
                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <?php if($product['is_sale'] && $product['sale_price']): ?>
                        <p class="product-price">₹<?php echo number_format($product['sale_price']); ?> <span class="original-price">₹<?php echo number_format($product['price']); ?></span></p>
                        <?php else: ?>
                        <p class="product-price">₹<?php echo number_format($product['price']); ?></p>
                        <?php endif; ?>
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
                            
                            <span>(<?php echo $product['rating_count']; ?>)</span>
                        </div>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured-categories">
        <div class="container">
            <h2 class="section-title fade-in">Shop By Category</h2>
            <div class="category-grid">
                <?php 
                // Get main categories for women for display
                $category_query = "SELECT * FROM categories WHERE name IN ('Dresses', 'Accessories', 'Shoes') LIMIT 3";
                $category_result = mysqli_query($conn, $category_query);
                $slide_classes = ['slide-in-left', 'slide-in-right', 'slide-in-left'];
                $slide_index = 0;
                
                while($category = mysqli_fetch_assoc($category_result)):
                    $slide_class = $slide_classes[$slide_index];
                    $slide_index++;
                ?>
                <div class="category-card <?php echo $slide_class; ?>">
                    <img src="../images/hoodie-black.png" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <div class="category-content">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="product.php?category=<?php echo urlencode($category['name']); ?>" class="category-btn">Explore <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Trending Section -->
    <section class="trending-section">
        <div class="container">
            <h2 class="section-title fade-in">Trending Now</h2>
            <div class="trending-banner slide-in-right">
                <div class="trending-content">
                    <h3>Summer Collection 2025</h3>
                    <p>Discover the latest trends for the upcoming season</p>
                    <a href="product.php?collection=summer" class="shop-now">Shop Collection <i class="fas fa-arrow-right"></i></a>
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
                Copyright &copy; MyCling 2025. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/filters.js"></script>
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
        
        // Price Range Slider
        const priceSlider = document.querySelector('.price-slider');
        const priceValues = document.querySelector('.price-values');
        
        if (priceSlider) {
            priceSlider.addEventListener('input', () => {
                const value = priceSlider.value;
                priceValues.innerHTML = `<span>₹0</span><span>₹${value}</span>`;
            });
        }
    </script>
</body>
</html> 