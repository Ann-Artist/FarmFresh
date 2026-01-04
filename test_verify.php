<?php
$hash = '$2y$10$DWqxf9SkpSBElNCwvCRV7OZo4CMNUCkUJwTFtKb0ktJsOL0jnW6TS';

var_dump(password_verify('admin123', $hash));
?>