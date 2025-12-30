<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Check user type
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isFarmer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'farmer';
}

function isCustomer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /farmfresh/login.php');
        exit();
    }
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload image
function uploadImage($file, $target_dir = '../assets/images/') {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

// Get cart count
function getCartCount($conn, $customer_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Get product image with fallback to placeholder
function getProductImage($image, $product_name = 'Product') {
    if (empty($image)) {
        // Use online placeholder based on product name
        return "https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=300&fit=crop";
    }
    
    $image_path = $_SERVER['DOCUMENT_ROOT'] . '/farmfresh/assets/images/' . $image;
    
    // If image file doesn't exist locally, use online placeholder
    if (!file_exists($image_path)) {
        return "https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=300&fit=crop";
    }
    
    return "/farmfresh/assets/images/" . $image;
}

// Time ago function
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $time);
}
?>