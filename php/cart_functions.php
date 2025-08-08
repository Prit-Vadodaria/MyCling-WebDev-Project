<?php
require_once 'config.php';

/**
 * Get or create a cart for the current user/session
 * 
 * @return int The cart ID
 */
function getCartId() {
    global $conn;
    
    $user_id = getCurrentUserId();
    $session_id = getSessionId();
    
    // Check if cart exists for this user/session
    $sql = "SELECT cart_id FROM cart WHERE ";
    $params = [];
    
    if ($user_id) {
        $sql .= "user_id = ?";
        $params[] = $user_id;
    } else {
        $sql .= "session_id = ? AND user_id IS NULL";
        $params[] = $session_id;
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['cart_id'];
    }
    
    // No cart found, create a new one
    $sql = "INSERT INTO cart (user_id, session_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_id, $session_id);
    mysqli_stmt_execute($stmt);
    
    return mysqli_insert_id($conn);
}

/**
 * Add a product to the cart
 * 
 * @param int $product_id The product ID
 * @param int $quantity The quantity to add (default: 1)
 * @return bool True if successful, false otherwise
 */
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    // Validate product exists
    $product_check = mysqli_query($conn, "SELECT product_id FROM products WHERE product_id = $product_id");
    if (mysqli_num_rows($product_check) == 0) {
        return false;
    }
    
    $cart_id = getCartId();
    
    // Check if product already in cart
    $sql = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Update quantity if product already in cart
        $new_quantity = $row['quantity'] + $quantity;
        $sql = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $row['cart_item_id']);
        return mysqli_stmt_execute($stmt);
    } else {
        // Add new cart item
        $sql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $cart_id, $product_id, $quantity);
        return mysqli_stmt_execute($stmt);
    }
}

/**
 * Remove a product from the cart
 * 
 * @param int $product_id The product ID to remove
 * @return bool True if successful, false otherwise
 */
function removeFromCart($product_id) {
    global $conn;
    
    $cart_id = getCartId();
    
    $sql = "DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Update product quantity in cart
 * 
 * @param int $product_id The product ID
 * @param int $quantity The new quantity
 * @return bool True if successful, false otherwise
 */
function updateCartQuantity($product_id, $quantity) {
    global $conn;
    
    if ($quantity <= 0) {
        return removeFromCart($product_id);
    }
    
    $cart_id = getCartId();
    
    $sql = "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $quantity, $cart_id, $product_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get cart items with product details
 * 
 * @return array The cart items
 */
function getCartItems() {
    global $conn;
    
    $cart_id = getCartId();
    
    $sql = "SELECT ci.*, p.name, p.price, p.sale_price, p.image_url, p.is_sale 
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cart_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['actual_price'] = $row['is_sale'] && $row['sale_price'] ? $row['sale_price'] : $row['price'];
        $row['total'] = $row['actual_price'] * $row['quantity'];
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Get cart summary (total items and total price)
 * 
 * @return array Cart summary with 'total_items' and 'total_price'
 */
function getCartSummary() {
    $items = getCartItems();
    
    $total_items = 0;
    $total_price = 0;
    
    foreach ($items as $item) {
        $total_items += $item['quantity'];
        $total_price += $item['total'];
    }
    
    return [
        'total_items' => $total_items,
        'total_price' => $total_price
    ];
}

/**
 * Clear the entire cart
 * 
 * @return bool True if successful, false otherwise
 */
function clearCart() {
    global $conn;
    
    $cart_id = getCartId();
    
    $sql = "DELETE FROM cart_items WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cart_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get a specific cart item
 * 
 * @param int $product_id The product ID
 * @return array|null The cart item or null if not found
 */
function getCartItem($product_id) {
    global $conn;
    
    $cart_id = getCartId();
    
    $sql = "SELECT ci.*, p.name, p.price, p.sale_price, p.image_url, p.is_sale 
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ? AND ci.product_id = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $row['actual_price'] = $row['is_sale'] && $row['sale_price'] ? $row['sale_price'] : $row['price'];
        $row['total'] = $row['actual_price'] * $row['quantity'];
        return $row;
    }
    
    return null;
}

/**
 * Calculate cart totals
 * 
 * @return array Cart totals
 */
function calculateCartTotals() {
    $summary = getCartSummary();
    
    return [
        'subtotal' => $summary['total_price'],
        'total_items' => $summary['total_items'],
        'shipping' => 0, // You can implement shipping calculation here
        'tax' => 0, // You can implement tax calculation here
        'total' => $summary['total_price'] // Add shipping and tax if needed
    ];
}
?> 