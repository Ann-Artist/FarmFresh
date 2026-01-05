<?php
$page_title = "Register";
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    $user_type = clean($_POST['user_type']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($user_type)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Set approval status based on user type
            $approval_status = ($user_type === 'farmer') ? 'pending' : 'approved';
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, user_type, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $hashed_password, $phone, $address, $user_type, $approval_status);
            
            if ($stmt->execute()) {
                if ($user_type === 'farmer') {
                    header('Location: login.php?registered=farmer_pending');
                } else {
                    header('Location: login.php?registered=1');
                }
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-container">
                <h2 class="text-center mb-4">
                    <i class="fas fa-user-plus text-success"></i> Register on FarmFresh
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                            <small class="text-muted">At least 6 characters</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Register As *</label>
                            <select class="form-control" name="user_type" required id="userType">
                                <option value="">Select Type</option>
                                <option value="farmer">Farmer</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                    
                    <!-- Farmer approval notice -->
                    <div class="alert alert-info" id="farmerNotice" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note for Farmers:</strong> Your account will be pending approval by admin. You'll be able to login once approved.
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('userType').addEventListener('change', function() {
    const farmerNotice = document.getElementById('farmerNotice');
    if (this.value === 'farmer') {
        farmerNotice.style.display = 'block';
    } else {
        farmerNotice.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>