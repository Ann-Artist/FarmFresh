<?php
$page_title = "Edit Product";
include '../includes/header.php';

requireLogin();
if (!isFarmer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$farmer_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND farmer_id = ?");
$stmt->bind_param("ii", $product_id, $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: my_products.php');
    exit();
}

$product = $result->fetch_assoc();
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
    $status = clean($_POST['status']);
    
    // Validation
    if (empty($name) || empty($category) || $price <= 0 || $quantity < 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle image upload
        $image_name = $product['image']; // Keep existing image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadImage($_FILES['image']);
            if ($upload_result['success']) {
                // Delete old image if exists
                if ($product['image']) {
                    $old_image = '../assets/images/' . $product['image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                $image_name = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (!$error) {
            $update_stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, description = ?, price = ?, quantity = ?, unit = ?, image = ?, is_organic = ?, certification = ?, status = ? WHERE id = ? AND farmer_id = ?");
            $update_stmt->bind_param("sssdisssssii", $name, $category, $description, $price, $quantity, $unit, $image_name, $is_organic, $certification, $status, $product_id, $farmer_id);
            
            if ($update_stmt->execute()) {
                $success = 'Product updated successfully!';
                // Refresh product data
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update product';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-edit text-success"></i> Edit Product
                    </h2>
                    <a href="my_products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="vegetables" <?php echo $product['category'] == 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                                <option value="fruits" <?php echo $product['category'] == 'fruits' ? 'selected' : ''; ?>>Fruits</option>
                                <option value="grains" <?php echo $product['category'] == 'grains' ? 'selected' : ''; ?>>Grains</option>
                                <option value="dairy" <?php echo $product['category'] == 'dairy' ? 'selected' : ''; ?>>Dairy</option>
                                <option value="herbs" <?php echo $product['category'] == 'herbs' ? 'selected' : ''; ?>>Herbs</option>
                                <option value="others" <?php echo $product['category'] == 'others' ? 'selected' : ''; ?>>Others</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantity *</label>
                            <input type="number" class="form-control" name="quantity" min="0" value="<?php echo $product['quantity']; ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit *</label>
                            <select class="form-control" name="unit" required>
                                <option value="kg" <?php echo $product['unit'] == 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                <option value="g" <?php echo $product['unit'] == 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                <option value="l" <?php echo $product['unit'] == 'l' ? 'selected' : ''; ?>>Liter (l)</option>
                                <option value="piece" <?php echo $product['unit'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                <option value="dozen" <?php echo $product['unit'] == 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Image</label>
                            <?php if ($product['image']): ?>
                                <div class="mb-2">
                                    <img src="/farmfresh/assets/images/<?php echo $product['image']; ?>" style="max-width: 200px; border-radius: 10px;">
                                    <p class="small text-muted mt-1">Current image</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted">Leave empty to keep current image</small>
                            <img id="imagePreview" src="" style="max-width: 200px; margin-top: 10px; display: none; border-radius: 10px;">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Certification Number</label>
                            <input type="text" class="form-control" name="certification" value="<?php echo htmlspecialchars($product['certification']); ?>" placeholder="e.g., CERT123456">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="available" <?php echo $product['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="out_of_stock" <?php echo $product['status'] == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" class="form-check-input" id="is_organic" name="is_organic" <?php echo $product['is_organic'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_organic">
                                    <i class="fas fa-leaf text-success"></i> This is an organic product
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="my_products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>