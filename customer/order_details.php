<?php
$page_title = "Order Details";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    header('Location: my_orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.name as product_name, p.image, p.unit, u.name as farmer_name 
              FROM order_items oi 
              LEFT JOIN products p ON oi.product_id = p.id 
              LEFT JOIN users u ON oi.farmer_id = u.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-receipt text-success"></i> Order Details
        </h2>
        <a href="my_orders.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
    
    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h4 class="mb-3">
                    Order #<?php echo $order['id']; ?>
                    <span class="badge bg-info ms-2"><?php echo ucfirst($order['order_status']); ?></span>
                </h4>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="fas fa-calendar"></i> Order Date:</strong><br>
                            <?php echo date('F d, Y, h:i A', strtotime($order['created_at'])); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="fas fa-credit-card"></i> Payment Method:</strong><br>
                            <?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online Payment'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="fas fa-money-check-alt"></i> Payment Status:</strong><br>
                            <span class="badge <?php 
                                echo $order['payment_status'] === 'completed' ? 'bg-success' : 
                                     ($order['payment_status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                            ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="fas fa-truck"></i> Delivery Status:</strong><br>
                            <span class="badge 
                                <?php 
                                    switch($order['order_status']) {
                                        case 'pending': echo 'bg-warning'; break;
                                        case 'processing': echo 'bg-info'; break;
                                        case 'shipped': echo 'bg-primary'; break;
                                        case 'delivered': echo 'bg-success'; break;
                                        case 'cancelled': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong><i class="fas fa-map-marker-alt"></i> Delivery Address:</strong><br>
                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="dashboard-card">
                <h4 class="mb-3">Order Items</h4>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Farmer</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $image_path = $item['image'] ? 
                                                getProductImage($item['image'], $item['product_name'] ?? 'Product') : 
                                                'https://via.placeholder.com/50x50?text=No+Image';
                                            ?>
                                            <img src="<?php echo $image_path; ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                                                 class="me-2">
                                            <strong>
                                                <?php 
                                                // Use product_name from order_items if product is deleted
                                                echo htmlspecialchars($item['product_name'] ?? $item['product_name'] ?? 'Product Deleted'); 
                                                ?>
                                            </strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['farmer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="cart-summary">
                <h4 class="mb-3">Order Summary</h4>
                
                <?php
                // Calculate delivery charge (assuming same logic as checkout)
                $subtotal = $order['total_amount'];
                $delivery_charge = 0;
                
                // If order has delivery info, we can calculate
                // For now, assume orders > 500 had free delivery
                if ($order['total_amount'] <= 500) {
                    $delivery_charge = 50;
                    $subtotal = $order['total_amount'] - $delivery_charge;
                }
                ?>
                
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
                    <strong style="font-size: 1.2rem;">Total Amount:</strong>
                    <strong class="text-success" style="font-size: 1.5rem;">
                        <?php echo formatPrice($order['total_amount']); ?>
                    </strong>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6>Order Timeline</h6>
                    <div class="timeline">
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i> Order Placed
                        </div>
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> Processing
                        </div>
                        <div class="timeline-item <?php echo in_array($order['order_status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-truck"></i> Shipped
                        </div>
                        <div class="timeline-item <?php echo $order['order_status'] === 'delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Delivered
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <?php if ($order['order_status'] === 'delivered'): ?>
                        <a href="#" class="btn btn-outline-primary" onclick="alert('Review feature coming soon!')">
                            <i class="fas fa-star"></i> Write a Review
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print"></i> Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    margin-top: 15px;
}

.timeline-item {
    padding: 10px 0;
    color: #999;
    border-left: 2px solid #e0e0e0;
    padding-left: 20px;
    position: relative;
}

.timeline-item.active {
    color: #2ecc71;
    border-left-color: #2ecc71;
}

.timeline-item i {
    position: absolute;
    left: -9px;
    background: white;
    border-radius: 50%;
}

.timeline-item.active i {
    color: #2ecc71;
}

@media print {
    .btn, .navbar, .footer, a[href*="my_orders"] {
        display: none !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>