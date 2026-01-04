<?php
$page_title = "Manage Products";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $success = 'Product deleted successfully!';
    } else {
        $error = 'Failed to delete product';
    }
}

// Get filter
$category = isset($_GET['category']) ? clean($_GET['category']) : 'all';
$status = isset($_GET['status']) ? clean($_GET['status']) : 'all';

// Get all products
$sql = "SELECT p.*, u.name as farmer_name FROM products p 
        JOIN users u ON p.farmer_id = u.id WHERE 1=1";

if ($category !== 'all') {
    $sql .= " AND p.category = '$category'";
}

if ($status !== 'all') {
    $sql .= " AND p.status = '$status'";
}

$sql .= " ORDER BY p.created_at DESC";
$products_result = $conn->query($sql);

// Get categories
$categories = ['vegetables', 'fruits', 'grains', 'dairy', 'herbs', 'others'];
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-box text-success"></i> Manage Products
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
            <label class="form-label">Category</label>
            <select class="form-control" onchange="window.location.href='?category='+this.value+'&status=<?php echo $status; ?>'">
                <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                        <?php echo ucfirst($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-control" onchange="window.location.href='?category=<?php echo $category; ?>&status='+this.value">
                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="out_of_stock" <?php echo $status === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Farmer</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $product['id']; ?></td>
                            <td>
                                <?php $img = getProductImage($product['image'], $product['name']); ?>
                                <img src="<?php echo $img; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <?php if ($product['is_organic']): ?>
                                    <span class="badge bg-success"><i class="fas fa-leaf"></i> Organic</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['farmer_name']); ?></td>
                            <td><?php echo ucfirst($product['category']); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['quantity']; ?> <?php echo $product['unit']; ?></td>
                            <td>
                                <span class="badge <?php echo $product['status'] === 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $product['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../product_detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal<?php echo $product['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
                                                    <p class="text-info"><i class="fas fa-info-circle"></i> Past orders will be preserved.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="delete_product" class="btn btn-danger">Delete Product</button>
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