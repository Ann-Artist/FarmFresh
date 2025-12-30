<?php
$page_title = "Home";
include 'includes/header.php';

// Get featured products
$featured_sql = "SELECT p.*, u.name as farmer_name FROM products p 
                 JOIN users u ON p.farmer_id = u.id 
                 WHERE p.status = 'available' 
                 ORDER BY p.created_at DESC LIMIT 6";
$featured_result = $conn->query($featured_sql);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Fresh Organic Produce</h1>
        <p>Directly from Farmers to Your Doorstep</p>
        <a href="products.php" class="btn btn-hero">Shop Now</a>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose FarmFresh?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-leaf feature-icon"></i>
                    <h3>100% Organic</h3>
                    <p>All our products are certified organic, grown without harmful pesticides or chemicals.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-handshake feature-icon"></i>
                    <h3>Direct from Farmers</h3>
                    <p>Buy directly from farmers, ensuring fair prices and supporting local agriculture.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-shipping-fast feature-icon"></i>
                    <h3>Fast Delivery</h3>
                    <p>Get fresh produce delivered to your doorstep within 24-48 hours.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Featured Products</h2>
        <div class="row">
            <?php if ($featured_result->num_rows > 0): ?>
                <?php while ($product = $featured_result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            <div class="position-relative">
                                <?php 
                                $image_path = $product['image'] ? 
                                    "/farmfresh/assets/images/" . $product['image'] : 
                                    "https://via.placeholder.com/400x300?text=No+Image";
                                ?>
                                <img src="<?php echo $image_path; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php if ($product['is_organic']): ?>
                                    <span class="product-badge"><i class="fas fa-leaf"></i> Organic</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($product['farmer_name']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="product-price"><?php echo formatPrice($product['price']); ?>/<?php echo $product['unit']; ?></span>
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary-custom">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-primary-custom btn-lg">View All Products</a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-plus" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h4>1. Register</h4>
                    <p>Create your account as a farmer or customer</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <div class="mb-3">
                        <i class="fas fa-search" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h4>2. Browse</h4>
                    <p>Explore our wide range of organic products</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <div class="mb-3">
                        <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h4>3. Order</h4>
                    <p>Add items to cart and place your order</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <div class="mb-3">
                        <i class="fas fa-truck" style="font-size: 4rem; color: var(--primary-color);"></i>
                    </div>
                    <h4>4. Receive</h4>
                    <p>Get fresh products delivered to your door</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>