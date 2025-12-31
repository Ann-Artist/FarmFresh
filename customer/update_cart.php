<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn() || !isCustomer()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$customer_id = $_SESSION['user_id'];

// Validate input
if ($cart_id <= 0 || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Check if cart item belongs to customer and get product stock
$check_sql = "SELECT c.*, p.quantity as stock, p.name as product_name 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.id = ? AND c.customer_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $cart_id, $customer_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit();
}

$cart_item = $result->fetch_assoc();

// Check stock availability
if ($quantity > $cart_item['stock']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Only ' . $cart_item['stock'] . ' items available in stock'
    ]);
    exit();
}

// Update cart quantity
$update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND customer_id = ?");
$update_stmt->bind_param("iii", $quantity, $cart_id, $customer_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Cart updated successfully',
        'quantity' => $quantity
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}
?>