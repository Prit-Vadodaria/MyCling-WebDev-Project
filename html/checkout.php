<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Check if user is logged in, redirect to login if not
if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

// Get cart items and summary
$cart_items = getCartItems();
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];
$subtotal = $cart_summary['total_price'];

// If cart is empty, redirect to cart page
if($total_items == 0) {
    header("Location: cart.php");
    exit();
}

// Calculate shipping, tax, and total
$shipping = ($subtotal > 0) ? 150 : 0;
$tax = round($subtotal * 0.18); // 18% tax
$total = $subtotal + $shipping + $tax;

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Process checkout form submission
$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = isset($_POST['transaction_id']) ? mysqli_real_escape_string($conn, $_POST['transaction_id']) : '';
    
    // Validate form data
    if(empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($zip)) {
        $error_message = "Please fill in all required fields.";
    } elseif($payment_method == 'qr_code' && empty($transaction_id)) {
        $error_message = "Please enter the transaction ID for QR code payment.";
    } else {
        // Create order
        $order_date = date('Y-m-d H:i:s');
        $status = ($payment_method == 'qr_code') ? 'Paid' : 'Pending';
        
        // Insert order into database using prepared statements
        $order_query = "INSERT INTO orders (user_id, created_at, total_amount, status, payment_method, transaction_id, 
                       shipping_address, shipping_fee, tax,
                       shipping_name, shipping_email, shipping_phone, shipping_city, shipping_state, shipping_zip) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $shipping_address = $address . ", " . $city . ", " . $state . " " . $zip;
        $shipping_fee = $shipping;
        
        $stmt = mysqli_prepare($conn, $order_query);
        mysqli_stmt_bind_param($stmt, "isdsssddsssssss", 
                              $user_id, $order_date, $total, $status, $payment_method, $transaction_id,
                              $shipping_address, $shipping_fee, $tax,
                              $full_name, $email, $phone, $city, $state, $zip);
        
        if(mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items using prepared statements
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($conn, $item_query);
            
            foreach($cart_items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['is_sale'] && $item['sale_price'] ? $item['sale_price'] : $item['price'];
                
                mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $product_id, $quantity, $price);
                mysqli_stmt_execute($item_stmt);
            }
            
            // Clear the cart
            clearCart();
            
            // Show success message and redirect
            $success_message = "Your order has been placed successfully!";
            
            // Redirect to order confirmation page
            header("Refresh: 3; URL=index.php");
        } else {
            $error_message = "Error placing order: " . mysqli_error($conn);
        }
    }
}

// Get user details for pre-filling the form
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/checkout_styles.css">
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

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Checkout</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <a href="cart.php">Cart</a> / <span>Checkout</span>
            </div>
        </div>
    </section>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <?php if($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo $success_message; ?></div>
            </div>
            <?php elseif($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo $error_message; ?></div>
            </div>
            <?php endif; ?>
            
            <div class="checkout-container">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form method="POST" action="checkout.php" id="checkoutForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip">Postal Code</label>
                            <input type="text" id="zip" name="zip" required>
                        </div>
                        
                        <div class="payment-options">
                            <h2>Payment Method</h2>
                            
                            <div class="payment-option active">
                                <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                <label for="cod" class="payment-option-label">
                                    <div class="payment-option-info">
                                        <i class="fas fa-money-bill-wave payment-icon"></i>
                                        Cash on Delivery
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="qr_code" name="payment_method" value="qr_code">
                                <label for="qr_code" class="payment-option-label">
                                    <div class="payment-option-info">
                                        <i class="fas fa-qrcode payment-icon"></i>
                                        Pay with QR Code
                                    </div>
                                </label>
                            </div>
                            
                            <div class="transaction-id-container" id="transactionIdContainer">
                                <div class="qr-code-container">
                                    <img src="../images/qr_code.jpg" alt="Payment QR Code">
                                    <p>Scan this QR code to make payment</p>
                                </div>
                                <div class="form-group">
                                    <label for="transaction_id">Enter Transaction ID</label>
                                    <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter the transaction ID after payment">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="checkout-btn">
                            Complete Order <i class="fas fa-check-circle"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="order-items">
                        <?php foreach($cart_items as $item): 
                            $unit_price = $item['is_sale'] && $item['sale_price'] ? $item['sale_price'] : $item['price'];
                            $total_price = $unit_price * $item['quantity'];
                        ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                            <div class="order-item-details">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="order-item-meta">
                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                    <span>₹<?php echo number_format($total_price); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($subtotal); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span>₹<?php echo number_format($shipping); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (18%)</span>
                        <span>₹<?php echo number_format($tax); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($total); ?></span>
                    </div>
                </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Payment method selection
        const paymentOptions = document.querySelectorAll('.payment-option');
        const transactionIdContainer = document.getElementById('transactionIdContainer');
        const transactionIdInput = document.getElementById('transaction_id');
        
        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                paymentOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                this.classList.add('active');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Show/hide transaction ID field
                if (radio.value === 'qr_code') {
                    transactionIdContainer.style.display = 'block';
                    transactionIdInput.setAttribute('required', 'required');
                } else {
                    transactionIdContainer.style.display = 'none';
                    transactionIdInput.removeAttribute('required');
                }
            });
        });
        
        // Form validation
        const checkoutForm = document.getElementById('checkoutForm');
        
        checkoutForm.addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const transactionId = document.getElementById('transaction_id').value;
            
            if (paymentMethod === 'qr_code' && !transactionId) {
                e.preventDefault();
                alert('Please enter the transaction ID for QR code payment.');
            }
        });
        
        // Auto-hide success message
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 0.5s';
            }, 2500);
        }
    });
    </script>
</body>
</html> 