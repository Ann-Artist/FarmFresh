<?php
$page_title = "Product Details";
include 'includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT p.*, u.name as farmer_name, u.phone as farmer_phone, u.email as farmer_email 
                        FROM products p 
                        JOIN users u ON p.farmer_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();

// Get reviews
$reviews_sql = "SELECT r.*, u.name as customer_name FROM reviews r 
                JOIN users u ON r.customer_id = u.id 
                WHERE r.product_id = ? ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Calculate average rating
$rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $product_id);
$rating_stmt->execute();
$rating_data = $rating_stmt->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];
?>

<div class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <?php 
                $image_path = $product['image'] ? 
                    "/farmfresh/assets/images/" . $product['image'] : 
                    "https://via.placeholder.com/600x450?text=" . urlencode($product['name']);
                ?>
                <img src="<?php echo $image_path; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php if ($product['is_organic']): ?>
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success" style="font-size: 1.2rem; padding: 10px 20px;">
                            <i class="fas fa-leaf"></i> Certified Organic
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-6">
            <div class="dashboard-card">
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Rating -->
                <?php if ($total_reviews > 0): ?>
                    <div class="mb-3">
                        <span class="product-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $avg_rating ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="text-muted">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <h2 class="text-success mb-3"><?php echo formatPrice($product['price']); ?> / <?php echo $product['unit']; ?></h2>
                
                <div class="mb-3">
                    <span class="badge bg-primary"><?php echo ucfirst($product['category']); ?></span>
                    <span class="badge <?php echo $product['status'] === 'available' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo ucfirst($product['status']); ?>
                    </span>
                </div>
                
                <p class="mb-3"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                
                <div class="mb-4">
                    <h5>Product Details:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> Available: <?php echo $product['quantity']; ?> <?php echo $product['unit']; ?></li>
                        <?php if ($product['certification']): ?>
                            <li><i class="fas fa-certificate text-success"></i> Certification: <?php echo htmlspecialchars($product['certification']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="mb-4">
                    <h5>Farmer Information:</h5>
                    <p>
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($product['farmer_name']); ?><br>
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($product['farmer_email']); ?><br>
                        <?php if ($product['farmer_phone']): ?>
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($product['farmer_phone']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if (isCustomer() && $product['status'] === 'available'): ?>
                    <div class="d-grid gap-2">
                        <button onclick="addToCart(<?php echo $product_id; ?>)" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="alert alert-info">
                        Please <a href="login.php">login</a> as a customer to purchase this product.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="dashboard-card">
                <h3 class="mb-4">Customer Reviews</h3>
                
                <?php if ($reviews_result->num_rows > 0): ?>
                    <?php while ($review = $reviews_result->fetch_assoc()): ?>
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($review['customer_name']); ?></h6>
                                    <div class="product-rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo timeAgo($review['created_at']); ?></small>
                            </div>
                            <p class="mb-0"><?php echo htmlspecialchars($review['comment']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>