<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$current_user = isLoggedIn() ? getCurrentUser($conn) : null;
$cart_count = isLoggedIn() && isCustomer() ? getCartCount($conn, $_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FarmFresh Organic</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/farmfresh/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/farmfresh/index.php">
                <i class="fas fa-leaf"></i> FarmFresh Organic
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/farmfresh/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/farmfresh/products.php">Products</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/farmfresh/admin/index.php">Dashboard</a>
                            </li>
                        <?php elseif (isFarmer()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/farmfresh/farmer/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif (isCustomer()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/farmfresh/customer/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="/farmfresh/customer/cart.php">
                                    <i class="fas fa-shopping-cart"></i> Cart
                                    <?php if ($cart_count > 0): ?>
                                        <span class="cart-count badge bg-danger rounded-pill"><?php echo $cart_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($current_user['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/farmfresh/profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/farmfresh/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn-login" href="/farmfresh/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-register" href="/farmfresh/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>