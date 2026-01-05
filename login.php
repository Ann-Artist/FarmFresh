<?php
$page_title = "Login";
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                
                // Check approval status for farmers
                if ($user['user_type'] === 'farmer' && $user['approval_status'] !== 'approved') {
                    if ($user['approval_status'] === 'pending') {
                        $error = 'Your farmer account is pending admin approval. Please wait for approval.';
                    } elseif ($user['approval_status'] === 'rejected') {
                        $error = 'Your farmer account has been rejected. Reason: ' . ($user['rejection_reason'] ?? 'Not specified');
                    }
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    // Redirect based on user type
                    switch ($user['user_type']) {
                        case 'admin':
                            header('Location: admin/index.php');
                            break;
                        case 'farmer':
                            header('Location: farmer/dashboard.php');
                            break;
                        case 'customer':
                            header('Location: customer/dashboard.php');
                            break;
                    }
                    exit();
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found or account inactive';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-container">
                <h2 class="text-center mb-4">
                    <i class="fas fa-sign-in-alt text-success"></i> Login to FarmFresh
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                    <div class="alert alert-success alert-custom">
                        Registration successful! Please login.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] == 'farmer_pending'): ?>
                    <div class="alert alert-info alert-custom">
                        <strong>Registration Successful!</strong><br>
                        Your farmer account is pending admin approval. You'll receive notification once approved.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>