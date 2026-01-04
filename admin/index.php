<?php
$page_title = "Admin Dashboard";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$users_sql = "SELECT COUNT(*) as count FROM users WHERE user_type != 'admin'";
$stats['total_users'] = $conn->query($users_sql)->fetch_assoc()['count'];

// Total farmers
$farmers_sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 'farmer'";
$stats['total_farmers'] = $conn->query($farmers_sql)->fetch_assoc()['count'];

// Total customers
$customers_sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'";
$stats['total_customers'] = $conn->query($customers_sql)->fetch_assoc()['count'];

// Total products
$products_sql = "SELECT COUNT(*) as count FROM products";
$stats['total_products'] = $conn->query($products_sql)->fetch_assoc()['count'];

// Total orders
$orders_sql = "SELECT COUNT(*) as count FROM orders";
$stats['total_orders'] = $conn->query($orders_sql)->fetch_assoc()['count'];

// Total revenue
$revenue_sql = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'";
$stats['total_revenue'] = $conn->query($revenue_sql)->fetch_assoc()['total'] ?? 0;

// Pending payments
$pending_payments_sql = "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending' AND payment_method != 'cod'";
$stats['pending_payments'] = $conn->query($pending_payments_sql)->fetch_assoc()['count'];

// Recent users
$recent_users_sql = "SELECT * FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($recent_users_sql);

// Recent orders
$recent_orders_sql = "SELECT o.*, u.name as customer_name FROM orders o 
                      JOIN users u ON o.customer_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-user-shield text-success"></i> Admin Dashboard
    </h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="fas fa-users fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
                <small class="d-block mt-2">
                    <i class="fas fa-tractor"></i> <?php echo $stats['total_farmers']; ?> Farmers | 
                    <i class="fas fa-user"></i> <?php echo $stats['total_customers']; ?> Customers
                </small>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-box fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                <i class="fas fa-rupee-sign fa-3x mb-3"></i>
                <div class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h4 class="mb-3">Quick Actions</h4>
                <div class="d-flex flex-wrap gap-2">
                    <a href="manage_users.php" class="btn btn-primary-custom">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="manage_products.php" class="btn btn-outline-primary">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="manage_orders.php" class="btn btn-outline-success">
                        <i class="fas fa-shopping-bag"></i> Manage Orders
                    </a>
                    <?php if ($stats['pending_payments'] > 0): ?>
                        <a href="verify_payments.php" class="btn btn-warning">
                            <i class="fas fa-check-circle"></i> Verify Payments (<?php echo $stats['pending_payments']; ?>)
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Data -->
    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4 class="mb-3">Recent Users</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['user_type'] === 'farmer' ? 'bg-success' : 'bg-primary'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4 class="mb-3">Recent Orders</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>