<?php
require_once '../php/cart_functions.php';
require_once '../php/config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Get cart items and summary
$cart_items = getCartItems();
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];
$subtotal = $cart_summary['total_price'];

// Calculate shipping, tax, and total
$shipping = ($subtotal > 0) ? 150 : 0; // Free shipping over a certain amount could be implemented
$tax = round($subtotal * 0.18); // 18% tax
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
    <style>
        /* Highlight effect for updated cart items */
        .highlight {
            animation: highlight-effect 0.5s ease-in-out;
        }
        
        @keyframes highlight-effect {
            0% { background-color: rgba(107, 76, 230, 0.1); }
            50% { background-color: rgba(107, 76, 230, 0.2); }
            100% { background-color: transparent; }
        }
        
        /* Removing animation for cart items */
        .removing {
            animation: remove-item 0.3s ease-out forwards;
        }
        
        @keyframes remove-item {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(-30px); }
        }
        
        /* Login notification */
        .login-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: rgba(255, 107, 107, 0.95);
            color: white;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .login-notification.show {
            transform: translateX(0);
        }
        
        .login-notification i {
            font-size: 20px;
        }
        
        .login-notification-content {
            flex-grow: 1;
        }
        
        .login-notification-content h4 {
            margin: 0 0 5px 0;
        }
        
        .login-notification-content p {
            margin: 0;
            font-size: 14px;
        }
        
        .login-notification .close-notification {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
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
        
        /* Empty Cart with Orders Styles */
        .empty-cart-section {
            padding: 20px 0;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 25px rgba(0, 0, 0, 0.08);
        }
        
        .empty-cart i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h3 {
            margin-bottom: 15px;
            color: var(--text-color);
            font-size: 22px;
        }
        
        .empty-cart p {
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
        
        .empty-cart-with-orders {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .empty-cart-with-orders {
                grid-template-columns: 1fr;
            }
        }
        
        .empty-cart-column {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 25px rgba(0, 0, 0, 0.08);
        }
        
        .empty-cart-column i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart-column h3 {
            margin-bottom: 15px;
            color: var(--text-color);
            font-size: 20px;
        }
        
        .empty-cart-column p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .recent-orders-column {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 25px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        
        .recent-orders-column h3 {
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 20px;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 10px;
        }
        
        .recent-orders-column h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        
        .recent-orders-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .recent-order {
            margin-bottom: 20px;
            border: 1px solid #f1f1f1;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .recent-order:last-child {
            margin-bottom: 15px;
        }
        
        .recent-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: rgba(107, 76, 230, 0.05);
            border-bottom: 1px solid #f5f5f5;
        }
        
        .recent-order-id {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
        }
        
        .recent-order-date {
            color: #666;
            font-size: 13px;
        }
        
        .recent-order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
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
        
        .recent-order-preview {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .recent-order-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-right: 15px;
            border: 1px solid #f1f1f1;
        }
        
        .recent-order-preview-details {
            flex-grow: 1;
        }
        
        .recent-order-preview-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .recent-order-preview-meta {
            font-size: 13px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .additional-items {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .recent-order-actions {
            display: flex;
            justify-content: space-between;
            padding: 15px;
        }
        
        .view-order-btn {
            display: inline-block;
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius);
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .view-order-btn:hover {
            background-color: rgba(107, 76, 230, 0.05);
        }
        
        .buy-again-form {
            margin: 0;
        }
        
        .buy-again-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 8px 15px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .buy-again-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .view-all-orders-btn {
            display: block;
            text-align: center;
            padding: 10px;
            color: var(--primary-color);
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .view-all-orders-btn:hover {
            background-color: rgba(107, 76, 230, 0.05);
        }
        
        .no-recent-orders {
            text-align: center;
            padding: 30px 20px;
            color: #666;
        }
        
        /* Cart Container Styles */
        .cart-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .cart-heading h2 {
            font-size: 24px;
            margin: 0;
            color: var(--text-color);
        }
        
        .clear-cart-btn {
            display: inline-flex;
            align-items: center;
            color: #dc3545;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .clear-cart-btn:hover {
            opacity: 0.8;
        }
        
        .clear-cart-btn i {
            margin-right: 5px;
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
                <li><a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart <?php if($total_items > 0): ?><span class="cart-count"><?php echo $total_items; ?></span><?php endif; ?></a></li>
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
            <h1>Shopping Cart</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Cart</span>
            </div>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if($total_items > 0): ?>
            <!-- Cart Heading -->
            <div class="cart-heading">
                <h2>Your Shopping Cart (<?php echo $total_items; ?> items)</h2>
            </div>
            <!-- Cart with Items -->
            <div class="cart-items">
                <div class="cart-header">
                    <div class="cart-product">Product</div>
                    <div class="cart-quantity">Quantity</div>
                    <div class="cart-price">Price</div>
                    <div class="cart-total">Total</div>
                    <div class="cart-remove">Remove</div>
                </div>
                
                <?php foreach($cart_items as $item): 
                    $unit_price = $item['is_sale'] && $item['sale_price'] ? $item['sale_price'] : $item['price'];
                    $total_price = $unit_price * $item['quantity'];
                ?>
                <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                    <div class="cart-product">
                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="cart-product-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <?php if($item['is_sale'] && $item['sale_price']): ?>
                            <p>₹<?php echo number_format($item['sale_price']); ?> <span class="original-price">₹<?php echo number_format($item['price']); ?></span></p>
                            <?php else: ?>
                            <p>₹<?php echo number_format($item['price']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="cart-quantity">
                        <button class="cart-quantity-btn decrease">-</button>
                        <input type="number" class="cart-quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                        <button class="cart-quantity-btn increase">+</button>
                    </div>
                    <div class="cart-price">
                        ₹<?php echo number_format($unit_price); ?>
                    </div>
                    <div class="cart-total">
                        ₹<?php echo number_format($total_price); ?>
                    </div>
                    <div class="cart-remove">
                        <button class="remove-item" data-product-id="<?php echo $item['product_id']; ?>" onclick="removeCartItem(<?php echo $item['product_id']; ?>, this.closest('.cart-item'))"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>₹<?php echo number_format($shipping); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (18%)</span>
                    <span>₹<?php echo number_format($tax); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>₹<?php echo number_format($total); ?></span>
                </div>
                <!-- Cart Actions -->
                <div class="cart-actions">
                    <a href="index.php" class="continue-shopping-btn">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                    <?php if($is_logged_in && $total_items > 0): ?>
                    <a href="checkout.php" class="checkout-btn">
                        Proceed to Payment <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php elseif(!$is_logged_in && $total_items > 0): ?>
                    <a href="login.php?redirect=checkout" class="checkout-btn">
                        Login to Checkout <i class="fas fa-sign-in-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-cart-section">
                <?php if(!$is_logged_in): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your Shopping Cart is Empty</h3>
                    <p>Looks like you haven't added any products to your cart yet.</p>
                    <a href="product.php" class="shop-now-btn">Start Shopping <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php else: ?>
                <div class="empty-cart-with-orders">
                    <div class="empty-cart-column">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your Shopping Cart is Empty</h3>
                        <p>Looks like you haven't added any products to your cart yet.</p>
                        <a href="product.php" class="shop-now-btn">Start Shopping <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="recent-orders-column">
                        <h3>Your Recent Orders</h3>
                        
                        <?php
                        // Get recent orders for logged in user
                        $user_id = $_SESSION['user_id'];
                        $recent_orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 2";
                        $recent_orders_result = mysqli_query($conn, $recent_orders_query);
                        
                        if(mysqli_num_rows($recent_orders_result) > 0):
                        ?>
                        <div class="recent-orders-list">
                            <?php while($order = mysqli_fetch_assoc($recent_orders_result)): 
                                // Get one item from the order as a preview
                                $order_id = $order['order_id'];
                                $preview_item_query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
                                                    JOIN products p ON oi.product_id = p.product_id 
                                                    WHERE oi.order_id = ? LIMIT 1";
                                $preview_stmt = mysqli_prepare($conn, $preview_item_query);
                                mysqli_stmt_bind_param($preview_stmt, "i", $order_id);
                                mysqli_stmt_execute($preview_stmt);
                                $preview_item_result = mysqli_stmt_get_result($preview_stmt);
                                $preview_item = mysqli_fetch_assoc($preview_item_result);
                                
                                // Get count of other items
                                $count_query = "SELECT COUNT(*) as item_count FROM order_items WHERE order_id = ?";
                                $count_stmt = mysqli_prepare($conn, $count_query);
                                mysqli_stmt_bind_param($count_stmt, "i", $order_id);
                                mysqli_stmt_execute($count_stmt);
                                $count_result = mysqli_stmt_get_result($count_stmt);
                                $count_data = mysqli_fetch_assoc($count_result);
                                $additional_items = $count_data['item_count'] - 1;
                                
                                // Determine status class
                                $status_class = '';
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
                            ?>
                            <div class="recent-order">
                                <div class="recent-order-header">
                                    <div>
                                        <div class="recent-order-id">Order #<?php echo $order['order_id']; ?></div>
                                        <div class="recent-order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <div class="recent-order-status <?php echo $status_class; ?>">
                                        <?php echo $order['status']; ?>
                                    </div>
                                </div>
                                
                                <?php if($preview_item): ?>
                                <div class="recent-order-preview">
                                    <img src="<?php echo $preview_item['image_url']; ?>" alt="<?php echo htmlspecialchars($preview_item['name']); ?>" class="recent-order-image">
                                    <div class="recent-order-preview-details">
                                        <div class="recent-order-preview-name"><?php echo htmlspecialchars($preview_item['name']); ?></div>
                                        <div class="recent-order-preview-meta">
                                            <span>Qty: <?php echo $preview_item['quantity']; ?></span>
                                            <?php if($additional_items > 0): ?>
                                            <span class="additional-items">+<?php echo $additional_items; ?> more item<?php echo $additional_items > 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="recent-order-actions">
                                    <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="view-order-btn">View Order Details</a>
                                    <?php if($preview_item): ?>
                                    <form action="cart.php" method="post" class="buy-again-form">
                                        <input type="hidden" name="product_id" value="<?php echo $preview_item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="buy-again-btn">Buy Again</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            
                            <a href="orders.php" class="view-all-orders-btn">View All Orders <i class="fas fa-chevron-right"></i></a>
                        </div>
                        <?php else: ?>
                        <div class="no-recent-orders">
                            <p>You don't have any orders yet. Start shopping to place your first order!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- You May Also Like Section (Shown for both empty and filled carts) -->
    <section class="related-products">
        <div class="container">
            <h2 class="section-title">You May Also Like</h2>
            <div class="product-carousel">
                <?php
                // Get related products
                $sql = "SELECT * FROM products WHERE is_featured = 1 LIMIT 5";
                $result = mysqli_query($conn, $sql);
                while($product = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-card">
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
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                    </a>
                </div>
                <?php endwhile; ?>
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

    <?php if(!$is_logged_in && $total_items > 0): ?>
    <!-- Login Notification -->
    <div class="login-notification" id="loginNotification">
        <i class="fas fa-exclamation-circle"></i>
        <div class="login-notification-content">
            <h4>Login Required</h4>
            <p>Please log in to proceed with your purchase</p>
        </div>
        <button class="close-notification" id="closeNotification">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="../js/cart.js"></script>
</body>
</html> 