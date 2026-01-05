<?php
$page_title = "My Profile";
include 'includes/header.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user = getCurrentUser($conn);
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    $pincode = clean($_POST['pincode']);
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    
    // Validate
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required';
    } else {
        // Check if email is already used by another user
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email already in use by another account';
        } else {

            // Handle profile image upload
            $profile_image = $user['profile_image'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $upload_result = uploadProfileImage($_FILES['profile_image']);

    if ($upload_result['success']) {
        // Delete old image
        if ($user['profile_image']) {
            $old_image = 'assets/images/' . $user['profile_image'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }
        $profile_image = $upload_result['filename'];
    }
}
            
            // Update profile
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, pincode = ?, city = ?, state = ?, profile_image = ? WHERE id = ?");
            $update_stmt->bind_param("ssssssssi", $name, $email, $phone, $address, $pincode, $city, $state, $profile_image, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Profile updated successfully!';
                $_SESSION['user_name'] = $name;
                // Refresh user data
                $user = getCurrentUser($conn);
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pwd_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($pwd_stmt->execute()) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'Password must be at least 6 characters';
            }
        } else {
            $error = 'New passwords do not match';
        }
    } else {
        $error = 'Current password is incorrect';
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-user-circle text-success"></i> My Profile
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="dashboard-card">
                <h4 class="mb-4">Profile Information</h4>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 mb-3 text-center">
                            <div class="profile-image-container mb-3">
                                <?php 
                                $profile_img_path = 'assets/images/' . ($user['profile_image'] ?? '');
                                if ($user['profile_image'] && file_exists($profile_img_path)): 
                                ?>
                                    <img src="<?php echo $profile_img_path; ?>" id="profilePreview" class="profile-image-large">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&size=150&background=2ecc71&color=fff" id="profilePreview" class="profile-image-large">
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control" name="profile_image" accept="image/*" onchange="previewProfileImage(this)">
                            <small class="text-muted">Upload profile picture (JPG, PNG)</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">User Type</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['user_type']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" maxlength="6">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Member since: <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Change Password -->
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Change Password</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                        <small class="text-muted">At least 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-outline-primary w-100">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
            
            <!-- Account Info -->
            <div class="dashboard-card">
                <h5 class="mb-3">Account Information</h5>
                <p class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </p>
                <p class="mb-2">
                    <strong>Account Type:</strong> <?php echo ucfirst($user['user_type']); ?>
                </p>
                <p class="mb-0">
                    <strong>User ID:</strong> #<?php echo $user['id']; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.profile-image-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary-color);
}
</style>

<script>
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>