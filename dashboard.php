<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2>Dashboard</h2>
    <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
</div>
</body>
</html>
