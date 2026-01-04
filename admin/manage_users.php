<?php
$page_title = "Manage Users";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle user status change
if (isset($_POST['change_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = clean($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $success = 'User status updated successfully!';
    } else {
        $error = 'Failed to update user status';
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting admin users
    $check = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $user_type = $check->get_result()->fetch_assoc()['user_type'];
    
    if ($user_type === 'admin') {
        $error = 'Cannot delete admin users';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = 'User deleted successfully!';
        } else {
            $error = 'Failed to delete user';
        }
    }
}

// Get filter
$filter = isset($_GET['type']) ? clean($_GET['type']) : 'all';

// Get all users
$sql = "SELECT * FROM users WHERE 1=1";
if ($filter !== 'all') {
    $sql .= " AND user_type = '$filter'";
}
$sql .= " ORDER BY created_at DESC";
$users_result = $conn->query($sql);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-users text-success"></i> Manage Users
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filter -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="?type=all" class="btn btn-<?php echo $filter === 'all' ? 'primary' : 'outline-primary'; ?>">
                All Users
            </a>
            <a href="?type=farmer" class="btn btn-<?php echo $filter === 'farmer' ? 'success' : 'outline-success'; ?>">
                Farmers
            </a>
            <a href="?type=customer" class="btn btn-<?php echo $filter === 'customer' ? 'info' : 'outline-info'; ?>">
                Customers
            </a>
            <a href="?type=admin" class="btn btn-<?php echo $filter === 'admin' ? 'danger' : 'outline-danger'; ?>">
                Admins
            </a>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        echo $user['user_type'] === 'admin' ? 'bg-danger' : 
                                             ($user['user_type'] === 'farmer' ? 'bg-success' : 'bg-primary'); 
                                    ?>">
                                    <?php echo ucfirst($user['user_type']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <!-- Change Status -->
                                    <button type="button" class="btn btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Delete (not for admins) -->
                                    <?php if ($user['user_type'] !== 'admin'): ?>
                                        <button type="button" class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Status Modal -->
                                <div class="modal fade" id="statusModal<?php echo $user['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Change User Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select class="form-control" name="status">
                                                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="change_status" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete Modal -->
                                <?php if ($user['user_type'] !== 'admin'): ?>
                                    <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($user['name']); ?></strong>?</p>
                                                        <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>