<?php
$page_title = "Approve Farmers";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle farmer approval/rejection
if (isset($_POST['approve_farmer'])) {
    $farmer_id = (int)$_POST['farmer_id'];
    $action = clean($_POST['action']);
    $rejection_reason = isset($_POST['rejection_reason']) ? clean($_POST['rejection_reason']) : null;
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET approval_status = 'approved' WHERE id = ? AND user_type = 'farmer'");
        $stmt->bind_param("i", $farmer_id);
        
        if ($stmt->execute()) {
            $success = 'Farmer approved successfully!';
        } else {
            $error = 'Failed to approve farmer';
        }
    } elseif ($action === 'reject') {
        if (empty($rejection_reason)) {
            $error = 'Please provide a reason for rejection';
        } else {
            $stmt = $conn->prepare("UPDATE users SET approval_status = 'rejected', rejection_reason = ? WHERE id = ? AND user_type = 'farmer'");
            $stmt->bind_param("si", $rejection_reason, $farmer_id);
            
            if ($stmt->execute()) {
                $success = 'Farmer application rejected';
            } else {
                $error = 'Failed to reject farmer';
            }
        }
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : 'pending';

// Get farmers based on filter
$farmers_sql = "SELECT * FROM users WHERE user_type = 'farmer' AND approval_status = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($farmers_sql);
$stmt->bind_param("s", $status_filter);
$stmt->execute();
$farmers_result = $stmt->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-user-check text-success"></i> Approve Farmers
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="?status=pending" class="btn btn-<?php echo $status_filter === 'pending' ? 'warning' : 'outline-warning'; ?>">
                <i class="fas fa-clock"></i> Pending
            </a>
            <a href="?status=approved" class="btn btn-<?php echo $status_filter === 'approved' ? 'success' : 'outline-success'; ?>">
                <i class="fas fa-check"></i> Approved
            </a>
            <a href="?status=rejected" class="btn btn-<?php echo $status_filter === 'rejected' ? 'danger' : 'outline-danger'; ?>">
                <i class="fas fa-times"></i> Rejected
            </a>
        </div>
    </div>
    
    <?php if ($farmers_result->num_rows > 0): ?>
        <div class="row">
            <?php while ($farmer = $farmers_result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5><?php echo htmlspecialchars($farmer['name']); ?></h5>
                            <span class="badge 
                                <?php 
                                    echo $farmer['approval_status'] === 'approved' ? 'bg-success' : 
                                         ($farmer['approval_status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                                ?>">
                                <?php echo ucfirst($farmer['approval_status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-2">
                                <strong><i class="fas fa-envelope"></i> Email:</strong><br>
                                <?php echo htmlspecialchars($farmer['email']); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-phone"></i> Phone:</strong><br>
                                <?php echo htmlspecialchars($farmer['phone'] ?? 'N/A'); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-map-marker-alt"></i> Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($farmer['address'] ?? 'N/A')); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar"></i> Registered:</strong><br>
                                <?php echo date('F d, Y h:i A', strtotime($farmer['created_at'])); ?>
                            </p>
                            
                            <?php if ($farmer['approval_status'] === 'rejected' && $farmer['rejection_reason']): ?>
                                <div class="alert alert-danger mt-3">
                                    <strong>Rejection Reason:</strong><br>
                                    <?php echo htmlspecialchars($farmer['rejection_reason']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($farmer['approval_status'] === 'pending'): ?>
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success flex-fill" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal<?php echo $farmer['id']; ?>">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger flex-fill" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal<?php echo $farmer['id']; ?>">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                            
                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal<?php echo $farmer['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Farmer</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="farmer_id" value="<?php echo $farmer['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <p>Are you sure you want to approve <strong><?php echo htmlspecialchars($farmer['name']); ?></strong> as a farmer?</p>
                                                <p class="text-success"><i class="fas fa-info-circle"></i> They will be able to login and add products.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="approve_farmer" class="btn btn-success">Approve Farmer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?php echo $farmer['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Farmer Application</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="farmer_id" value="<?php echo $farmer['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <p>Reject <strong><?php echo htmlspecialchars($farmer['name']); ?></strong>'s application?</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Reason for Rejection *</label>
                                                    <textarea class="form-control" name="rejection_reason" rows="3" required 
                                                              placeholder="Provide a reason for rejection..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="approve_farmer" class="btn btn-danger">Reject Application</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-user-check" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No <?php echo $status_filter; ?> farmers</h4>
            <p class="text-muted">
                <?php 
                if ($status_filter === 'pending') {
                    echo 'No farmer applications pending approval';
                } elseif ($status_filter === 'approved') {
                    echo 'No approved farmers yet';
                } else {
                    echo 'No rejected applications';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>