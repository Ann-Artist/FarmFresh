<?php
$page_title = "Manage Orders";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle order status update
if (isset($_POST['update_order'])) {
    $order_id = (int)$_POST['order_id'];
    $order_status = clean($_POST['order_status']);
    $payment_status = clean($_POST['payment_status']);
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $order_status, $payment_status, $order_id);
    
    if ($stmt->execute()) {
        $success = 'Order updated successfully!';
    } else {
        $error = 'Failed to update order';
    }
}

// Get filter
$order_status = isset($_GET['status']) ? clean($_GET['status']) : 'all';
$payment_status = isset($_GET['payment']) ? clean($_GET['payment']) : 'all';

// Get all orders
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id WHERE 1=1";

if ($order_status !== 'all') {
    $sql .= " AND o.order_status = '$order_status'";
}

if ($payment_status !== 'all') {
    $sql .= " AND o.payment_status = '$payment_status'";
}

$sql .= " ORDER BY o.created_at DESC";
$orders_result = $conn->query($sql);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-bag text-success"></i> Manage Orders
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label">Order Status</label>
            <select class="form-control" onchange="window.location.href='?status='+this.value+'&payment=<?php echo $payment_status; ?>'">
                <option value="all" <?php echo $order_status === 'all' ? 'selected' : ''; ?>>All Orders</option>
                <option value="pending" <?php echo $order_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $order_status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $order_status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $order_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $order_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Payment Status</label>
            <select class="form-control" onchange="window.location.href='?status=<?php echo $order_status; ?>&payment='+this.value">
                <option value="all" <?php echo $payment_status === 'all' ? 'selected' : ''; ?>>All Payments</option>
                <option value="pending" <?php echo $payment_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo $payment_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="failed" <?php echo $payment_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo $order['payment_method'] === 'cod' ? 'COD' : ucfirst($order['payment_method']); ?>
                                </span>
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
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?php echo $order['id']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?php echo $order['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Order #<?php echo $order['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                                        <strong>Amount:</strong> <?php echo formatPrice($order['total_amount']); ?>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Order Status</label>
                                                        <select class="form-control" name="order_status">
                                                            <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                            <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                            <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                            <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Payment Status</label>
                                                        <select class="form-control" name="payment_status">
                                                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="completed" <?php echo $order['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>