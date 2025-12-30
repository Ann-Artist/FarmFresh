<?php
$page_title = "Farmer Dashboard";
include '../includes/header.php';

requireLogin();
if (!isFarmer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Get statistics
$stats = [];

// Total products
$products_sql = "SELECT COUNT(*) as count FROM products WHERE farmer_id = ?";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$stats['total_products'] = $stmt->get_result()->fetch_assoc()['count'];

// Total orders
$orders_sql = "SELECT COUNT(DISTINCT oi.order_id) as count FROM order_items oi WHERE oi.farmer_id = ?";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$stats['total_orders'] = $stmt->get_result()->fetch_assoc()['count'];

// Total revenue
$revenue_sql = "SELECT SUM(oi.price * oi.quantity) as total FROM order_items oi WHERE oi.farmer_id = ?";
$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$stats['total_revenue'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Recent products
$recent_products_sql = "SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_products_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$recent_products = $stmt->get_result();

// Recent orders
$recent_orders_sql = "SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name as product_name 
                      FROM orders o 
                      JOIN order_items oi ON o.id = oi.order_id 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.farmer_id = ? 
                      ORDER BY o.created_at DESC LIMIT 10";
$stmt = $conn->prepare($recent_orders_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-tachometer-alt text-success"></i> Farmer Dashboard
    </h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="fas fa-box fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
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
                    <a href="add_product.php" class="btn btn-primary-custom">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                    <a href="my_products.php" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Manage Products
                    </a>
                    <a href="orders.php" class="btn btn-outline-success">
                        <i class="fas fa-shopping-bag"></i> View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Products -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4 class="mb-3">Recent Products</h4>
                <?php if ($recent_products->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = $recent_products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td><?php echo $product['quantity']; ?> <?php echo $product['unit']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $product['status'] === 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $product['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="my_products.php" class="btn btn-sm btn-outline-primary">View All Products</a>
                <?php else: ?>
                    <p class="text-muted">No products yet. <a href="add_product.php">Add your first product</a></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4 class="mb-3">Recent Orders</h4>
                <?php if ($recent_orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($order['product_name'], 0, 20)); ?></td>
                                        <td><?php echo $order['quantity']; ?></td>
                                        <td><?php echo formatPrice($order['price'] * $order['quantity']); ?></td>
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
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
                <?php else: ?>
                    <p class="text-muted">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>