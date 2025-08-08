<?php
require_once 'cart_functions.php';

// Set content type as JSON
header('Content-Type: application/json');

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($request_method) {
    case 'GET':
        // Get cart items or cart summary
        if (isset($_GET['summary']) && $_GET['summary'] == 'true') {
            echo json_encode(getCartSummary());
        } else {
            echo json_encode([
                'items' => getCartItems(),
                'summary' => getCartSummary()
            ]);
        }
        break;
        
    case 'POST':
        // Add item to cart
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['product_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        $product_id = intval($data['product_id']);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
        
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Quantity must be positive']);
            exit;
        }
        
        if (addToCart($product_id, $quantity)) {
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'summary' => getCartSummary()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add product to cart']);
        }
        break;
        
    case 'PUT':
        // Update cart item quantity
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID and quantity are required']);
            exit;
        }
        
        $product_id = intval($data['product_id']);
        $quantity = intval($data['quantity']);
        
        if (updateCartQuantity($product_id, $quantity)) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'summary' => getCartSummary()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cart']);
        }
        break;
        
    case 'DELETE':
        // Remove item from cart
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['product_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }
        
        $product_id = intval($data['product_id']);
        
        if (removeFromCart($product_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart',
                'summary' => getCartSummary()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to remove product from cart']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 