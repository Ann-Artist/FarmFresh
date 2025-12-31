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
$customer_id = $_SESSION['user_id'];

// Validate input
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

// Delete cart item (verify it belongs to this customer)
$delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
$delete_stmt->bind_param("ii", $cart_id, $customer_id);

if ($delete_stmt->execute()) {
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Item removed from cart'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Cart item not found or already removed'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}
?>