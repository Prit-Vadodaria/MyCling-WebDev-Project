<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Get cart summary for header
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Get trending products
$trending_query = "SELECT * FROM products WHERE is_featured = 1 ORDER BY is_new DESC LIMIT 5";
$trending_result = mysqli_query($conn, $trending_query);
$trending_products = [];
while($row = mysqli_fetch_assoc($trending_result)) {
    $trending_products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
    <style>
      /* Account Dropdown Styles */
      .account-dropdown {
          position: relative;
      }
      
      .account-dropdown .account-link {
          display: flex;
          align-items: center;
          gap: 5px;
      }
      
      .account-dropdown .fa-chevron-down {
          font-size: 12px;
          transition: transform 0.3s;
      }
      
      .account-dropdown:hover .fa-chevron-down {
          transform: rotate(180deg);
      }
      
      .dropdown-menu {
          position: absolute;
          top: 100%;
          right: 0;
          min-width: 180px;
          background-color: white;
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
          border-radius: var(--border-radius);
          padding: 8px 0;
          display: none;
          z-index: 1000;
      }
      
      .account-dropdown:hover .dropdown-menu {
          display: block;
          animation: fadeInDown 0.3s ease;
      }
      
      @keyframes fadeInDown {
          from {
              opacity: 0;
              transform: translateY(-10px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }
      
      .dropdown-menu a {
          display: flex;
          align-items: center;
          gap: 10px;
          padding: 10px 15px;
          color: var(--text-color);
          transition: all 0.2s;
      }
      
      .dropdown-menu a:hover {
          background-color: rgba(107, 76, 230, 0.1);
          color: var(--primary-color);
      }
      
      .dropdown-menu a i {
          width: 16px;
          text-align: center;
      }
    </style>
  </head>
  <body>
    <!-- Header -->
    <header>
      <nav>
        <div class="logo">MyCling</div>
        <ul>
          <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
          <li><a href="men.php"><i class="fas fa-male"></i> Men</a></li>
          <li><a href="women.php"><i class="fas fa-female"></i> Women</a></li>
          <li><a href="product.php"><i class="fas fa-tshirt"></i> Products</a></li>
          <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart <?php if($total_items > 0): ?><span class="cart-count"><?php echo $total_items; ?></span><?php endif; ?></a></li>
          <li class="account-dropdown">
            <?php if($is_logged_in): ?>
            <a href="#" class="account-link"><i class="fas fa-user"></i> Account <i class="fas fa-chevron-down"></i></a>
            <div class="dropdown-menu">
              <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
              <a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
              <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            <?php else: ?>
            <a href="login.php"><i class="fas fa-user"></i> Login</a>
            <?php endif; ?>
          </li>
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

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <p class="logoname">MyCling</p>
        <a href="product.php"><button class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></button></a>
      </div>
    </section>
    
    <div class="container">
      <!-- Featured Products Section -->
      <section class="section1">
        <h2 class="section-title fade-in">Featured Collections</h2>
        
        <div class="discountsandcoomingsoon">
          <!-- Left Card: Special Offer Hoodies -->
          <div class="left-card">
            <div class="image-container">
              <img
                src="../images/hoodie-green.png"
                class="green-hoodie"
                alt="Green Hoodie"
              />
              <img
                src="../images/hoodie-black.png"
                class="black-hoodie"
                alt="Black Hoodie"
              />
              <!-- <img
                src="../images/pixelcut-export.png"
                class="discount-tape"
                alt="discount"
              /> -->
            </div>
            <h3>Premium Hoodies</h3>
            <p>Limited time offer - 30% off on all hoodies</p>
            <a href="product.php?category=1"><button class="explore-btn">Explore Now <i class="fas fa-arrow-right"></i></button></a>
          </div>

          <!-- Right Side: Coming Soon -->
          <div class="right-side">
            <div class="coming-soon-ethnics">
              <p>Ethnic Collection</p>
              <span class="coming-soon-label">Coming Soon</span>
            </div>
            <div class="coming-soon-shoes">
              <p class="text">Footwear Collection</p>
              <span class="coming-soon-label">Coming Soon</span>
            </div>
          </div>
        </div>
        
        <!-- Product Carousel Title -->
        <h3 class="carousel-title slide-in-left">Trending Products</h3>
        
        <!-- Bottom Product Carousel -->
        <div class="product-carousel">
          <?php foreach($trending_products as $product): ?>
          <div class="product-card">
            <?php if($product['is_new']): ?>
            <div class="product-badge">New</div>
            <?php elseif($product['is_sale']): ?>
            <div class="product-badge sale">Sale</div>
            <?php endif; ?>
            <a href="product-details.php?id=<?php echo $product['product_id']; ?>">
              <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
              <h3><?php echo htmlspecialchars($product['name']); ?></h3>
              <?php if($product['is_sale'] && $product['sale_price']): ?>
              <p class="product-price">₹<?php echo number_format($product['sale_price']); ?> <span class="original-price">₹<?php echo number_format($product['price']); ?></span></p>
              <?php else: ?>
              <p class="product-price">₹<?php echo number_format($product['price']); ?></p>
              <?php endif; ?>
              <button class="add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <!-- AI Upload Section -->
    <section class="ai_upload">
      <p class="upload-title">Create & Own Your Design!</p>
      <div class="upload-section">
        <!-- Upload Box -->
        <div class="upload-box">
          <input type="file" id="file-input" style="display: none" />
          <label for="file-input" class="upload-btn"><i class="fas fa-cloud-upload-alt"></i> Upload Your Design</label>
        </div>

        <!-- Info Box -->
        <div class="info-box">
          <p class="info-text">Let our experts bring your unique design to life!</p>
          <a href="appointment.php" class="appointment-btn"><i class="fas fa-calendar-alt"></i> Book an Appointment</a>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2 class="section-title fade-in">Why Choose Us</h2>
        <div class="features-grid">
          <div class="feature-card">
            <i class="fas fa-truck-fast"></i>
            <h3>Fast Delivery</h3>
            <p>Free shipping on all orders above ₹999</p>
          </div>
          <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Secure Payment</h3>
            <p>Multiple secure payment options</p>
          </div>
          <div class="feature-card">
            <i class="fas fa-medal"></i>
            <h3>Premium Quality</h3>
            <p>Handpicked fabrics for ultimate comfort</p>
          </div>
          <div class="feature-card">
            <i class="fas fa-exchange-alt"></i>
            <h3>Easy Returns</h3>
            <p>30-day easy return policy</p>
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
    </script>
  </body>
</html> 