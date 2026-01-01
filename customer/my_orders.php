<?php
$page_title = "My Orders";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Get all orders for this customer
$orders_sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders_result = $stmt->get_result();
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
    
    <?php if ($orders_result->num_rows > 0): ?>
        <div class="dashboard-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                <td>
                                    <?php if ($order['payment_method'] === 'cod'): ?>
                                        <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                                    <?php else: ?>
                                        <i class="fas fa-credit-card"></i> Online Payment
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo $order['payment_status'] === 'completed' ? 'bg-success' : 
                                             ($order['payment_status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                                    ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary-custom">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No orders yet</h4>
            <p class="text-muted">Start shopping for fresh organic products!</p>
            <a href="../products.php" class="btn btn-primary-custom mt-3">
                <i class="fas fa-shopping-basket"></i> Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>