<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Enable error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

if(!$is_logged_in) {
    header("Location: login.php?redirect=orders");
    exit();
}

// Get user orders
$user_id = $_SESSION['user_id'];
$orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders_result = false;

try {
    $stmt = mysqli_prepare($conn, $orders_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $orders_result = mysqli_stmt_get_result($stmt);
    } else {
        error_log("Failed to prepare statement: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    error_log("Error querying orders: " . $e->getMessage());
}

// Get cart items count for header
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
    <style>
        /* Orders Page Styles */
        .orders-section {
            padding: 40px 0;
        }
        
        .order-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background-color: rgba(107, 76, 230, 0.05);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .order-id {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 16px;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            padding: 25px;
        }
        
        .order-detail-row {
            display: flex;
            margin-bottom: 20px;
        }
        
        .order-detail-col {
            flex: 1;
        }
        
        .detail-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--text-color);
        }
        
        .order-items-container {
            margin-top: 25px;
            border-top: 1px solid #f1f1f1;
            padding-top: 20px;
        }
        
        .order-items-heading {
            margin-bottom: 15px;
            font-size: 16px;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .order-items {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .order-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-right: 15px;
            border: 1px solid #f1f1f1;
        }
        
        .order-item-details {
            flex-grow: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .order-item-meta {
            font-size: 13px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .order-summary {
            margin-top: 20px;
            border-top: 1px solid #f1f1f1;
            padding-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-weight: 600;
            font-size: 16px;
            color: var(--primary-color);
        }
        
        .empty-orders {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 25px rgba(0, 0, 0, 0.08);
        }
        
        .empty-orders i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-orders h3 {
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .empty-orders p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .shop-now-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .shop-now-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
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
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-date {
                margin: 5px 0 10px 0;
            }
            
            .order-detail-row {
                flex-direction: column;
            }
            
            .order-detail-col {
                margin-bottom: 15px;
            }
        }
    </style>
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
                        <a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> My Orders</a>
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
            <h1>My Orders</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>My Orders</span>
            </div>
        </div>
    </section>

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="container">
            <?php if($orders_result && mysqli_num_rows($orders_result) > 0): ?>
                <?php while($order = mysqli_fetch_assoc($orders_result)): 
                    // Get order items
                    $order_id = $order['order_id'];
                    $items_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.product_id 
                                  WHERE oi.order_id = ?";
                    $items_result = false;
                    
                    try {
                        $items_stmt = mysqli_prepare($conn, $items_query);
                        if ($items_stmt) {
                            mysqli_stmt_bind_param($items_stmt, "i", $order_id);
                            mysqli_stmt_execute($items_stmt);
                            $items_result = mysqli_stmt_get_result($items_stmt);
                        } else {
                            error_log("Failed to prepare items statement: " . mysqli_error($conn));
                        }
                    } catch (Exception $e) {
                        error_log("Error querying order items: " . $e->getMessage());
                    }
                    
                    // Calculate order totals
                    $items_total = 0;
                    $items = [];
                    
                    if ($items_result) {
                        while($item = mysqli_fetch_assoc($items_result)) {
                            $items[] = $item;
                            $items_total += $item['price'] * $item['quantity'];
                        }
                    }
                    
                    $shipping = 150;
                    $tax = round($items_total * 0.18);
                    $total = $items_total + $shipping + $tax;
                    
                    // Determine status class
                    $status_class = '';
                    if (isset($order['status'])) {
                        switch(strtolower($order['status'])) {
                            case 'pending':
                                $status_class = 'status-pending';
                                break;
                            case 'paid':
                                $status_class = 'status-paid';
                                break;
                            case 'shipped':
                                $status_class = 'status-shipped';
                                break;
                            case 'delivered':
                                $status_class = 'status-delivered';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                break;
                        }
                    }
                ?>
                <div class="order-container">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="order-status <?php echo $status_class; ?>">
                            <?php echo $order['status']; ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-detail-row">
                            <div class="order-detail-col">
                                <div class="detail-label">Shipping Address</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($order['shipping_name']); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> <?php echo htmlspecialchars($order['shipping_zip']); ?>
                                </div>
                            </div>
                            
                            <div class="order-detail-col">
                                <div class="detail-label">Contact Information</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($order['shipping_email']); ?><br>
                                    <?php echo htmlspecialchars($order['shipping_phone']); ?>
                                </div>
                            </div>
                            
                            <div class="order-detail-col">
                                <div class="detail-label">Payment Method</div>
                                <div class="detail-value">
                                    <?php if($order['payment_method'] == 'cod'): ?>
                                        Cash on Delivery
                                    <?php elseif($order['payment_method'] == 'qr_code'): ?>
                                        QR Code Payment<br>
                                        <small>Transaction ID: <?php echo htmlspecialchars($order['transaction_id']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-items-container">
                            <div class="order-items-heading">Order Items</div>
                            <div class="order-items">
                                <?php 
                                // Make sure $items is not empty before trying to iterate
                                if (!empty($items)):
                                    foreach($items as $item): 
                                ?>
                                <div class="order-item">
                                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                                    <div class="order-item-details">
                                        <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="order-item-meta">
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                            <span>₹<?php echo number_format($item['price']); ?> each</span>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <div class="order-item">
                                    <p>No items found for this order.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span>₹<?php echo number_format($items_total); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping</span>
                                    <span>₹<?php echo number_format($shipping); ?></span>
                                </div>
                                <div class="summary-row">
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
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start shopping and place your first order!</p>
                    <a href="product.php" class="shop-now-btn">Shop Now <i class="fas fa-arrow-right"></i></a>
                </div>
            <?php endif; ?>
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
    });
    </script>
</body>
</html> 