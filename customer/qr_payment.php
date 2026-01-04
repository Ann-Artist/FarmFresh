<?php
$page_title = "Complete Payment";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

// Get order details from session or parameter
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$customer_id = $_SESSION['user_id'];

// Verify order belongs to customer
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: dashboard.php');
    exit();
}

$order = $result->fetch_assoc();

// Handle payment screenshot upload
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $transaction_id = clean($_POST['transaction_id']);
    $payment_method = clean($_POST['payment_method']);
    
    if (empty($transaction_id)) {
        $error = 'Please enter transaction ID';
    } elseif (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] != 0) {
        $error = 'Please upload payment screenshot';
    } else {
        // Upload screenshot
        $upload_result = uploadImage($_FILES['payment_screenshot'], '../assets/images/payments/');
        
        if ($upload_result['success']) {
            // Update order with payment details
            $update_stmt = $conn->prepare("UPDATE orders SET 
                payment_method = ?, 
                payment_id = ?, 
                payment_signature = ?, 
                payment_status = 'pending' 
                WHERE id = ?");
            
            $update_stmt->bind_param("sssi", $payment_method, $transaction_id, $upload_result['filename'], $order_id);
            
            if ($update_stmt->execute()) {
                $success = 'Payment proof uploaded successfully! We will verify and confirm your order.';
            } else {
                $error = 'Failed to submit payment proof';
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

// UPI Payment details (Replace with your actual UPI details)
$upi_id = "farmfresh@paytm"; // Your UPI ID
$merchant_name = "FarmFresh Organic";
$amount = $order['total_amount'];

// Generate UPI payment link
$upi_link = "upi://pay?pa={$upi_id}&pn={$merchant_name}&am={$amount}&cu=INR&tn=Order{$order_id}";
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="dashboard-card">
                <h3 class="text-center mb-4">
                    <i class="fas fa-qrcode text-success"></i> Complete Payment
                </h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-custom">
                        <?php echo $success; ?>
                        <div class="mt-3">
                            <a href="dashboard.php" class="btn btn-primary-custom">Go to Dashboard</a>
                            <a href="order_details.php?id=<?php echo $order_id; ?>" class="btn btn-outline-primary">View Order</a>
                        </div>
                    </div>
                <?php else: ?>
                
                <!-- Order Summary -->
                <div class="text-center mb-4 p-3 bg-light rounded">
                    <h5>Order #<?php echo $order['id']; ?></h5>
                    <h2 class="text-success mb-0"><?php echo formatPrice($order['total_amount']); ?></h2>
                    <small class="text-muted">Amount to Pay</small>
                </div>
                
                <!-- Payment Instructions -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Payment Instructions:</h6>
                    <ol class="mb-0 small">
                        <li>Scan the QR code below using any UPI app</li>
                        <li>Or click "Pay Now" to open payment app directly</li>
                        <li>Complete the payment of <strong><?php echo formatPrice($amount); ?></strong></li>
                        <li>Take a screenshot of payment confirmation</li>
                        <li>Upload screenshot below with Transaction ID</li>
                    </ol>
                </div>
                
                <!-- QR Code Section -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-4">
                            <h6 class="mb-3">Scan QR Code to Pay</h6>
                            
                            <!-- QR Code using Google Charts API (No API key needed) -->
                            <div class="qr-code-container p-3 bg-white border rounded">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($upi_link); ?>" 
                                     alt="Payment QR Code" 
                                     class="img-fluid"
                                     style="max-width: 250px;">
                            </div>
                            
                            <p class="small text-muted mt-3 mb-2">
                                <i class="fas fa-mobile-alt"></i> Scan with any UPI app
                            </p>
                            
                            <!-- Payment Apps Icons -->
                            <div class="payment-apps">
                                <span class="badge bg-light text-dark me-1">
                                    <i class="fas fa-mobile"></i> PhonePe
                                </span>
                                <span class="badge bg-light text-dark me-1">
                                    <i class="fas fa-mobile"></i> Paytm
                                </span>
                                <span class="badge bg-light text-dark me-1">
                                    <i class="fas fa-mobile"></i> Google Pay
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-mobile"></i> BHIM
                                </span>
                            </div>
                            
                            <!-- Direct Payment Button (Opens UPI app on mobile) -->
                            <div class="mt-3">
                                <a href="<?php echo $upi_link; ?>" class="btn btn-primary-custom">
                                    <i class="fas fa-mobile-alt"></i> Pay Now via UPI
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Payment Proof -->
                    <div class="col-md-6">
                        <div class="payment-proof-section">
                            <h6 class="mb-3">Upload Payment Proof</h6>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Select Payment App *</label>
                                    <select class="form-control" name="payment_method" required>
                                        <option value="">Choose App...</option>
                                        <option value="phonepe">PhonePe</option>
                                        <option value="paytm">Paytm</option>
                                        <option value="googlepay">Google Pay</option>
                                        <option value="bhim">BHIM UPI</option>
                                        <option value="other">Other UPI App</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Transaction ID / UTR Number *</label>
                                    <input type="text" class="form-control" name="transaction_id" 
                                           placeholder="Enter 12-digit transaction ID" required>
                                    <small class="text-muted">Find this in your payment app after completing payment</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Payment Screenshot *</label>
                                    <input type="file" class="form-control" name="payment_screenshot" 
                                           accept="image/*" required onchange="previewScreenshot(this)">
                                    <small class="text-muted">Upload clear screenshot of payment confirmation</small>
                                    
                                    <!-- Preview -->
                                    <div id="screenshotPreview" class="mt-2" style="display: none;">
                                        <img src="" id="previewImg" style="max-width: 100%; max-height: 200px; border-radius: 10px;">
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning small">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>Important:</strong> Your order will be processed after payment verification (within 2-4 hours)
                                </div>
                                
                                <button type="submit" name="submit_payment" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> Submit Payment Proof
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- UPI Details (Manual) -->
                <div class="mt-4">
                    <hr>
                    <h6 class="text-center mb-3">Or Pay Manually</h6>
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">UPI ID</small>
                                <strong class="text-success"><?php echo $upi_id; ?></strong>
                                <button class="btn btn-sm btn-outline-secondary mt-2" onclick="copyUPI()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Amount</small>
                                <strong class="text-success"><?php echo formatPrice($amount); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyUPI() {
    const upiId = '<?php echo $upi_id; ?>';
    navigator.clipboard.writeText(upiId).then(() => {
        alert('UPI ID copied to clipboard!');
    });
}

function previewScreenshot(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('screenshotPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.qr-code-container {
    display: inline-block;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.payment-apps .badge {
    padding: 5px 10px;
    font-size: 0.85rem;
}

.payment-proof-section {
    border-left: 3px solid var(--primary-color);
    padding-left: 20px;
}

@media (max-width: 768px) {
    .payment-proof-section {
        border-left: none;
        border-top: 3px solid var(--primary-color);
        padding-left: 0;
        padding-top: 20px;
        margin-top: 20px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>