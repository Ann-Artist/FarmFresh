<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<h3>Password: $password</h3>";
    echo "<h3>Hash:</h3>";
    echo "<textarea style='width:100%; height:100px;'>$hash</textarea>";
}
?>

<form method="POST">
    <h2>Password Hash Generator</h2>
    <input type="text" name="password" placeholder="Enter password" required>
    <button type="submit">Generate Hash</button>
</form>