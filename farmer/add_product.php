<?php
$page_title = "Add Product";
include '../includes/header.php';

requireLogin();
if (!isFarmer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $category = clean($_POST['category']);
    $description = clean($_POST['description']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $unit = clean($_POST['unit']);
    $certification = clean($_POST['certification']);
    $is_organic = isset($_POST['is_organic']) ? 1 : 0;
    $farmer_id = $_SESSION['user_id'];
    
    // Validation
    if (empty($name) || empty($category) || $price <= 0 || $quantity <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle image upload
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            if ($upload_result['success']) {
                $image_name = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO products (farmer_id, name, category, description, price, quantity, unit, image, is_organic, certification, status, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', 'pending')");
            $stmt->bind_param("isssdissss", $farmer_id, $name, $category, $description, $price, $quantity, $unit, $image_name, $is_organic, $certification);
            
            if ($stmt->execute()) {
                $success = 'Product added successfully! It will be visible once approved by admin.';
                // Clear form
                $_POST = array();
            } else {
                $error = 'Failed to add product';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="dashboard-card">
                <h2 class="mb-4">
                    <i class="fas fa-plus-circle text-success"></i> Add New Product
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom">
                        <?php echo $success; ?>
                        <a href="my_products.php">View all products</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="vegetables">Vegetables</option>
                                <option value="fruits">Fruits</option>
                                <option value="grains">Grains</option>
                                <option value="dairy">Dairy</option>
                                <option value="herbs">Herbs</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit *</label>
                            <select class="form-control" name="unit" required>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="l">Liter (l)</option>
                                <option value="piece">Piece</option>
                                <option value="dozen">Dozen</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this)">
                            <img id="imagePreview" src="" style="max-width: 200px; margin-top: 10px; display: none;">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Certification Number</label>
                            <input type="text" class="form-control" name="certification" placeholder="e.g., CERT123456">
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_organic" name="is_organic" checked>
                        <label class="form-check-label" for="is_organic">
                            <i class="fas fa-leaf text-success"></i> This is an organic product
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>