<?php
require_once 'config.php';
require_once 'cart_functions.php';

// Check if product ID and quantity are set
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'error' => 'Product ID and quantity are required']);
    exit;
}

// Get product ID and quantity
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// Ensure quantity is at least 1
if ($quantity < 1) {
    $quantity = 1;
}

// Update cart item quantity
$result = updateCartQuantity($product_id, $quantity);

if ($result) {
    // Get updated cart summary
    $cart_summary = getCartSummary();
    $subtotal = $cart_summary['total_price'];
    $shipping = ($subtotal > 0) ? 150 : 0;
    $tax = round($subtotal * 0.18);
    $total = $subtotal + $shipping + $tax;
    
    // Get updated item info
    $item = getCartItem($product_id);
    $item_total = $item ? $item['total'] : 0;
    
    // Send success response with updated cart data
    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'total_items' => $cart_summary['total_items'],
        'item_total' => $item_total
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update cart quantity']);
}
?> 