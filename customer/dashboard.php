<?php
$page_title = "Customer Dashboard";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Get statistics
$stats = [];

// Total orders
$orders_sql = "SELECT COUNT(*) as count FROM orders WHERE customer_id = ?";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stats['total_orders'] = $stmt->get_result()->fetch_assoc()['count'];

// Total spent
$spent_sql = "SELECT SUM(total_amount) as total FROM orders WHERE customer_id = ?";
$stmt = $conn->prepare($spent_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stats['total_spent'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Cart items
$stats['cart_items'] = getCartCount($conn, $customer_id);

// Recent orders
$recent_orders_sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_orders_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-tachometer-alt text-success"></i> Customer Dashboard
    </h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-rupee-sign fa-3x mb-3"></i>
                <div class="stat-number"><?php echo formatPrice($stats['total_spent']); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['cart_items']; ?></div>
                <div class="stat-label">Cart Items</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h4 class="mb-3">Quick Actions</h4>
                <div class="d-flex flex-wrap gap-2">
                    <a href="../products.php" class="btn btn-primary-custom">
                        <i class="fas fa-shopping-basket"></i> Browse Products
                    </a>
                    <a href="cart.php" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart"></i> View Cart (<?php echo $stats['cart_items']; ?>)
                    </a>
                    <a href="my_orders.php" class="btn btn-outline-success">
                        <i class="fas fa-list"></i> My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <h4 class="mb-3">Recent Orders</h4>
                <?php if ($recent_orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $order['payment_status'] === 'completed' ? 'bg-success' : 
                                                     ($order['payment_status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                                            ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="my_orders.php" class="btn btn-outline-primary">View All Orders</a>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag" style="font-size: 4rem; color: #ddd;"></i>
                        <h5 class="mt-3 text-muted">No orders yet</h5>
                        <p class="text-muted">Start shopping for fresh organic products!</p>
                        <a href="../products.php" class="btn btn-primary-custom mt-2">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>