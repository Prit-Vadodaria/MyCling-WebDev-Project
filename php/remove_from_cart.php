<?php
require_once 'config.php';
require_once 'cart_functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if product_id is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

// Get product id
$product_id = intval($_POST['product_id']);

// Remove item from cart
$removed = removeFromCart($product_id);

if ($removed) {
    // Get updated cart summary
    $cart_summary = getCartSummary();
    
    // Return success response with updated cart data
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart successfully',
        'total_items' => $cart_summary['total_items'],
        'subtotal' => $cart_summary['total_price'],
        'shipping' => ($cart_summary['total_price'] > 0) ? 150 : 0,
        'tax' => round($cart_summary['total_price'] * 0.18),
        'total' => $cart_summary['total_price'] + (($cart_summary['total_price'] > 0) ? 150 : 0) + round($cart_summary['total_price'] * 0.18)
    ]);
} else {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove item from cart'
    ]);
}
?> 