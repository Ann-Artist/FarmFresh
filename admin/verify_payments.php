<?php
$page_title = "Verify Payments";
include '../includes/header.php';

requireLogin();
if (!isAdmin()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$success = '';
$error = '';

// Handle payment verification
if (isset($_POST['verify_payment'])) {
    $order_id = (int)$_POST['order_id'];
    $action = clean($_POST['action']);
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            $success = 'Payment approved successfully!';
        } else {
            $error = 'Failed to approve payment';
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            $success = 'Payment rejected';
        } else {
            $error = 'Failed to reject payment';
        }
    }
}

// Get all pending payments
$payments_sql = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone 
                 FROM orders o 
                 JOIN users u ON o.customer_id = u.id 
                 WHERE o.payment_status = 'pending' AND o.payment_method != 'cod'
                 ORDER BY o.created_at DESC";
$payments_result = $conn->query($payments_sql);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-check-circle text-success"></i> Verify Payments
    </h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-custom"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($payments_result->num_rows > 0): ?>
        <div class="row">
            <?php while ($payment = $payments_result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5>Order #<?php echo $payment['id']; ?></h5>
                            <span class="badge bg-warning">Pending Verification</span>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="fas fa-user"></i> Customer:</strong><br>
                                    <?php echo htmlspecialchars($payment['customer_name']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-phone"></i> Phone:</strong><br>
                                    <?php echo htmlspecialchars($payment['customer_phone']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="fas fa-rupee-sign"></i> Amount:</strong><br>
                                    <span class="text-success fs-5"><?php echo formatPrice($payment['total_amount']); ?></span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-calendar"></i> Date:</strong><br>
                                    <?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-mobile-alt"></i> Payment Method:</strong>
                            <span class="badge bg-info"><?php echo ucfirst($payment['payment_method']); ?></span>
                        </div>
                        
                        <?php if ($payment['payment_id']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-hashtag"></i> Transaction ID:</strong>
                                <code><?php echo htmlspecialchars($payment['payment_id']); ?></code>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($payment['payment_signature']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-image"></i> Payment Screenshot:</strong><br>
                                <a href="../assets/images/payments/<?php echo $payment['payment_signature']; ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-external-link-alt"></i> View Screenshot
                                </a>
                                <br>
                                <img src="../assets/images/payments/<?php echo $payment['payment_signature']; ?>" 
                                     class="img-fluid mt-2 rounded" 
                                     style="max-height: 300px; cursor: pointer;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="order_id" value="<?php echo $payment['id']; ?>">
                            <button type="submit" name="verify_payment" value="approve" 
                                    onclick="return confirm('Approve this payment?')" 
                                    class="btn btn-success flex-fill">
                                <i class="fas fa-check"></i> Approve Payment
                            </button>
                            <button type="submit" name="verify_payment" value="reject" 
                                    onclick="return confirm('Reject this payment?')" 
                                    class="btn btn-danger flex-fill">
                                <i class="fas fa-times"></i> Reject
                            </button>
                            <input type="hidden" name="action" value="">
                        </form>
                        
                        <script>
                        document.querySelectorAll('button[name="verify_payment"]').forEach(btn => {
                            btn.addEventListener('click', function() {
                                this.closest('form').querySelector('input[name="action"]').value = this.value;
                            });
                        });
                        </script>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-check-circle" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">No Pending Payments</h4>
            <p class="text-muted">All payments have been verified!</p>
            <a href="index.php" class="btn btn-primary-custom mt-3">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>