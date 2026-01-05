<?php
$page_title = "My Products";
include '../includes/header.php';

requireLogin();
if (!isFarmer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Get all products
$products_sql = "SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$products = $stmt->get_result();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-box text-success"></i> My Products
        </h2>
        <a href="add_product.php" class="btn btn-primary-custom">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
    
    <?php if ($products->num_rows > 0): ?>
        <div class="row">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <?php $image_path = getProductImage($product['image'], $product['name']); ?>
                            <img src="<?php echo $image_path; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['is_organic']): ?>
                                <span class="product-badge"><i class="fas fa-leaf"></i> Organic</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-tag"></i> <?php echo ucfirst($product['category']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="product-price"><?php echo formatPrice($product['price']); ?>/<?php echo $product['unit']; ?></span>
                                <span class="badge <?php echo $product['status'] === 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>

<!-- ADD THIS: -->
<span class="badge 
    <?php 
        echo $product['approval_status'] === 'approved' ? 'bg-success' : 
             ($product['approval_status'] === 'pending' ? 'bg-warning text-dark' : 'bg-danger'); 
    ?>">
    <?php echo ucfirst($product['approval_status']); ?>
</span>

<?php if ($product['approval_status'] === 'pending'): ?>
    <div class="alert alert-warning small mt-2">
        <i class="fas fa-clock"></i> Pending admin approval
    </div>
<?php elseif ($product['approval_status'] === 'rejected'): ?>
    <div class="alert alert-danger small mt-2">
        <i class="fas fa-times-circle"></i> Rejected
        <?php if ($product['rejection_reason']): ?>
            <br><small><?php echo htmlspecialchars($product['rejection_reason']); ?></small>
        <?php endif; ?>
    </div>
<?php endif; ?>

                            </div>
                            <p class="small mb-3">
                                <strong>Stock:</strong> <?php echo $product['quantity']; ?> <?php echo $product['unit']; ?>
                            </p>
                            <div class="d-grid gap-2">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-box" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No products yet</h4>
            <p class="text-muted">Start adding your products to sell on FarmFresh</p>
            <a href="add_product.php" class="btn btn-primary-custom mt-3">
                <i class="fas fa-plus"></i> Add First Product
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>