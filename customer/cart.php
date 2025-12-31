<?php
$page_title = "Shopping Cart";
include '../includes/header.php';

requireLogin();
if (!isCustomer()) {
    header('Location: /farmfresh/index.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Get cart items
$cart_sql = "SELECT c.*, p.name, p.price, p.image, p.unit, p.quantity as stock, u.name as farmer_name 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             JOIN users u ON p.farmer_id = u.id 
             WHERE c.customer_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];
}

$delivery_charge = $subtotal > 500 ? 0 : 50;
$total = $subtotal + $delivery_charge;
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-cart text-success"></i> Shopping Cart
    </h2>
    
    <?php if (count($items) > 0): ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-md-8">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php $image_path = getProductImage($item['image'], $item['name']); ?>
                                <img src="<?php echo $image_path; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($item['farmer_name']); ?>
                                </p>
                                <p class="text-muted small mb-0">Stock: <?php echo $item['stock']; ?> <?php echo $item['unit']; ?></p>
                            </div>
                            <div class="col-md-2">
                                <strong><?php echo formatPrice($item['price']); ?></strong>
                                <span class="text-muted">/ <?php echo $item['unit']; ?></span>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo max(1, $item['quantity'] - 1); ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="text" class="form-control text-center" value="<?php echo $item['quantity']; ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                            <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <strong class="text-success"><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                                <br>
                                <button onclick="removeCartItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4 class="mb-3">Order Summary</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong><?php echo formatPrice($subtotal); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Charge:</span>
                        <strong><?php echo $delivery_charge > 0 ? formatPrice($delivery_charge) : 'FREE'; ?></strong>
                    </div>
                    <?php if ($subtotal < 500): ?>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle"></i> Add <?php echo formatPrice(500 - $subtotal); ?> more for free delivery!
                        </small>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-success" style="font-size: 1.5rem;"><?php echo formatPrice($total); ?></strong>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-check"></i> Proceed to Checkout
                        </a>
                        <a href="../products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-basket"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">Your cart is empty</h4>
            <p class="text-muted">Add some products to get started!</p>
            <a href="../products.php" class="btn btn-primary-custom mt-3">
                <i class="fas fa-shopping-basket"></i> Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Cart update function
function updateCartQuantity(cartId, quantity) {
    if (quantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }
    
    // Show loading
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update cart');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating cart. Please try again.');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

// Cart remove function
function removeCartItem(cartId) {
    if (!confirm('Are you sure you want to remove this item from cart?')) {
        return;
    }
    
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing item. Please try again.');
    });
}
</script>

<?php include '../includes/footer.php'; ?>