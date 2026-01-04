<?php
$page_title = "Select Delivery Location";
include 'includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getCurrentUser($conn);

// Handle location save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_location'])) {
    $address = clean($_POST['address']);
    $pincode = clean($_POST['pincode']);
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    
    $stmt = $conn->prepare("UPDATE users SET address = ?, pincode = ?, city = ?, state = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $address, $pincode, $city, $state, $user_id);
    
    if ($stmt->execute()) {
        header('Location: customer/checkout.php?location_updated=1');
        exit();
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-map-marker-alt text-success"></i> Select Delivery Location
    </h2>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Location Form -->
            <div class="dashboard-card">
                <h5 class="mb-3">Enter Delivery Address</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" name="address" rows="3" required placeholder="House/Flat No, Building Name, Street, Landmark"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" name="pincode" 
                                   value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" 
                                   required maxlength="6" pattern="[0-9]{6}" 
                                   placeholder="411001">
                            <small class="text-muted">6-digit pincode</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" name="city" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" 
                                   required placeholder="Pune">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State *</label>
                            <select class="form-control" name="state" required>
                                <option value="">Select State</option>
                                <?php
                                $states = ['Andhra Pradesh', 'Bihar', 'Chhattisgarh', 'Delhi', 'Goa', 'Gujarat', 
                                          'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 
                                          'Madhya Pradesh', 'Maharashtra', 'Odisha', 'Punjab', 'Rajasthan', 
                                          'Tamil Nadu', 'Telangana', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'];
                                foreach ($states as $state) {
                                    $selected = ($user['state'] ?? '') === $state ? 'selected' : '';
                                    echo "<option value='$state' $selected>$state</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Products will be shown from farmers within your area.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="save_location" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-check"></i> Save Location & Continue
                        </button>
                        <a href="customer/cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Sidebar -->
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="fas fa-truck"></i> Delivery Information</h5>
                
                <div class="mb-3">
                    <h6>Delivery Area</h6>
                    <p class="text-muted small">We deliver to most locations across India</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Time</h6>
                    <p class="text-muted small">2-3 business days for standard delivery</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Charges</h6>
                    <p class="text-muted small">
                        Free delivery on orders above ₹500<br>
                        ₹50 for orders below ₹500
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-map-marker-alt text-success"></i> Select Delivery Location
    </h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Search or Select Location on Map</h5>
                
                <!-- Search Box -->
                <div class="mb-3">
                    <input type="text" id="pac-input" class="form-control" placeholder="Search for your location...">
                </div>
                
                <!-- Map Container -->
                <div id="map" style="height: 400px; border-radius: 10px;"></div>
            </div>
            
            <!-- Location Form -->
            <div class="dashboard-card">
                <h5 class="mb-3">Confirm Delivery Address</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode *</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required maxlength="6">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State *</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo $user['latitude'] ?? '18.5204'; ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo $user['longitude'] ?? '73.8567'; ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Products will be shown from farmers within 50 km of your location.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="save_location" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-check"></i> Confirm Location & Continue
                        </button>
                        <a href="customer/cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Sidebar -->
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5 class="mb-3"><i class="fas fa-truck"></i> Delivery Information</h5>
                
                <div class="mb-3">
                    <h6>Delivery Radius</h6>
                    <p class="text-muted small">We deliver within 50 km of your selected location</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Time</h6>
                    <p class="text-muted small">2-3 business days for standard delivery</p>
                </div>
                
                <div class="mb-3">
                    <h6>Delivery Charges</h6>
                    <p class="text-muted small">
                        Free delivery on orders above ₹500<br>
                        ₹50 for orders below ₹500
                    </p>
                </div>
                
                <hr>
                
                <h6>How It Works</h6>
                <ol class="small text-muted ps-3">
                    <li>Search or click on the map</li>
                    <li>Confirm your delivery address</li>
                    <li>See products from nearby farmers</li>
                    <li>Place your order</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>