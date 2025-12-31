<?php
$page_title = "My Orders";
include '../includes/header.php';

requireLogin();
if (!isFarmer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Get all orders for this farmer's products
$orders_sql = "SELECT 
                o.id as order_id,
                o.created_at,
                o.order_status,
                o.payment_status,
                o.delivery_address,
                u.name as customer_name,
                u.phone as customer_phone,
                u.email as customer_email,
                oi.product_id,
                oi.product_name,
                oi.quantity,
                oi.price,
                p.name as current_product_name
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN users u ON o.customer_id = u.id
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.farmer_id = ?
              ORDER BY o.created_at DESC";

$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$orders_result = $stmt->get_result();

// Group orders by order_id
$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $order_id,
            'created_at' => $row['created_at'],
            'order_status' => $row['order_status'],
            'payment_status' => $row['payment_status'],
            'delivery_address' => $row['delivery_address'],
            'customer_name' => $row['customer_name'],
            'customer_phone' => $row['customer_phone'],
            'customer_email' => $row['customer_email'],
            'items' => [],
            'total' => 0
        ];
    }
    
    // Use product_name from order_items (preserved even if product deleted) or current name
    $product_display_name = $row['product_name'] ? $row['product_name'] : 
                           ($row['current_product_name'] ? $row['current_product_name'] : 'Product Deleted');
    
    $orders[$order_id]['items'][] = [
        'product_name' => $product_display_name,
        'quantity' => $row['quantity'],
        'price' => $row['price']
    ];
    $orders[$order_id]['total'] += $row['price'] * $row['quantity'];
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-shopping-bag text-success"></i> My Orders
        </h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (count($orders) > 0): ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-12 mb-4">
                    <div class="dashboard-card">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">
                                    Order #<?php echo $order['order_id']; ?>
                                    <span class="badge bg-info ms-2"><?php echo ucfirst($order['order_status']); ?></span>
                                    <span class="badge <?php echo $order['payment_status'] === 'completed' ? 'bg-success' : 'bg-warning'; ?> ms-2">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </h5>
                                
                                <p class="mb-2">
                                    <strong><i class="fas fa-user"></i> Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-phone"></i> Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-map-marker-alt"></i> Delivery Address:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                                </p>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="col-md-4">
                                <h6 class="mb-3">Order Items:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total:</th>
                                                <th><?php echo formatPrice($order['total']); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No orders yet</h4>
            <p class="text-muted">Orders for your products will appear here</p>
            <a href="my_products.php" class="btn btn-primary-custom mt-3">
                <i class="fas fa-box"></i> View My Products
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>