<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isCustomer()) {
    echo json_encode(['count' => 0]);
    exit();
}

$count = getCartCount($conn, $_SESSION['user_id']);
echo json_encode(['count' => $count]);
?>