<?php
$page_title = "Approve Products";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle product approval/rejection
if (isset($_POST['approve_product'])) {
    $product_id = (int)$_POST['product_id'];
    $action = clean($_POST['action']);
    $rejection_reason = isset($_POST['rejection_reason']) ? clean($_POST['rejection_reason']) : null;
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE products SET approval_status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $success = 'Product approved successfully!';
        } else {
            $error = 'Failed to approve product';
        }
    } elseif ($action === 'reject') {
        if (empty($rejection_reason)) {
            $error = 'Please provide a reason for rejection';
        } else {
            $stmt = $conn->prepare("UPDATE products SET approval_status = 'rejected', rejection_reason = ? WHERE id = ?");
            $stmt->bind_param("si", $rejection_reason, $product_id);
            
            if ($stmt->execute()) {
                $success = 'Product rejected';
            } else {
                $error = 'Failed to reject product';
            }
        }
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : 'pending';

// Get products based on filter
$products_sql = "SELECT p.*, u.name as farmer_name FROM products p 
                 JOIN users u ON p.farmer_id = u.id 
                 WHERE p.approval_status = ? 
                 ORDER BY p.created_at DESC";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("s", $status_filter);
$stmt->execute();
$products_result = $stmt->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-check-square text-success"></i> Approve Products
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="?status=pending" class="btn btn-<?php echo $status_filter === 'pending' ? 'warning' : 'outline-warning'; ?>">
                <i class="fas fa-clock"></i> Pending
            </a>
            <a href="?status=approved" class="btn btn-<?php echo $status_filter === 'approved' ? 'success' : 'outline-success'; ?>">
                <i class="fas fa-check"></i> Approved
            </a>
            <a href="?status=rejected" class="btn btn-<?php echo $status_filter === 'rejected' ? 'danger' : 'outline-danger'; ?>">
                <i class="fas fa-times"></i> Rejected
            </a>
        </div>
    </div>
    
    <?php if ($products_result->num_rows > 0): ?>
        <div class="row">
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <?php $image_path = getProductImage($product['image'], $product['name']); ?>
                            <img src="<?php echo $image_path; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            
                            <span class="position-absolute top-0 end-0 m-2">
                                <span class="badge 
                                    <?php 
                                        echo $product['approval_status'] === 'approved' ? 'bg-success' : 
                                             ($product['approval_status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                                    ?>">
                                    <?php echo ucfirst($product['approval_status']); ?>
                                </span>
                            </span>
                            
                            <?php if ($product['is_organic']): ?>
                                <span class="product-badge"><i class="fas fa-leaf"></i> Organic</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($product['farmer_name']); ?>
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag"></i> <?php echo ucfirst($product['category']); ?>
                            </p>
                            <div class="mb-2">
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                <span class="text-muted">/ <?php echo $product['unit']; ?></span>
                            </div>
                            <p class="small mb-2">
                                <strong>Stock:</strong> <?php echo $product['quantity']; ?> <?php echo $product['unit']; ?>
                            </p>
                            <p class="small mb-3">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                            </p>
                            
                            <?php if ($product['approval_status'] === 'rejected' && $product['rejection_reason']): ?>
                                <div class="alert alert-danger small">
                                    <strong>Rejection Reason:</strong><br>
                                    <?php echo htmlspecialchars($product['rejection_reason']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($product['approval_status'] === 'pending'): ?>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal<?php echo $product['id']; ?>">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal<?php echo $product['id']; ?>">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                                
                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal<?php echo $product['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Approve Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <p>Approve <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
                                                    <p class="text-success"><i class="fas fa-info-circle"></i> This product will be visible to customers.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="approve_product" class="btn btn-success">Approve Product</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?php echo $product['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <p>Reject <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Rejection *</label>
                                                        <textarea class="form-control" name="rejection_reason" rows="3" required 
                                                                  placeholder="Provide a reason for rejection..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="approve_product" class="btn btn-danger">Reject Product</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-box" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No <?php echo $status_filter; ?> products</h4>
            <p class="text-muted">
                <?php 
                if ($status_filter === 'pending') {
                    echo 'No products pending approval';
                } elseif ($status_filter === 'approved') {
                    echo 'No approved products yet';
                } else {
                    echo 'No rejected products';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>