<?php
$page_title = "Checkout";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$user = getCurrentUser($conn);

// Get cart items
$cart_sql = "SELECT c.*, p.name, p.price, p.farmer_id, p.quantity as stock 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.customer_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result();

if ($cart_items->num_rows == 0) {
    header('Location: cart.php');
    exit();
}

$error = '';
$success = '';

// Calculate totals
$subtotal = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_charge = $subtotal > 500 ? 0 : 50;
$total = $subtotal + $delivery_charge;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_address = clean($_POST['delivery_address']);
    $payment_method = clean($_POST['payment_method']);
    $payment_id = isset($_POST['payment_id']) ? clean($_POST['payment_id']) : null;
    $payment_signature = isset($_POST['payment_signature']) ? clean($_POST['payment_signature']) : null;
    $payment_status = isset($_POST['payment_status']) ? clean($_POST['payment_status']) : 'pending';
    
    if (empty($delivery_address)) {
        $error = 'Please provide delivery address';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, payment_id, payment_signature, payment_status, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $order_stmt->bind_param("idsssss", $customer_id, $total, $payment_method, $payment_id, $payment_signature, $payment_status, $delivery_address);
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items
            foreach ($items as $item) {
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, farmer_id, quantity, price, product_name) VALUES (?, ?, ?, ?, ?, ?)");
                $item_stmt->bind_param("iiiids", $order_id, $item['product_id'], $item['farmer_id'], $item['quantity'], $item['price'], $item['name']);
                $item_stmt->execute();
                
                // Update product stock
                $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                $update_stock->execute();
            }
            
            // Clear cart
            $clear_cart = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
            $clear_cart->bind_param("i", $customer_id);
            $clear_cart->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect based on payment method
            if ($payment_method === 'cod') {
                header('Location: order_success.php?order_id=' . $order_id);
            } else {
                // For online payment, redirect to QR payment page
                header('Location: qr_payment.php?order_id=' . $order_id);
            }
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Order placement failed. Please try again.';
        }
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-bag text-success"></i> Checkout
    </h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-md-7">
            <div class="dashboard-card">
                <h4 class="mb-4">Delivery Information</h4>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Delivery Address *</label>
                        <textarea class="form-control" name="delivery_address" rows="4" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <div class="payment-methods">
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" checked>
                                <label class="form-check-label w-100" for="online">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-qrcode fa-2x text-primary me-3"></i>
                                        <div>
                                            <strong>UPI / Online Payment</strong>
                                            <p class="small text-muted mb-0">Pay via QR code - PhonePe, Paytm, Google Pay, BHIM</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                                <label class="form-check-label w-100" for="cod">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <p class="small text-muted mb-0">Pay when you receive the order</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom btn-lg w-100">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-md-5">
            <div class="cart-summary">
                <h4 class="mb-3">Order Summary</h4>
                
                <div class="mb-3">
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                            <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong><?php echo formatPrice($subtotal); ?></strong>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery Charge:</span>
                    <strong><?php echo $delivery_charge > 0 ? formatPrice($delivery_charge) : 'FREE'; ?></strong>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-success" style="font-size: 1.5rem;"><?php echo formatPrice($total); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>