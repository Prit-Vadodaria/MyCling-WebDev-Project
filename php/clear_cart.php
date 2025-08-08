<?php
require_once 'config.php';
require_once 'cart_functions.php';

// Clear the cart
clearCart();

// Send success response
echo json_encode(['success' => true]);
?> 