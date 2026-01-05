<?php
$page_title = "Products";
include 'includes/header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$category = isset($_GET['category']) ? clean($_GET['category']) : '';

// Build query
$sql = "SELECT p.*, u.name as farmer_name FROM products p 
        JOIN users u ON p.farmer_id = u.id 
        WHERE p.status = 'available' AND p.approval_status = 'approved'";

if ($search) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if ($category) {
    $sql .= " AND p.category = '$category'";
}

$sql .= " ORDER BY p.created_at DESC";

$result = $conn->query($sql);

// Get categories for filter
$categories_sql = "SELECT DISTINCT category FROM products WHERE status = 'available' ORDER BY category";
$categories_result = $conn->query($categories_sql);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-basket text-success"></i> Our Products
    </h2>
    
    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary-custom" onclick="searchProducts()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-control" id="categoryFilter" onchange="searchProducts()">
                <option value="">All Categories</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo ucfirst($cat['category']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <a href="products.php" class="btn btn-outline-secondary w-100">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <?php 
                            $image_path = $product['image'] ? 
                                "/farmfresh/assets/images/" . $product['image'] : 
                                "https://via.placeholder.com/400x300?text=" . urlencode($product['name']);
                            ?>
                            <img src="<?php echo $image_path; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                            <div class="product-price-wrapper mb-3">
    <div class="product-price">
        <?php echo formatPrice($product['price']); ?>/<?php echo $product['unit']; ?>
    </div>
    <div class="product-availability">
        <?php echo $product['quantity']; ?> <?php echo $product['unit']; ?> available
    </div>
</div>

                            <div class="d-grid gap-2">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary-custom btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isCustomer()): ?>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-basket" style="font-size: 5rem; color: #ddd;"></i>
                    <h4 class="mt-3 text-muted">No products found</h4>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="products.php" class="btn btn-primary-custom mt-3">View All Products</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>