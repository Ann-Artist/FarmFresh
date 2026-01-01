<?php
$page_title = "Order Success";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$customer_id = $_SESSION['user_id'];

// Verify this order belongs to the customer
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: dashboard.php');
    exit();
}

$order = $result->fetch_assoc();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle" style="font-size: 5rem; color: #2ecc71;"></i>
                </div>
                <h1 class="mb-3">Order Placed Successfully!</h1>
                <p class="lead text-muted">Thank you for your order. We'll start processing it right away.</p>
            </div>
            
            <div class="dashboard-card mb-4">
                <h4 class="mb-4 text-center">Order Summary</h4>
                
                <div class="row text-center mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="p-3">
                            <i class="fas fa-receipt fa-2x text-success mb-2"></i>
                            <h6>Order Number</h6>
                            <strong class="text-success" style="font-size: 1.5rem;">#<?php echo $order['id']; ?></strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3">
                            <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
                            <h6>Total Amount</h6>
                            <strong class="text-success" style="font-size: 1.5rem;"><?php echo formatPrice($order['total_amount']); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3">
                            <i class="fas fa-credit-card fa-2x text-success mb-2"></i>
                            <h6>Payment Method</h6>
                            <strong><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online Payment'; ?></strong>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-4">
                    <h6><i class="fas fa-map-marker-alt"></i> Delivery Address:</h6>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>What's Next?</strong>
                    <ul class="mb-0 mt-2">
                        <li>You'll receive an order confirmation email shortly</li>
                        <li>Our farmers will prepare your fresh organic products</li>
                        <li>We'll notify you when your order is shipped</li>
                        <li>Expected delivery: 2-3 business days</li>
                    </ul>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary-custom btn-lg">
                    <i class="fas fa-eye"></i> View Order Details
                </a>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
                <a href="../products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-basket"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Show a celebration animation
window.onload = function() {
    // You can add confetti or celebration animation here if desired
    console.log('Order placed successfully!');
};
</script>

<?php include '../includes/footer.php'; ?>