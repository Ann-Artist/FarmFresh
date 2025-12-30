<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isCustomer()) {
    echo json_encode(['success' => false, 'message' => 'Please login as customer']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$customer_id = $_SESSION['user_id'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit();
}

// Check if product exists and is available
$check_stmt = $conn->prepare("SELECT quantity, status FROM products WHERE id = ?");
$check_stmt->bind_param("i", $product_id);
$check_stmt->execute();
$product = $check_stmt->get_result()->fetch_assoc();

if (!$product || $product['status'] !== 'available') {
    echo json_encode(['success' => false, 'message' => 'Product not available']);
    exit();
}

if ($quantity > $product['quantity']) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit();
}

// Check if already in cart
$cart_check = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
$cart_check->bind_param("ii", $customer_id, $product_id);
$cart_check->execute();
$cart_item = $cart_check->get_result()->fetch_assoc();

if ($cart_item) {
    // Update quantity
    $new_quantity = $cart_item['quantity'] + $quantity;
    if ($new_quantity > $product['quantity']) {
        echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock']);
        exit();
    }
    
    $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $update_stmt->execute();
} else {
    // Add new item
    $insert_stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iii", $customer_id, $product_id, $quantity);
    $insert_stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Product added to cart']);
?>